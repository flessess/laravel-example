<?php

namespace App\Services\SxopeLogService;

use App\Models\BaseModel;
use Carbon\Carbon;
use DateTime;

/**
 * Convert log values from original to logging string values
 */
class SxopeChangeLogEntryBuilder
{
    public const OPERATION_TYPES = [
        self::OPERATION_TYPE_ADDED,
        self::OPERATION_TYPE_MODIFIED,
        self::OPERATION_TYPE_DELETED,
    ];

    private const OPERATION_TYPE_ADDED = 'Added';
    private const OPERATION_TYPE_MODIFIED = 'Modified';
    private const OPERATION_TYPE_DELETED = 'Deleted';

    private BaseModel $model;
    private string $attribute;
    private $oldValue;
    private $newValue;
    private ?string $modelId = null;
    private Carbon $date;
    private ?string $userId = null;

    /**
     * @param BaseModel $model
     * @return SxopeChangeLogEntryBuilder
     */
    public function setModel(BaseModel $model): SxopeChangeLogEntryBuilder
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @param string $attribute
     * @return SxopeChangeLogEntryBuilder
     */
    public function setAttribute(string $attribute): SxopeChangeLogEntryBuilder
    {
        $this->attribute = $attribute;
        return $this;
    }

    /**
     * @param mixed $oldValue
     * @return SxopeChangeLogEntryBuilder
     */
    public function setOldValue($oldValue)
    {
        $this->oldValue = $oldValue;
        return $this;
    }

    /**
     * @param mixed $newValue
     * @return SxopeChangeLogEntryBuilder
     */
    public function setNewValue($newValue)
    {
        $this->newValue = $newValue;
        return $this;
    }

    /**
     * @param string $modelId
     * @return SxopeChangeLogEntryBuilder
     */
    public function setModelId(string $modelId): SxopeChangeLogEntryBuilder
    {
        $this->modelId = $modelId;
        return $this;
    }

    /**
     * @param Carbon $date
     * @return SxopeChangeLogEntryBuilder
     */
    public function setDate(Carbon $date): SxopeChangeLogEntryBuilder
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @param string|null $userId
     * @return SxopeChangeLogEntryBuilder
     */
    public function setUserId(?string $userId): SxopeChangeLogEntryBuilder
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * Convert log values from original to logging string values
     *
     * @return array
     */
    public function build(): array
    {
        $attributePath = $this->model->getTable() . '.' . $this->attribute;
        $values = $this->model->getLogValues($this->attribute, ['from' => $this->oldValue, 'to' => $this->newValue]);

        return [
            'context' => config('app.name') . ' (' . config('app.env') . ')',
            'model_id' => $this->modelId ?? null,
            'model_type' => get_class($this->model),
            'label' => $this->model->getAttributeName($attributePath),
            'attribute' => $attributePath,
            'table_name' => $this->model->getTable(),
            'value_old' => $this->convertToString($values['from']),
            'value_new' => $this->convertToString($values['to']),
            'operation_type' => $this->getOperationType($this->oldValue, $this->newValue),
            'platform_user_id' => $this->userId,
            'changed_at' => $this->date->format(DateTime::ATOM),
            'description' => $this->model->getLogDescription($this->attribute),
        ];
    }

    private function convertToString($value)
    {
        if (empty($value)) {
            return '-';
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            return implode(', ', array_map([$this, 'convertToString'], $value));
        }

        return strval($value);
    }

    private function getOperationType($from = null, $to = null): string
    {
        if (!isset($from)) {
            return self::OPERATION_TYPE_ADDED;
        }
        if (!isset($to)) {
            return self::OPERATION_TYPE_DELETED;
        }
        return self::OPERATION_TYPE_MODIFIED;
    }
}
