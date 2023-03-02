<?php

namespace App\Http\Controllers;

use App\Events\DataTableExportFileDownloadedEvent;
use App\Models\Audit;
use App\Models\LongReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Class LongReportController.
 *
 * @package App\Http\Controllers
 */
class LongReportController extends Controller
{
    /**
     * @var \App\Models\LongReport
     */
    protected $report;

    /**
     * LongReportController constructor.
     *
     * @param \App\Models\LongReport $report
     */
    public function __construct(LongReport $report)
    {
        $this->report = $report;
    }

    /**
     * Download report.
     *
     * @param string $hash
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function download(string $hash)
    {
        /** @var \App\Models\LongReport $report */
        $report = $this->report->findOrFail($hash);

        $this->authorize('download', $report);

        if ($report->status === LongReport::STATUS_COMPLETED) {
            activity(Audit::EVENT_DOWNLOADED, "Members Export {$report->filename}");

            $report->update([
                'downloaded_at' => Carbon::now(),
            ]);

            event(new DataTableExportFileDownloadedEvent(auth()->user(), $report));

            return Storage::disk(LongReport::STORAGE_DISK)->download($report->getPath(/*$relative*/ true), $report->filename);
        }

        throw new RuntimeException('Attempt to load the wrong report.');
    }
}
