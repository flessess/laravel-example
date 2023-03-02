<?php

declare(strict_types=1);

namespace App\Services\MasterOutbox;

use App\Helpers\PageHelper;
use App\Models\MasterOutbox\EntityType;
use App\Models\MasterOutbox\File;
use App\Models\MasterOutbox\FileAllowedEntity;
use App\Models\MasterOutbox\FileVisibilityType;
use App\Repository\MasterOutbox\FileAllowedEntityRepository;
use App\Repository\MasterOutbox\FileRepository;
use App\Services\MasterOutbox\Dto\FileAllowedEntityDto;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Sxope\Exceptions\SxopeDomainException;
use Sxope\Exceptions\SxopeEntityNotFoundException;
use Sxope\Facades\Sxope;
use Sxope\Facades\SxopeLogger;
use Sxope\ValueObjects\Id;
use Sxope\ValueObjects\Repository\WhereCondition;

class FileAllowedEntityService
{
    public function __construct(
        private readonly FileAllowedEntityRepository $repository,
        private readonly FileRepository $fileRepository,
    ) {
    }


    public function create(Id $fileId, Collection $dto): array
    {
        if (!$this->fileRepository->exists(['file_id' => $fileId])) {
            throw new SxopeEntityNotFoundException(File::class, $fileId->getHex());
        }

        /** @var Collection $created */
        $created = DB::transaction(function () use ($fileId, $dto) {
            $valuesToCreate = [];

            try {
                /** @var FileAllowedEntityDto $item */
                foreach ($dto->all() as $item) {
                    if (EntityType::find($item->entityTypeId) === null) {
                        throw new SxopeDomainException("Entity type with id $item->entityTypeId not exists");
                    }
                    if (
                        $this->repository->exists(
                            ['file_id' => $fileId, 'entity_type_id' => $item->entityTypeId, 'entity_id' => $item->entityId]
                        )
                    ) {
                        continue;
                    }
                    $valuesToCreate[] = [
                            'file_allowed_entity_id' => Sxope::getNewId(),
                            'file_id' => $fileId,
                            'entity_type_id' => $item->entityTypeId,
                            'entity_id' => $item->entityId,
                            'data_owner_id' => Sxope::getCurrentDataOwnerId(),
                        ] + Sxope::getCreatingContext();
                }

                if (count($valuesToCreate) > 0) {
                    $this->repository->createBatch($valuesToCreate);

                    return collect($valuesToCreate)
                        ->map(static fn(array $data) => new FileAllowedEntity($data));
                }
            } catch (\Throwable $e) {
                SxopeLogger::error(
                    'Error on create allowed entitites: ' . $e->getMessage(),
                    [
                        'file_id' => $fileId->getHex()
                    ]
                );

                throw $e;
            }

            return collect([]);
        });

        if ($created->count() > 0) {
            $this->fileRepository->updateByWhere(
                ['file_id' => $fileId],
                ['visibility_type_id' => FileVisibilityType::TYPE_PRIVATE_ID]
            );
        }

        return PageHelper::applyOnly($created, FileAllowedEntity::$onlyFields)->all();
    }

    public function delete(Id $fileId, array $fileAllowedEntityIds): void
    {
        if (!$this->fileRepository->exists(['file_id' => $fileId])) {
            throw new SxopeEntityNotFoundException(File::class, $fileId->getHex());
        }

        $this->repository->deleteByChunk(
            new WhereCondition(whereIn: ['file_allowed_entity_id' => $fileAllowedEntityIds])
        );

        $file = $this->fileRepository->findById($fileId);
        if ($file !== null && !$file->use_card_permissions && !$this->hasFilePermissions($fileId)) {
            $file->visibility_type_id = FileVisibilityType::TYPE_PUBLIC_ID;
            $file->save();
        }
    }

    public function hasFilePermissions(Id $fileId): bool
    {
        return $this->repository->exists(['file_id' => $fileId]);
    }
}
