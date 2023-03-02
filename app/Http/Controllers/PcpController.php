<?php

namespace App\Http\Controllers;

use App\Services\AccessControlSnapshotService;
use App\Services\SxopeApiService;
use App\Traits\SxopeApiIdHashTrait;
use Illuminate\Http\Request;
use Inventcorp\SxopeApiGatewayClient\ApiException;
use Jenssegers\Agent\Agent;

/**
 * Class PcpController.
 *
 * @package App\Http\Controllers
 */
class PcpController extends Controller
{
    use SxopeApiIdHashTrait;

    private SxopeApiService $sxopeApiService;
    private AccessControlSnapshotService $accessControlService;

    /**
     * PcpController constructor.
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
     * @return \Illuminate\Contracts\View\Factory
     *
     * @throws \Exception
     */
    public function index(Request $request)
    {
        $pageTitle = 'PCP';
        $query = $request->get('q');
        $apiToken = $this->sxopeApiService->getApiToken();
        $baseApiUrl = $this->sxopeApiService->getBaseUrl();

        // INFO: Get access from explore/pcps tab
        $tabs = $this->accessControlService->getSnapshotTabs(AccessControlSnapshotService::EXPLORE_SNAPSHOT);
        $pcpKey = array_search('pcps', array_column($tabs, 'id'));
        $allowedFields = [];

        if ($pcpKey !== false) {
            $allowedFields = $tabs[$pcpKey]['allowedFields'];
        }

        $requestUserAgent = request()->header('user-agent');
        $isDP = (bool) preg_match('#dp desktop#i', $requestUserAgent);

        return view(
            'pcps.index',
            compact(
                'pageTitle',
                'apiToken',
                'baseApiUrl',
                'query',
                'allowedFields',
                'isDP'
            )
        )
            ->with('user', auth()->user());
    }

    /**
     * @param string $pcpId
     *
     * @return \Illuminate\View\View
     *
     * @throws ApiException
     */
    public function snapshot($pcpId)
    {
        $pageTitle = 'PCP Snapshot';
        $this->validateHash($pcpId);
        $apiToken = $this->sxopeApiService->getApiToken();
        $tabs = $this->accessControlService->getSnapshotTabs(AccessControlSnapshotService::PCP_SNAPSHOT);

        // INFO: Manage access to 5star form
        $memberTabs = $this->accessControlService->getSnapshotTabs(AccessControlSnapshotService::MEMBER_SNAPSHOT);
        $fivestarKey = array_search('?5star', array_column($memberTabs, 'id'));

        $agent = new Agent();
        $isMobile = $agent->isiPhone();

        if ($fivestarKey !== false && !$memberTabs[$fivestarKey]['disabled'] && !$isMobile) {
            $membershipKey = array_search('pcp-membership-list', array_column($tabs, 'id'));
            array_push($tabs[$membershipKey]['allowedFields'], 'actions');
        }

        if (!!$isMobile) {
            $appointmentsKey = array_search('?appointments', array_column($tabs, 'id'));
            $tabs[$appointmentsKey]['disabled'] = true;
        }

        $requestUserAgent = request()->header('user-agent');
        $isDP = (bool) preg_match('#dp desktop#i', $requestUserAgent);

        $baseApiUrl = $this->sxopeApiService->getBaseUrl();

        return view(
            'pcps.snapshot',
            compact(
                'pageTitle',
                'apiToken',
                'baseApiUrl',
                'tabs',
                'isDP'
            )
        )
            ->with('pcpId', $pcpId)
            ->with('user', auth()->user());
    }
}
