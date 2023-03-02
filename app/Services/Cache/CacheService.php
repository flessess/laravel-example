<?php

declare(strict_types=1);

namespace App\Services\Cache;

use App\Api\DataApi\Contracts\DataApiClientInterface;
use App\Api\DataApi\Facades\GatewayDataApiClientFacade;
use App\Api\GatewayApi\GatewayApiClient;
use App\Api\Iam\Dto\Groups\Group;
use App\Api\Iam\Dto\Roles\Role;
use App\Api\Iam\Dto\Users\UserGroup;
use App\Api\Iam\Dto\Users\UserRole;
use App\Api\Iam\Facades\GatewayIamApiClientFacade;
use App\Api\Iam\Facades\IamApiClientFacade;
use App\Api\PersonaApi\Models\Pcp;
use App\Api\PersonaApi\PersonaApiClientFacade;
use App\Api\SphereApi\Dto\Payer;
use App\Services\MasterOutbox\Dto\PcpAllowedEntitiesDto;
use App\Services\MasterOutbox\Dto\UserAllowedEntitiesDto;
use Sxope\Exceptions\SxopeDomainException;
use Sxope\Facades\Debugger;
use Sxope\Facades\SxopeLogger;
use Sxope\ValueObjects\Id;

class CacheService
{
    public function __construct(
        private readonly DataApiClientInterface $dataApiClient,
        private readonly GatewayApiClient $gatewayApi
    ) {
    }

    public function warmUpPcps(): void
    {
        SxopeLogger::addLabels([
            'action_type' => 'cache',
            'action' => 'cache.warmup_pcps_allowed_entities',
        ]);
        $description = 'Caching pcps allowed entities';
        SxopeLogger::beginProfile($description);

        $pcpsData = $this->dataApiClient->getPcpsData();
        $personaPcps = collect(PersonaApiClientFacade::getPcpList()->data)->keyBy(static fn (Pcp $pcp) => (int) $pcp->pcpNpi);

        foreach ($pcpsData->data as $pcp) {
            $pcpId = Id::create($pcp->pcpId);
            $groups = [];

            if ($personaPcps->has($pcp->npi)) {
                foreach ($personaPcps->get($pcp->npi)->pcpGroups as $item) {
                    $groups[] = Id::create($item['id'])->getHex();
                }
            }

            $dto = new PcpAllowedEntitiesDto(
                $pcpId->getHex(),
                $groups,
                $pcpsData->getActivePayers(),
                $pcp->pcpName
            );
            CacheAccessorServiceFacade::setPcpAllowedEntities($dto);
        }

        SxopeLogger::endProfile(
            $description,
            [
                'pcp_count' => count($pcpsData->data),
            ],
            [
                'action_type' => 'cache',
                'action' => 'cache.warmup_pcps_allowed_entities',
            ]
        );
    }

    public function warmupUserGroups(): void
    {
        $token = 'Warmup user groups cache';
        SxopeLogger::beginProfile($token);

        $groups = IamApiClientFacade::getGroups();

        /** @var Group $datum */
        foreach ($groups->data as $datum) {
            CacheAccessorServiceFacade::setUserGroup($datum);
        }

        SxopeLogger::endProfile(
            $token,
            ['user_group_count' => count($groups->data)],
            [
                'action_type' => 'cache',
                'action' => 'cache.warmup_user_groups',
            ]
        );
    }

    public function warmupUserRoles(): void
    {
        $token = 'Warmup user roles cache';
        SxopeLogger::beginProfile($token);

        $roles = IamApiClientFacade::getRoles();

        /** @var Role $datum */
        foreach ($roles->data as $datum) {
            CacheAccessorServiceFacade::setUserRole($datum);
        }

        SxopeLogger::endProfile(
            $token,
            ['user_roles_count' => count($roles->data)],
            [
                'action_type' => 'cache',
                'action' => 'cache.warmup_user_roles',
            ]
        );
    }

