<?php

namespace App\Services\SxopeLogService;

use App\Models\BaseModel;
use App\Services\BaseSpanner\SpannerBinaryUuid;
use App\Services\BaseSpanner\SpannerNumeric;
use Carbon\Carbon;
use Google\Cloud\Spanner\Bytes;
use Ramsey\Uuid\Uuid;

class SxopeChangeLogBuilder
{
    private BaseModel $model;
    private Carbon $date;
    private bool $isDeleted = false;

    /**
     * @param BaseModel $model
     * @return SxopeChangeLogBuilder
     */
    public function setModel(BaseModel $model): SxopeChangeLogBuilder
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @param Carbon $date
     * @return SxopeChangeLogBuilder
     */
    public function setDate(Carbon $date): SxopeChangeLogBuilder
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @param bool $isDeleted
     * @return SxopeChangeLogBuilder
     */
    public function setIsDeleted(bool $isDeleted): SxopeChangeLogBuilder
    {
        $this->isDeleted = $isDeleted;
        return $this;
    }

    public function build(): array
    {
        $log = [];

        if (empty($this->model->getLogAttributes())) {
            return $log;
        }

        $userId = auth()->user() ? auth()->user()->{config('access-control.sxope_user_id_field_name')} : null;
        $userId = $this->convertIdToString($userId);

        foreach ($this->model->getModelKey() as $modelId) {
            if (empty($modelId)) {
                continue;
            }

            $modelId = $this->convertIdToString($modelId);
            foreach ($this->model->getLogAttributes() as $attribute) {
                $old = $this->model->getOriginal($attribute);
                if ($this->isDeleted) {
                    $new = null;
                } else {
                    $new = $this->model->{$attribute} ?? null;
                }

                if ($this->isNeedToSkipValue($attribute, $old, $new)) {
                    continue;
                }

                $changes = $this->getChanges($old, $new);

                foreach ($changes as [$old, $new]) {
                    /** @var SxopeChangeLogEntryBuilder $logEntryBuilder */
                    $logEntryBuilder = resolve(SxopeChangeLogEntryBuilder::class);

                    $log[] = $logEntryBuilder
                        ->setModel($this->model)
                        ->setModelId($modelId)
                        ->setNewValue($new)
                        ->setOldValue($old)
                        ->setAttribute($attribute)
                        ->setDate($this->date)
                        ->setUserId($userId)
                        ->build();
                }
            }
        }

        return $log;
    }

    private function convertIdToString($id): ?string
    {
        if ($id instanceof SpannerBinaryUuid || $id instanceof SpannerNumeric) {
            return (string) $id;
        } elseif ($id instanceof Bytes) {
            $id = Uuid::fromBytes($id->get())->toString();
        } elseif (is_int($id)) {
            $id = (string) $id;
        }

        return $id;
    }

    private function getChanges($oldValue, $newValue): array
    {
        $changes = [];

        if (!is_array($oldValue) && !is_array($newValue)) {
            $changes[] = [$oldValue, $newValue];
        }

        $compare = SxopeChangeLogDataCompare::compare($oldValue ?? [], $newValue ?? []);

        SxopeChangeLogDataCompare::parseArray(
            $compare,
            function ($insert) use (&$changes) {
                $changes[] = [null, $insert];
            },
            function ($key, $update) {
            },
            function ($delete) use (&$changes) {
                $changes[] = [$delete, null];
            }
        );

        return $changes;
    }

    private function isNeedToSkipValue(string $attribute, $oldValue, $newValue): bool
    {
        if ($attribute === 'is_deleted' && (is_null($oldValue) || is_null($newValue))) {
            return true;
        }

        if ($this->compareIsEqual($oldValue, $newValue)) {
            return true;
        }

        return false;
    }

    /**
     * Compare old and new values.
     * Now compared bytes, arrays, strings and numbers.
     *
     * @param mixed $oldValue Old property value
     * @param mixed $newValue New property value
     *
     * @return bool Equals or not
     */
    private function compareIsEqual($oldValue, $newValue): bool
    {
        if (is_array($oldValue) || is_array($newValue)) {
            $compare = SxopeChangeLogDataCompare::compare($oldValue ?? [], $newValue ?? []);
            return count($compare) === 0;
        }
        if ($oldValue instanceof Bytes || $newValue instanceof Bytes) {
            return (string) $oldValue === (string) $newValue;
        }
        if ($oldValue instanceof SpannerNumeric || $newValue instanceof SpannerNumeric) {
            return (string) $oldValue === (string) $newValue;
        }
        if (is_string($oldValue) || is_string($newValue)) {
            if (
                (!isset($oldValue) || trim($oldValue) === '') &&
                (!isset($newValue) || trim($newValue) === '')
            ) {
                return true;
            }
        }
        return $oldValue === $newValue;
    }
}
