<?php

namespace App\Listeners;

use App\Models\BaseModel;
use App\Services\SxopeLogService\SxopeChangeLogLogger;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;

class SxopeChangeLogSubscriber
{
    private SxopeChangeLogLogger $changeLogLogger;

    private int $transactionsCount = 0;
    private int $actionsCount = 0;

    public function __construct(SxopeChangeLogLogger $changeHistoryLogger)
    {
        $this->changeLogLogger = $changeHistoryLogger;
    }

    public function beforeAction(BaseModel $model)
    {
        $this->actionsCount++;
    }

    public function afterAction(BaseModel $model)
    {
        $this->actionsCount--;
        $this->trySave();
    }

    public function saved(BaseModel $model)
    {
        $this->changeLogLogger->log($model);
    }

    public function deleted(BaseModel $model)
    {
        $this->changeLogLogger->log($model, true);
    }

    /**
     * Count started transactions
     *
     * @param TransactionBeginning $event
     */
    public function onTransactionBeginning(TransactionBeginning $event)
    {
        $this->transactionsCount++;
    }

    /**
     * Count down transactions, and write change log
     *
     * @param TransactionCommitted $event
     */
    public function onTransactionCommitted(TransactionCommitted $event)
    {
        $this->transactionsCount--;
        $this->trySave();
    }

    /**
     * Reset transactions count and clean log
     *
     * @param TransactionRolledBack $event
     */
    public function onTransactionRolledBack(TransactionRolledBack $event)
    {
        $this->transactionsCount = 0;
        $this->changeLogLogger->clear();
    }

    private function trySave(): void
    {
        if ($this->actionsCount === 0 && $this->transactionsCount === 0) {
            $this->changeLogLogger->save();
        }
    }
}
