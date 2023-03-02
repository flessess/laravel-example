<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DataOwner;
use App\Models\Day;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Str;
use Sxope\Contracts\Sxope;
use Sxope\ValueObjects\Id;
use Throwable;

/**
 * Class SxopeService
 *
 * @package App\Services
 */
class SxopeService implements Sxope
{
    /**
     * @return Id
     */
    public function getCurrentDataOwnerId(): Id
    {
        return Id::createFromBytes(DataOwner::getDataOwnerId());
    }

    /**
     * @return Id
     */
    public function getCurrentUserId(): Id
    {
        $userId = getCurrentUserIdBytes();
        if (!$userId) {
            throw new \RuntimeException('Undefined current user');
        }
        return Id::createFromBytes($userId);
    }

    /** @var Id|null */
    private ?Id $currentDate = null;

    /**
     * @return Id
     */
    public function getCurrentDayId(): Id
    {
        if (!$this->currentDate) {
            $this->currentDate = Id::create(Day::getDayId());
        }
        return $this->currentDate;
    }

    /**
     * @var array|Id[]
     */
    private array $mapSpecifiedDatesToDayIds = [];

    /**
     * @param DateTimeInterface $specifiedDateTime
     * @return Id
     */
    public function getSpecifiedDayId(DateTimeInterface $specifiedDateTime): Id
    {
        $date = $specifiedDateTime->format('Y-m-d');
        $day = $this->mapSpecifiedDatesToDayIds[$date] ?? null;
        if (!$day) {
            $day = Id::createFromBytes(Day::getDayId($date));
            $this->mapSpecifiedDatesToDayIds[$date] = $day;
        }
        return $day;
    }

    public function getCreatingContext(): array
    {
        return [
            'created_at_day_id' => $this->getCurrentDayId(),
            'created_at' => Carbon::now('UTC'),
            'created_by' => $this->getCurrentUserId(),
        ];
    }

    public function getUpdatingContext(): array
    {
        return [
            'updated_at_day_id' => $this->getCurrentDayId(),
            'updated_at' => Carbon::now('UTC'),
            'updated_by' => $this->getCurrentUserId(),
        ];
    }

    public function getNewId(): Id
    {
        return Id::create(Str::uuid()->toString());
    }

    public function logException(Throwable $exception, string $logHeader = ''): void
    {
        logException($exception, $logHeader);
    }

    public function getSpecifiedContext(string $name): array
    {
        return [
            "{$name}_at_day_id" => $this->getCurrentDayId(),
            "{$name}_at" => Carbon::now('UTC'),
            "{$name}_by" => $this->getCurrentUserId(),
        ];
    }
}
