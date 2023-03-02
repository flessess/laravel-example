<?php

declare(strict_types=1);

namespace App\Services\MasterOutbox;

use App\Models\MasterOutbox\FileViewLog;
use App\Repository\MasterOutbox\FileViewLogRepository;
use Google\Cloud\Spanner\Bytes;
use Illuminate\Support\Collection;
use Sxope\Facades\Sxope;
use Sxope\ValueObjects\Id;
use Sxope\ValueObjects\Repository\WhereCondition;

class FileViewLogsService
{
    public function __construct(
        private readonly FileViewLogRepository $fileViewLogRepository
    ) {
    }

    public function markReadBatch(array $fileIds, Id $userId, bool $isRead = true): void
    {
        $fileIdsCollection = collect($fileIds);

        $updated = $this->fileViewLogRepository->updateByWhere(
            new WhereCondition(['user_id' => $userId], whereIn: ['file_id' => $fileIds]),
            ['is_read' => $isRead],
            true
        );

        /** @var FileViewLog $item */
        foreach ($updated as $item) {
            $elementId = $fileIdsCollection->search(static fn(Bytes $id) => Id::create($id)->equal(Id::create($item->file_id)));
            if ($elementId !== false) {
                $fileIdsCollection->offsetUnset($elementId);
            }
        }

        if ($fileIdsCollection->count() > 0) {
            $items = $fileIdsCollection
                ->filter(
                    static fn(Bytes $fileId) => $updated->first(
                            static fn(FileViewLog $log) => Id::create($log->file_id)->getHex() === Id::create($fileId)->getHex()
                        ) === null
                )
                ->map(
                    static function ($id) use ($userId, $isRead) {
                        return [
                                'file_view_log_id' => Sxope::getNewId(),
                                'file_id' => $id,
                                'data_owner_id' => Sxope::getCurrentDataOwnerId(),
                                'user_id' => $userId,
                                'is_read' => $isRead,
                            ] + Sxope::getCreatingContext();
                    },
                )->all();

            $this->fileViewLogRepository->createBatch(array_values($items));
        }
    }
}
