<?php

namespace App\Http\Controllers;

use App\Services\AccessControlSnapshotService;
use App\Services\SxopeApiService;
use App\Traits\SxopeApiIdHashTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inventcorp\SxopeApiGatewayClient\ApiException;
use Jenssegers\Agent\Agent;

/**
 * Class MembersSxopeController.
 *
 * @package App\Http\Controllers
 */
class MembersSxopeController extends Controller
{
    use SxopeApiIdHashTrait;

    private SxopeApiService $sxopeApiService;
    private AccessControlSnapshotService $accessControlService;

    /**
     * MembersSxopeController constructor.
     *
     * @param SxopeApiService $sxopeApiService
     * @param AccessControlSnapshotService $accessControlService
     */
    public function __construct(SxopeApiService $sxopeApiService, AccessControlSnapshotService $accessControlService)
    {
        $this->sxopeApiService = $sxopeApiService;
        $this->accessControlService = $accessControlService;
    }

    /**
     * @return \Illuminate\View\View
     *
     * @throws \Exception
     */
    public function search(Request $request)
    {
        $pageTitle = 'Member Search';

        $baseApiUrl = $this->sxopeApiService->getBaseUrl();
        $apiToken = $this->sxopeApiService->getApiToken();

        $tabs = $this->accessControlService->getSnapshotTabs(AccessControlSnapshotService::EXPLORE_SNAPSHOT);
        $membersKey = array_search('members', array_column($tabs, 'id'));
        $allowedFields = [];

        $agent = new Agent();
        $isMobile = $agent->isiPhone();
        if ($membersKey !== false) {
            $allowedFields = $tabs[$membersKey]['allowedFields'];

            // INFO: Manage access to 5star form
            $memberTabs = $this->accessControlService->getSnapshotTabs(AccessControlSnapshotService::MEMBER_SNAPSHOT);
            $fivestarKey = array_search('?5star', array_column($memberTabs, 'id'));

            if ($fivestarKey !== false && !$memberTabs[$fivestarKey]['disabled'] && !$isMobile) {
                array_push($allowedFields, 'actions');

                $allowedActions = $memberTabs[$fivestarKey]['allowedActions'];
                $isAdmin = boolval(!!(array_search('admin', $allowedActions) !== false));
                $showVersions = boolval(!!(array_search('versions', $allowedActions) !== false));
                $readonly = boolval(!!(array_search('download', $allowedActions) === false));
            }
        }

        $requestUserAgent = request()->header('user-agent');
        $isDP = (bool) preg_match('#dp desktop#i', $requestUserAgent);

        $query = $request->get('q');
        $labels = $request->get('l');

        return view(
            'members.search-sxope',
            compact(
                'apiToken',
                'baseApiUrl',
                'pageTitle',
                'query',
                'labels',
                'allowedFields',
                'isAdmin',
                'showVersions',
                'readonly',
                'isDP'
            )
        );
    }

    /**
     * @param $memberUniqueId
     *
     * @return \Illuminate\View\View
     *
     * @throws ApiException
     */
    public function snapshot($memberUniqueId)
    {
        $pageTitle = 'Member Snapshot';
        $this->validateHash($memberUniqueId);
        $apiToken = $this->sxopeApiService->getApiToken();
        $tabs = $this->accessControlService->getSnapshotTabs(AccessControlSnapshotService::MEMBER_SNAPSHOT);
        $baseApiUrl = $this->sxopeApiService->getBaseUrl();

        $agent = new Agent();
        $isMobile = $agent->isiPhone();
        if (!!$isMobile) {
            $fivestarKey = array_search('?5star', array_column($tabs, 'id'));
            if ($fivestarKey !== false) {
                $tabs[$fivestarKey]['disabled'] = true;
            }
            $appointmentsKey = array_search('?appointments', array_column($tabs, 'id'));
            if ($appointmentsKey !== false) {
                $tabs[$appointmentsKey]['disabled'] = true;
            }
        }

        $requestUserAgent = request()->header('user-agent');
        $isDP = (bool) preg_match('#dp desktop#i', $requestUserAgent);
        if (!!$isDP) {
            $snapshotKey = array_search('#$show_core', array_column($tabs, 'id'));
            if ($snapshotKey !== false) {
                $tabs[$snapshotKey]['disabled'] = false;
            }
        }

        return view(
            'members.snapshot-sxope',
            compact(
                'pageTitle',
                'apiToken',
                'baseApiUrl',
                'memberUniqueId',
                'tabs',
                'isMobile',
                'isDP'
            )
        );
    }
}
