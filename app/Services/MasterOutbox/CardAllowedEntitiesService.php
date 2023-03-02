<?php

declare(strict_types=1);

namespace App\Services\MasterOutbox;

use App\Enums\MasterOutbox\EntityTypes;
use App\Helpers\PageHelper;
use App\Models\MasterOutbox\Card;
use App\Models\MasterOutbox\CardAllowedEntity;
use App\Models\MasterOutbox\File;
use App\Models\MasterOutbox\FileAllowedEntity;
use App\Repository\MasterOutbox\CardAllowedEntitiesRepository;
use App\Repository\MasterOutbox\CardsRepository;
use App\Repository\MasterOutbox\FileRepository;
use App\Services\MasterOutbox\Dto\CardAllowedEntityDto;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Sxope\Exceptions\SxopeDomainException;
use Sxope\Exceptions\SxopeEntityNotFoundException;
use Sxope\Facades\Sxope;
use Sxope\Facades\SxopeLogger;
use Sxope\ValueObjects\Id;
use Sxope\ValueObjects\Repository\WhereCondition;
use Sxope\ValueObjects\SearchConditions\Conditions\ContainsCondition;
use Throwable;

class CardAllowedEntitiesService
{
    public function __construct(
        private readonly CardAllowedEntitiesRepository $cardAllowedEntitiesRepository,
        private readonly AllowedEntitiesService $allowedEntitiesService,
        private readonly CardsRepository $cardsRepository,
        private readonly FileRepository $fileRepository,
    ) {
    }

    public function create(Id $cardId, Collection $dtos): array
    {
        if (!$this->cardsRepository->exists(['card_id' => $cardId])) {
            throw new SxopeEntityNotFoundException(Card::class, $cardId->getHex());
        }

        /** @var Collection $created */
        $created = DB::transaction(function () use ($cardId, $dtos) {
            $valuesToCreate = [];

            try {
                /** @var CardAllowedEntityDto $item */
                foreach ($dtos->all() as $item) {
                    if (!EntityTypes::isIdExists($item->entityTypeId)) {
                        throw new SxopeDomainException("Entity type with id $item->entityTypeId not exists");
                    }

                    if (
                        $this->cardAllowedEntitiesRepository->exists(
                            ['card_id' => $cardId, 'entity_type_id' => $item->entityTypeId, 'entity_id' => $item->entityId]
                        )
                    ) {
                        continue;
                    }
                    $valuesToCreate[] = [
                            'card_allowed_entity_id' => Sxope::getNewId(),
                            'card_id' => $cardId,
                            'entity_type_id' => $item->entityTypeId,
                            'entity_id' => $item->entityId,
                            'data_owner_id' => Sxope::getCurrentDataOwnerId(),
                        ] + Sxope::getCreatingContext();
                }

                if (count($valuesToCreate) > 0) {
                    $this->cardAllowedEntitiesRepository->createBatch($valuesToCreate);

                    return collect($valuesToCreate)
                        ->map(static fn(array $data) => new FileAllowedEntity($data));
                }
            } catch (Throwable $e) {
                SxopeLogger::error(
                    'Error on create allowed entities: ' . $e->getMessage(),
                    [
                        'card_id' => $cardId->getHex()
                    ]
                );

                throw $e;
            }

            return collect([]);
        });

        return PageHelper::applyOnly($created, CardAllowedEntity::$onlyFields)->all();
    }

    public function delete(Id $cardId, array $cardAllowedEntityIds): void
    {
        if (!$this->cardsRepository->exists(['card_id' => $cardId])) {
            throw new SxopeEntityNotFoundException(File::class, $cardId->getHex());
        }

        $this->cardAllowedEntitiesRepository->deleteByChunk(
            new WhereCondition(whereIn: ['card_allowed_entity_id' => Id::batchCreate($cardAllowedEntityIds)])
        );
    }

    public function isCardHasPermissions(Id $cardId): bool
    {
        return $this->cardAllowedEntitiesRepository->exists(['card_id' => $cardId]);
    }

    public function getAllowedCardsByUserId(
        Id $userid,
        ContainsCondition $entityIdsCondition = null,
        ContainsCondition $entityTypeIdsCondition = null
    ): Collection {
        $allowedEntities = $this->allowedEntitiesService->getUserAllowedEntities($userid);
        $query = $this->cardsRepository->getQuery()
            ->select(['mo_cards.card_id']);

        if (!$allowedEntities->fullAccess) {
            $subQueryCardPermissions = CardAllowedEntity::query()
                ->select([
                    'card_id'
                ])
                ->distinct()
                ->join(
                    DB::raw(
                        "(SELECT FROM_HEX(entity_id) AS entity_id  FROM UNNEST ("
                        . json_encode($allowedEntities, JSON_THROW_ON_ERROR)
                        . ") AS entity_id) AS TEMP"
                    ),
                    function (JoinClause $join) {
                        $join->on('TEMP.entity_id', 'mo_card_allowed_entities.entity_id');
                    }
                );
            $this->fileRepository->applyEntityFiltersToQuery(
                $subQueryCardPermissions,
                $entityIdsCondition,
                $entityTypeIdsCondition
            );

            $query
                ->leftJoinSub(
                    $subQueryCardPermissions,
                    'TEMP_CARD_PERMISSIONS',
                    'mo_cards.card_id',
                    'TEMP_CARD_PERMISSIONS.card_id'
                )
                ->whereNotExists(function (Builder $query) {
                    $query->from('mo_card_allowed_entities')
                        ->select([DB::raw(1)])
                        ->whereRaw('mo_card_allowed_entities.card_id = mo_cards.card_id')
                        ->whereNull('deleted_at');
                })
                ->orWhereNotNull('TEMP_CARD_PERMISSIONS.card_id');
        }


        return collect($query->distinct()->pluck('card_id'));
    }
}
