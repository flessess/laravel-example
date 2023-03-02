<?php

namespace App\Http\Controllers;

use App\Services\AccessControlSnapshotService;
use App\Services\SxopeApiService;
use App\Traits\SxopeApiIdHashTrait;
use Illuminate\Http\RedirectResponse;
use Inventcorp\SxopeApiGatewayClient\ApiException;
use Jenssegers\Agent\Agent;

/**
 * Class ExploreController.
 *
 * @package App\Http\Controllers
 */
class ExploreController extends Controller
{
    use SxopeApiIdHashTrait;

    private SxopeApiService $sxopeApiService;
    private AccessControlSnapshotService $accessControlService;

    /**
     * ExploreController constructor.
     *
     * @param SxopeApiService $sxopeApiService
     * @param AccessControlSnapshotService $accessControlService
     */
    public function __construct(SxopeApiService $sxopeApiService, AccessControlSnapshotService $accessControlService)
    {
        $this->sxopeApiService = $sxopeApiService;
        $this->accessControlService = $accessControlService;
    }

    public function oldSnapshotSearch() {
        return redirect()->route('patient-snapshot.index');
    }

    public function oldSnapshot($memberUniqueId, $tabName = null, $subTabName = null) {
        $newRoute = '/patient-snapshot';
        if ($memberUniqueId) {
            $newRoute .= '/';
            $newRoute .= $memberUniqueId;
        }
        if ($tabName) {
            $newRoute .= '/';
            $newRoute .= $tabName;
        }
        if ($subTabName) {
            $newRoute .= '/';
            $newRoute .= $subTabName;
        }
        return redirect($newRoute);
    }

    /**
     * @return \Illuminate\View\View
     *
     * @throws ApiException
     */
    public function snapshot()
    {
        $apiToken = $this->sxopeApiService->getApiToken();
        $baseApiUrl = $this->sxopeApiService->getBaseUrl();

        $agent = new Agent();
        $isMobile = $agent->isiPhone() || $agent->isMobile();

        $requestUserAgent = request()->header('user-agent');
        $isDP = (bool) preg_match('#dp desktop#i', $requestUserAgent);

        $exploreTabs = $this->accessControlService->getSnapshotTabs(AccessControlSnapshotService::EXPLORE_SNAPSHOT);
        $pcpTabs = $this->accessControlService->getSnapshotTabs(AccessControlSnapshotService::PCP_SNAPSHOT);
        $memberTabs = $this->accessControlService->getSnapshotTabs(AccessControlSnapshotService::MEMBER_SNAPSHOT);

        // INFO: Manage access to 5star form
        $fivestarKey = array_search('?5star', array_column($memberTabs, 'id'));
        if ($fivestarKey !== false && !$memberTabs[$fivestarKey]['disabled'] && !$isMobile) {
            $membersKey = array_search('patients', array_column($exploreTabs, 'id'));
            array_push($exploreTabs[$membersKey]['allowedFields'], 'actions');
            $pcpMembershipKey = array_search('pcp-membership-list', array_column($pcpTabs, 'id'));
            array_push($pcpTabs[$pcpMembershipKey]['allowedFields'], 'actions');
        }

        // INFO: Manage access to Preferred Providers
        $providersKey = array_search('preferred-providers', array_column($memberTabs, 'id'));
        if ($providersKey !== false && !$memberTabs[$providersKey]['disabled']
            && !$isMobile && !$isDP
            && config('app.env') === 'local') {
            $memberTabs[$providersKey]['allowedFields'] = [
                'address',
                'id',
                'lat',
                'lng',
                'name'
            ];
        }

        // INFO: Disable wide modals for phones
        if (!!$isMobile) {
            $pcpAppointmentsKey = array_search('?appointments', array_column($pcpTabs, 'id'));
            if ($pcpAppointmentsKey !== false) {
                $pcpTabs[$pcpAppointmentsKey]['disabled'] = true;
            }
            $memberFivestarKey = array_search('?5star', array_column($memberTabs, 'id'));
            if ($memberFivestarKey !== false) {
                $memberTabs[$memberFivestarKey]['disabled'] = true;
            }
            $memberAppointmentsKey = array_search('?appointments', array_column($memberTabs, 'id'));
            if ($memberAppointmentsKey !== false) {
                $memberTabs[$memberAppointmentsKey]['disabled'] = true;
            }
        }

        // INFO: enable sxope link for DP
        if (!!$isDP) {
            $snapshotKey = array_search('#$show_core', array_column($memberTabs, 'id'));
            if ($snapshotKey !== false) {
                $memberTabs[$snapshotKey]['disabled'] = false;
            }
        }

        // INFO: set allowedFields for PCP Search
        $fieldsPcpKey = array_search('pcps', array_column($exploreTabs, 'id'));
        $pcpSearchAllowedFields = [];
        if ($fieldsPcpKey !== false) {

            $pcpSearchAllowedFields = isset($exploreTabs[$fieldsPcpKey]['allowedFields']['accelerated'])
            ? $exploreTabs[$fieldsPcpKey]['allowedFields']['accelerated']
            : $exploreTabs[$fieldsPcpKey]['allowedFields'];
        }

        // INFO: set allowedFields for Member Search
        $fieldsMemberKey = array_search('patients', array_column($exploreTabs, 'id'));
        $memberSearchAllowedFields = [];
        $fiveStarIsAdmin = 0;
        $fiveStarShowVersions = 0;
        $fiveStarReadonly = 1;
        if ($fieldsMemberKey !== false) {
            $memberSearchAllowedFields = $exploreTabs[$fieldsMemberKey]['allowedFields'];

            // INFO: Manage access to 5star form opened from Member Search
            $fieldsFivestarKey = array_search('?5star', array_column($memberTabs, 'id'));

            if ($fieldsFivestarKey !== false && !$memberTabs[$fieldsFivestarKey]['disabled'] && !$isMobile) {
                array_push($memberSearchAllowedFields, 'actions');

                $fieldsAllowedActions = $memberTabs[$fieldsFivestarKey]['allowedActions'];
                $fiveStarIsAdmin = boolval(!!(array_search('admin', $fieldsAllowedActions) !== false));
                $fiveStarShowVersions = boolval(!!(array_search('versions', $fieldsAllowedActions) !== false));
                $fiveStarReadonly = boolval(!!(array_search('download', $fieldsAllowedActions) === false));
            }
        }

        // INFO: add secret rawDataKey for DP
        $rawDataKey = '';
        if (!!$isDP) {
            $rawDataKey = config('app.raw_data_key');
        }

        return view(
            'explore.snapshot',
            compact(
                'apiToken',
                'baseApiUrl',
                'exploreTabs',
                'pcpTabs',
                'memberTabs',
                'pcpSearchAllowedFields',
                'memberSearchAllowedFields',
                'fiveStarIsAdmin',
                'fiveStarShowVersions',
                'fiveStarReadonly',
                'rawDataKey',
                'isDP',
                'isMobile'
            )
        )
            ->with('user', auth()->user());
    }
}
