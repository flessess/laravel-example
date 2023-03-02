<?php

namespace App\Services\SxopeLogService;

use App\Models\BaseModel;
use Carbon\Carbon;
use Exception;

class SxopeChangeLogLogger
{
    private static $date;
    private array $logs = [];

    public function clear(): void
    {
        $this->logs = [];
    }

    public function log(BaseModel $model, bool $isDeleted = false): void
    {
        if (!isset(self::$date)) {
            self::$date = Carbon::now();
        }

        /** @var SxopeChangeLogBuilder $changeLogBuilder */
        $changeLogBuilder = resolve(SxopeChangeLogBuilder::class);

        $log = $changeLogBuilder
            ->setModel($model)
            ->setDate(self::$date)
            ->setIsDeleted($isDeleted)
            ->build();

        $this->put($log);

    }

    public function getLogs(): array
    {
        return $this->logs;
    }

    public function save()
    {
        if (!empty($this->getLogs())) {
            SxopeChangeLogService::queueLogMessage($this->getLogs());
            $this->clear();
        }
    }

    /**
     * @param array $logs
     *
     * @throws Exception
     */
    private function put(array $logs): void
    {
        $this->logs = array_merge($this->logs, $logs);
    }
}
