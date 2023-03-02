<?php

declare(strict_types=1);

namespace App\Services\MasterOutbox;

use App\Helpers\PageHelper;
use App\Models\MasterOutbox\File;
use App\Models\MasterOutbox\FileAllowedEntity;
use App\Models\MasterOutbox\FileVisibilityType;
use App\Repository\MasterOutbox\FileRepository;
use App\Services\MasterOutbox\Dto\FileContentDto;
use App\Services\MasterOutbox\Dto\FileCreateDto;
use App\Services\MasterOutbox\Dto\FileUpdateDto;
use Google\Cloud\Spanner\Bytes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Sxope\Exceptions\SxopeDomainException;
use Sxope\Exceptions\SxopeEntityNotFoundException;
use Sxope\Facades\Debugger;
use Sxope\Facades\Sxope;
use Sxope\ValueObjects\Id;
use Sxope\ValueObjects\SearchConditions\SearchConditions;
use Symfony\Component\Mime\MimeTypes;
use Throwable;

class FileService
{
    public function __construct(
        private readonly FileRepository $fileRepository,
        private readonly FileAllowedEntityService $fileAllowedEntityService,
        private readonly CardAllowedEntitiesService $cardAllowedEntitiesService,
        private readonly AllowedEntitiesService $allowedEntitiesService,
        private readonly FileViewLogsService $fileViewLogsService,
    ) {
    }

    public function create(FileCreateDto $dto): array
    {
        if (
            $dto->useCardPermissions
            && !$this->cardAllowedEntitiesService->isCardHasPermissions($dto->cardId)
        ) {
            throw new SxopeDomainException('Can`t use card permission. Card has no permission.');
        }

        $token = 'Create file';
        $labels = [
            'action_type' => 'master_outbox',
            'action' => 'master_outbox.card_files_create',
            'card_id' => $dto->cardId->getHex(),
        ];
        $context = [
            'card_id' => $dto->cardId->getHex(),
            'assigned_period' => $dto->assignedPeriod,
            'description' => $dto->description,
        ];

        Debugger::beginProfile($token, $context, $labels);

        $fileEntity = DB::transaction(function () use ($labels, &$context, $dto) {
            try {
                $data = [
                    'description' => $dto->description,
                    'data_owner_id' => Sxope::getCurrentDataOwnerId(),
                    'assigned_period' => $dto->assignedPeriod,
                    'card_id' => $dto->cardId,
                    'use_card_permissions' => $dto->useCardPermissions ?? false,
                    'original_file_name' => $dto->file->getClientOriginalName(),
                    'file_size' => $dto->file->getSize(),
                    'md5_checksum' => new Bytes(md5($dto->file->get(), true)),
                    'visibility_type_id' => $dto->useCardPermissions
                        ? FileVisibilityType::TYPE_PRIVATE_ID
                        : FileVisibilityType::TYPE_PUBLIC_ID,
                ];

                $fileEntity = $this->fileRepository->create($data + Sxope::getCreatingContext());

                $context += [
                    'file_id' => Id::create($fileEntity->file_id)->getHex(),
                    'original_file_name' => $dto->file->getClientOriginalName(),
                    'file_size' => $dto->file->getSize(),
                ];


                $filePath = Storage::disk(
                    config('filesystems.upload_disc', 'gcloud')
                )->put(Id::create($fileEntity->file_id)->getHex() . '.' . $dto->file->extension(), $dto->file->getContent());

                $context['file_path'] = $filePath;

                return $fileEntity;
            } catch (Throwable $e) {
                Debugger::error(
                    'File uploaded error',
                    [
                        'exception' => $e,
                        'message' => 'with_error',
                    ] + $context,
                    $labels
                );

                throw $e;
            }
        });

        Debugger::endProfile($token, $context, $labels);

        return $fileEntity->only(File::$onlyFields);
    }