    public function getUserEntitiesData(Id $userId): UserAllowedEntitiesDto
    {
        $cached = CacheAccessorServiceFacade::getUserAllowedEntities($userId);

        if ($cached !== null) {
            return $cached;
        }

        $userPcpData = GatewayDataApiClientFacade::getPcpsData();

        if ($userPcpData->data === []) {
            throw new SxopeDomainException('Pcp id for current user was not found.');
        }
        $pcpsAllowedEntities = CacheAccessorServiceFacade::getPcpsAllowedEntities($userPcpData->getPcpIds());

        $pcpIds = [];
        $pcpGroupIds = [];
        $activePayerIds = [];
        /** @var PcpAllowedEntitiesDto $pcpsAllowedEntity */
        foreach ($pcpsAllowedEntities as $key => $pcpsAllowedEntity) {
            if ($pcpsAllowedEntity === null) {
                Debugger::warning(
                    "pcp {$key} was not found",
                    [
                        'pcp_id' => $key,
                    ],
                    [
                        'action_type' => 'cache',
                        'action' => 'cache.get_user_pcp_data',
                    ]
                );
                continue;
            }

            $pcpIds[$pcpsAllowedEntity->pcpId] = $pcpsAllowedEntity->pcpId;

            foreach ($pcpsAllowedEntity->groups as $item) {
                $pcpGroupIds[$item] = $item;
            }

            if (!empty($pcpsAllowedEntity->activePayerIds)) {
                $activePayerIds = array_merge($activePayerIds, array_diff($pcpsAllowedEntity->activePayerIds, $activePayerIds));
            }
        }
        $userData = GatewayIamApiClientFacade::getUserData($userId);
        $userRoles = array_map(
            static fn (UserRole $role) => Id::create($role->id)->getHex(), $userData?->roles ?? []
        );
        $userGroups = array_map(
            static fn (UserGroup $group) => Id::create($group->id)->getHex(), $userData?->groups ?? []
        );
        $fullAccess = false;
        if (in_array(config('app.ops_admin_user_role_id'), $userRoles, true)) {
            $fullAccess = true;
        }

        foreach ($userGroups as $userGroup) {
            if (in_array($userGroup, [config('app.root_user_group'), config('app.super_admin_group_id')], true)) {
                $fullAccess = true;
            }
        }

        $dto = new UserAllowedEntitiesDto(
            $userId->getHex(),
            array_values($pcpIds),
            array_values($pcpGroupIds),
            $activePayerIds,
            $userGroups,
            $userRoles,
            $fullAccess
        );

        CacheAccessorServiceFacade::setUserAllowedEntities($dto);

        return $dto;
    }

    public function warmupUsers(): void
    {
        $token = 'Warmup users cache';
        SxopeLogger::beginProfile($token);

        $users = IamApiClientFacade::getUsers();

        foreach ($users->data as $datum) {
            CacheAccessorServiceFacade::setUser($datum);
        }

        SxopeLogger::endProfile(
            $token,
            ['users_count' => count($users->data)],
            [
                'action_type' => 'cache',
                'action' => 'cache.warmup_users',
            ]
        );
    }

    public function warmupPcpGroups(): void
    {
        $token = 'Warmup pcp groups cache';
        SxopeLogger::beginProfile($token);

        $groups = PersonaApiClientFacade::getPcpGroups();

        foreach ($groups->data as $datum) {
            CacheAccessorServiceFacade::setPcpGroup($datum);
        }

        SxopeLogger::endProfile(
            $token,
            ['pcp_group_count' => count($groups->data)],
            [
                'action_type' => 'cache',
                'action' => 'cache.warmup_pcp_groups',
            ]
        );
    }

    public function warmupPayers(): void
    {
        $token = 'Warmup payers cache';
        SxopeLogger::beginProfile($token);

        $payers = $this->gatewayApi->getPayers();

        foreach ($payers->getData() as $datum) {
            CacheAccessorServiceFacade::setPayer(new Payer($datum->getPayerId(), $datum->getPayerName()));
        }

        SxopeLogger::endProfile(
            $token,
            ['payers_count' => count($payers->getData())],
            [
                'action_type' => 'cache',
                'action' => 'cache.warmup_payers',
            ]
        );
    }
}
