<?php

declare(strict_types=1);

namespace App\Services\MasterOutbox;

use App\Helpers\PageHelper;
use App\Models\File;
use App\Models\MasterOutbox\Card;
use App\Models\MasterOutbox\CardAllowedEntity;
use App\Repository\MasterOutbox\CardsRepository;
use App\Services\MasterOutbox\Dto\CardCreateDto;
use App\Services\MasterOutbox\Dto\CardUpdateDto;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Sxope\Exceptions\SxopeDomainException;
use Sxope\Exceptions\SxopeEntityNotFoundException;
use Sxope\Facades\Sxope;
use Sxope\ValueObjects\Id;
use Sxope\ValueObjects\SearchConditions\Conditions\ContainsCondition;
use Sxope\ValueObjects\SearchConditions\SearchConditions;

class CardsService
{
    public function __construct(
        private readonly CardsRepository $cardsRepository,
        private readonly CardAllowedEntitiesService $cardAllowedEntitiesService
    ) {
    }

    public function create(CardCreateDto $cardCreateDto): array
    {
        $cardId = Sxope::getNewId();
        $cardLogo = null;

        if (!is_null($cardCreateDto->logo)) {
            $cardLogo = $this->putLogo($cardCreateDto->logo, $cardId);
        }
        $card = $this->cardsRepository->create([
            'card_id' => $cardId,
            'card_name' => $cardCreateDto->cardName,
            'logo' => $cardLogo,
            'is_custom' => true,
            'show_on_dashboard' => $cardCreateDto->showOnDashboard,
            'created_at' => Carbon::now(),
            'created_at_day_id' => Sxope::getCurrentDayId(),
            'created_by' => Sxope::getCurrentUserId(),
        ]);

        return $card->only(Card::$onlyFields);
    }

    /**
     * @throws SxopeEntityNotFoundException
     */
    public function update(Id $cardId, CardUpdateDto $cardUpdateDto): array
    {
        $shouldUpdated = [];
        $card = $this->cardsRepository->findById($cardId);

        if ($card === null) {
            throw new SxopeEntityNotFoundException(Card::class, $cardId->getHex());
        }

        $cardLogo = false;

        if (
            ($cardUpdateDto->deleteLogo || $cardUpdateDto->logo !== null)
            && $card->logo
        ) {
            Storage::delete($card->logo);
            $cardLogo = null;
        }
        if ($cardUpdateDto->logo !== null) {
            $cardLogo = $this->putLogo($cardUpdateDto->logo, $cardId);
        }

        if ($cardLogo !== false) {
            $shouldUpdated = [
                'logo' => $cardLogo,
            ];
        }
        if ($cardUpdateDto->cardName !== null) {
            $shouldUpdated['card_name'] = $cardUpdateDto->cardName;
        }

        if ($card->is_custom) {
            $shouldUpdated['show_on_dashboard'] = $cardUpdateDto->showOnDashboard;
        }

        $card = $this->cardsRepository->updateById($card->card_id, $shouldUpdated + Sxope::getUpdatingContext(), true);

        return $card->only(Card::$onlyFields);
    }

    /**
     * @throws SxopeEntityNotFoundException
     */
    public function delete(Id $cardId): void
    {
        $card = $this->cardsRepository->findById($cardId);

        if ($card === null) {
            throw new SxopeEntityNotFoundException(Card::class, $cardId->getHex());
        }

        if ($card->is_custom === false) {
            throw new SxopeDomainException('Can`t update system card ' . $card->card_name);
        }

        if ($card->logo !== null) {
            Storage::delete($card->logo);
        }

        $card->delete();
    }

    public function list(SearchConditions $searchConditions): array
    {
        /** @var ContainsCondition $entityIdsCondition */
        $entityIdsCondition = $searchConditions->getFilter()
            ->getAndRemoveFilter('entity_id');

        /** @var ContainsCondition $entityTypeIdsCondition */
        $entityTypeIdsCondition = $searchConditions->getFilter()
            ->getAndRemoveFilter('entity_type_id');

        $preparedQuery = $this->cardsRepository->getQuery()
            ->whereIn(
                'mo_cards.card_id',
                $this->cardAllowedEntitiesService->getAllowedCardsByUserId(
                    Sxope::getCurrentUserId(),
                    $entityIdsCondition,
                    $entityTypeIdsCondition,
                )
            );

        return PageHelper::applyOnly(
            $this->cardsRepository->search($searchConditions, $preparedQuery)->all(),
            $searchConditions->getAvailableFields()
        );
    }

    private function putLogo(UploadedFile $file, Id $cardId): ?string
    {
        $filePath = 'master_outbox/cards/' . $cardId->getHex() . '/logo.' . $file->extension();

        return Storage::disk(File::UPLOADS_DISK)->put($filePath, $file->getContent())
            ? $filePath : null;
    }

    public function exists(Id $cardId): bool
    {
        return $this->cardsRepository->exists(['card_id' => $cardId]);
    }

    public function findById(Id $cardId): array
    {
        $card = $this->cardsRepository->findById($cardId);

        if ($card === null) {
            throw new SxopeEntityNotFoundException(Card::class, $cardId->getHex());
        }

        return array_merge(
            $card->only(
                array_merge(Card::$onlyFields)
            ),
            [
                'allowed_entities' => PageHelper::applyOnly($card->allowedEntities, CardAllowedEntity::$onlyFields)
            ]
        );
    }
}