    public function update(Id $fileId, FileUpdateDto $dto): array
    {
        if (
            $dto->useCardPermissions
            && ($dto->cardId !== null && !$this->cardAllowedEntitiesService->isCardHasPermissions($dto->cardId))
        ) {
            throw new SxopeDomainException('Can`t use card permission. Card has no permission.');
        }

        $file = $this->fileRepository->findById($fileId);

        if ($file === null) {
            throw new SxopeEntityNotFoundException(File::class, $fileId->getHex());
        }

        $token = 'Create file';
        $labels = [
            'action_type' => 'master_outbox',
            'action' => 'master_outbox.card_files_update',
        ];

        $data = [];
        if ($dto->cardId !== null) {
            $data['card_id'] = $dto->cardId;
        }
        if ($dto->useCardPermissions !== null) {
            $data['use_card_permissions'] = $dto->useCardPermissions;
        }

        if (
            $dto->useCardPermissions === true
        ) {
            $data['visibility_type_id'] = FileVisibilityType::TYPE_PRIVATE_ID;
        } elseif ($this->fileAllowedEntityService->hasFilePermissions($fileId)) {
            $data['visibility_type_id'] = FileVisibilityType::TYPE_PRIVATE_ID;
        } else {
            $data['visibility_type_id'] = FileVisibilityType::TYPE_PUBLIC_ID;
        }

        Debugger::beginProfile($token, $data, $labels);

        try {
            $this->fileRepository->updateByWhere(
                ['file_id' => $fileId],
                $data + Sxope::getUpdatingContext()
            );

            $file->refresh();
        } catch (Throwable $e) {
            Debugger::error(
                'File uploaded error',
                [
                    'message' => 'with_error',
                    'exception' => $e,
                ],
                $labels
            );
            throw $e;
        }


        Debugger::endProfile($token, $data, $labels);

        return $file->only(File::$onlyFields);
    }

    public function delete(Id $fileId): void
    {
        if (!$this->fileRepository->exists(['file_id' => $fileId])) {
            throw new SxopeEntityNotFoundException(File::class, $fileId->getHex());
        }

        $this->fileRepository->deleteByWhere(['file_id' => $fileId]);
    }

    public function getFileEncodedContent(Id $fileId): FileContentDto
    {
        $file = $this->fileRepository->findById($fileId);

        if ($file === null) {
            throw new SxopeEntityNotFoundException(File::class, $fileId->getHex());
        }
        if (!$this->fileRepository->getIsFileAllowedForUser($fileId, $this->allowedEntitiesService->getUserAllowedEntities(Sxope::getCurrentUserId()))) {
            throw new SxopeDomainException('File not allowed for current user');
        }
        $fileParts = explode('.', $file->original_file_name);
        $extension = array_pop($fileParts);

        $contents = Storage::disk(
            config('filesystems.upload_disc', 'gcloud')
        )->get($fileId->getHex() . '.' . $extension);

        if ($contents === null) {
            throw new SxopeDomainException('File missed');
        }

        $this->fileViewLogsService->markReadBatch(
            [Id::create($file->file_id)],
            Sxope::getCurrentUserId(),
        );

        return new FileContentDto(
            base64_encode($contents),
            $extension,
            MimeTypes::getDefault()->getMimeTypes($extension)[0] ?? null
        );
    }

    public function getList(SearchConditions $conditions): array
    {
        return $this->fileRepository->getList(
            $conditions,
            $this->allowedEntitiesService->getUserAllowedEntities(Sxope::getCurrentUserId())
        )->all();
    }

    public function getFileIdsByCardId(Id $cardId): Collection
    {
        return $this->fileRepository->getFileIdsByCardId(
            $cardId,
            $this->allowedEntitiesService->getUserAllowedEntities(Sxope::getCurrentUserId())
        );
    }

    public function getCounts(SearchConditions $conditions): array
    {
        return $this->buildCountsResponse(
            $this->fileRepository->getCounts(
                $conditions,
                $this->allowedEntitiesService->getUserAllowedEntities(Sxope::getCurrentUserId())
            )
        );
    }

    public function findById(Id $fileId): array
    {
        $file = $this->fileRepository->findById($fileId);

        if ($file === null) {
            throw new SxopeEntityNotFoundException(File::class, $fileId->getHex());
        }

        return array_merge(
            $file->only(File::$onlyFields),
            ['allowed_entities' => PageHelper::applyOnly($file->allowedEntities, FileAllowedEntity::$onlyFields)]
        );
    }

    private function buildCountsResponse(array $data): array
    {
        $list = new Collection();
        $totalCount = 0;
        foreach ($data as $item) {
            $list->add($item);
            $totalCount += $item['count'];
        }

        $list->put('total_count',  $totalCount);

        return array_values($list->all());
    }
}
