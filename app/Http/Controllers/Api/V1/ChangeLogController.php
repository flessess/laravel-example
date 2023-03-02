<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\SxopeChangeLogFilterRequest;
use App\Repository\UsersRepository;
use App\Services\SxopeLogService\SxopeChangeLogService;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Support\Collection;

class ChangeLogController extends BaseController
{
    public function index(SxopeChangeLogFilterRequest $request, UsersRepository $usersRepository)
    {
        $data = $request->validated();
        if (!empty($data['search'])) {
            unset($data['search']);
        }

        $data['context'] = SxopeChangeLogService::getAppContext();

        $data = $this->convertChangedAt($data);

        $response = $this->getDataFromBqLogService($data);

        $responseData = $this->setUserInfo(collect($response['data'] ?? []), $usersRepository);

        $response['data'] = $responseData->all();
        return $response;
    }

    private function getDataFromBqLogService(array $data): array
    {
        $result = SxopeChangeLogService::getChangeHistoryLog($data);
        if (!$result) {
            if (!is_array($result)) {
                throw new Exception('Error in getting change history log');
            }

            return [];
        }

        return $result;
    }

    private function convertChangedAt(array $data): array
    {
        if (isset($data['filters']['changed_at'])) {
            $from = $data['filters']['changed_at']['from'];
            $to = $data['filters']['changed_at']['to'];

            $from = Carbon::parse($from)->startOfDay()->format(DateTime::ATOM);
            $to = Carbon::parse($to)->endOfDay()->format(DateTime::ATOM);

            $data['filters']['changed_at']['range']['from'] = $from;
            $data['filters']['changed_at']['range']['to'] = $to;

            unset(
                $data['filters']['changed_at']['from'],
                $data['filters']['changed_at']['to']
            );
        }
        return $data;
    }

    private function setUserInfo(Collection $response, UsersRepository $usersRepository): Collection
    {
        $usersIds = $response
            ->pluck('platform_user_id')
            ->unique()
            ->filter()
            ->values();

        $users = $usersRepository->getUsersByUserIdKeyedByUserId($usersIds);

        return $response->map(function (array $record) use ($users) {
            if (isset($record['platform_user_id'])) {
                $record['platform_user'] = $users[$record['platform_user_id']] ?? null;
            }
            return $record;
        });
    }
}
