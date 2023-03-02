<?php

declare(strict_types=1);

namespace App\Repository\MasterOutbox;

use App\Models\MasterOutbox\CardAllowedEntity;
use App\Models\MasterOutbox\File;
use App\Models\MasterOutbox\FileAllowedEntity;
use App\Models\MasterOutbox\FileVisibilityType;
use App\Services\MasterOutbox\Dto\UserAllowedEntitiesDto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Sxope\Repositories\Repository;
use Sxope\Repositories\SearchRepository;
use Sxope\ValueObjects\Id;
use Sxope\ValueObjects\SearchConditions\Conditions\ContainsCondition;
use Sxope\ValueObjects\SearchConditions\SearchConditions;

/**
 * @extends Repository<File>
 */
class FileRepository extends SearchRepository
{
    public static function getEntityInstance(): Model
    {
        return new File();
    }

    public function getList(SearchConditions $searchConditions, $userAllowedEntitiesData): mixed
    {
        /** @var ContainsCondition $entityIdsCondition */
        $entityIdsCondition = $searchConditions->getFilter()
            ->getAndRemoveFilter('entity_id');

        /** @var ContainsCondition $entityTypeIdsCondition */
        $entityTypeIdsCondition = $searchConditions->getFilter()
            ->getAndRemoveFilter('entity_type_id');

        $query = $this->buildListQuery($userAllowedEntitiesData, $entityIdsCondition, $entityTypeIdsCondition)
            ->select([
                'mo_files.file_id',
                'description',
                'assigned_period',
                'visibility_type_id',
                'mo_files.card_id',
                'file_size',
                'mo_files.created_at',
                'mo_files.created_by',
                'mo_files.updated_at',
                'mo_files.updated_by',
                DB::raw('IFNULL(mo_file_view_logs.is_read, false) as is_read')
            ]);

        $this->applyAllConditions($query, $searchConditions);

        return $this->only($query->get(), $searchConditions->getAvailableFields());
    }

    public function getFileIdsByCardId(Id $cardId, UserAllowedEntitiesDto $userAllowedEntitiesData): Collection
    {
        return $this->buildListQuery($userAllowedEntitiesData)
            ->select([
                'mo_files.file_id',
            ])
            ->where('mo_files.card_id', $cardId)
            ->pluck('file_id');
    }

    public function getCounts(SearchConditions $searchConditions, $userAllowedEntitiesData): array
    {
        /** @var ContainsCondition $entityIdsCondition */
        $entityIdsCondition = $searchConditions->getFilter()
            ->getAndRemoveFilter('entity_id');

        /** @var ContainsCondition $entityTypeIdsCondition */
        $entityTypeIdsCondition = $searchConditions->getFilter()
            ->getAndRemoveFilter('entity_type_id');

        $query = $this->buildListQuery($userAllowedEntitiesData, $entityIdsCondition, $entityTypeIdsCondition);

        $selectFieldsToGroupBuyString = '';
        foreach ($searchConditions->getAvailableFields() as $availableField) {
            if ($availableField === 'is_read') {
                $query->select(DB::raw('IFNULL(mo_file_view_logs.is_read, false) as is_read'));
                $query->groupBy('is_read');
            } else {
                $selectFieldsToGroupBuyString .= 'mo_files.' . $availableField . ',';
                $query->groupBy($availableField);
            }
        }
        $this->applyAllConditionsForCounts($query, $searchConditions);
        $query->selectRaw($selectFieldsToGroupBuyString . 'COUNT(*) AS count');

        return $query->get()->toArray();
    }

    public function getIsFileAllowedForUser(Id $fileId, UserAllowedEntitiesDto $userAllowedEntitiesData): bool
    {
        $query = $this->buildListQuery($userAllowedEntitiesData)
            ->where('mo_files.file_id', $fileId);

        return $query->exists();
    }

    private function buildListQuery(
        UserAllowedEntitiesDto $allowedEntities,
        ContainsCondition $entityIdsCondition = null,
        ContainsCondition $entityTypeIdsCondition = null,
    ): Builder {
        $query = $this->getQuery()
            ->leftJoin(
                'mo_file_view_logs',
                function (JoinClause $join) use ($allowedEntities) {
                    $join->on('mo_file_view_logs.file_id', 'mo_files.file_id')
                        ->where('mo_file_view_logs.user_id', Id::create($allowedEntities->userId));
                }
            );

        if (!$allowedEntities->fullAccess) {
            $subQueryFilePermissions = FileAllowedEntity::query()
                ->select([
                    'file_id'
                ])
                ->distinct()
                ->join(
                    DB::raw(
                        "(SELECT FROM_HEX(entity_id) AS entity_id  FROM UNNEST ("
                        . json_encode($allowedEntities, JSON_THROW_ON_ERROR)
                        . ") AS entity_id) AS TEMP"
                    ),
                    function (JoinClause $join) {
                        $join->on('TEMP.entity_id', 'mo_file_allowed_entities.entity_id');
                    }
                );

            $this->applyEntityFiltersToQuery(
                $subQueryFilePermissions,
                $entityIdsCondition,
                $entityTypeIdsCondition
            );

            $subQueryCardPermissions = CardAllowedEntity::query()
                ->select([
                    'mo_card_allowed_entities.card_id'
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

            $this->applyEntityFiltersToQuery(
                $subQueryCardPermissions,
                $entityIdsCondition,
                $entityTypeIdsCondition
            );

            $query->leftJoinSub(
                    $subQueryFilePermissions,
                    'TEMP_FILE_PERMISSIONS',
                    'mo_files.file_id',
                    'TEMP_FILE_PERMISSIONS.file_id'
                )
                ->leftJoinSub(
                    $subQueryCardPermissions,
                    'TEMP_CARD_PERMISSIONS',
                    'mo_files.card_id',
                    'TEMP_CARD_PERMISSIONS.card_id'
                )
                ->where(static function (Builder $query) {
                    $query->where('visibility_type_id', FileVisibilityType::TYPE_PUBLIC_ID);
                    $query->orWhereNotNull('TEMP_FILE_PERMISSIONS.file_id');
                    $query->orWhereNotNull('TEMP_CARD_PERMISSIONS.card_id');
                });
        } else {
            $this->applyEntityFiltersToQuery(
                $query,
                $entityIdsCondition,
                $entityTypeIdsCondition
            );
        }

        return $query;
    }

    public function applyEntityFiltersToQuery(
        Builder $builder,
        ContainsCondition $entityIdsCondition = null,
        ContainsCondition $entityTypeIdsCondition = null,
    ): Builder {
        if ($entityIdsCondition !== null) {
            $containsMethod = $entityIdsCondition->isContains() ? 'whereIn' : 'whereNotIn';
            $builder->$containsMethod($builder->from . '.entity_id', Id::batchCreate($entityIdsCondition->getValues()));
        }

        if ($entityTypeIdsCondition !== null) {
            $containsMethod = $entityTypeIdsCondition->isContains() ? 'whereIn' : 'whereNotIn';
            $builder->$containsMethod($builder->from . '.entity_type_id', $entityTypeIdsCondition->getValues());
        }

        return $builder;
    }

    public static function getMapFilterParamToColumn(): array
    {
        return [
            'card_id' => 'mo_files.card_id',
        ];
    }

    public static function getMapSortFieldToColumn(): array
    {
        return [
            'created_at' => 'mo_files.created_at'
        ];
    }
}
