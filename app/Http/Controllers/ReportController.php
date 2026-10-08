<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Host;
use App\Models\Report;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function index()
    {
        $reports = Report::latest('created_at')
            ->paginate(15);

        return view('reports.index', compact('reports'));
    }

    public function create()
    {
        $hosts = Host::all();
        $devices = Device::all();

        return view('reports.create', compact('hosts', 'devices'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:host,device,summary',
            'host_id' => 'nullable|exists:hosts,id',
            'device_id' => 'nullable|exists:devices,id',
            'period_days' => 'required|integer|min:1|max:365',
        ]);

        $periodEnd = now();
        $periodStart = $periodEnd->copy()->subDays($validated['period_days']);

        $report = match ($validated['type']) {
            'host' => $this->reportService->generateHostReport(
                Host::findOrFail($validated['host_id']),
                $periodStart,
                $periodEnd
            ),
            'device' => $this->reportService->generateDeviceReport(
                Device::findOrFail($validated['device_id']),
                $periodStart,
                $periodEnd
            ),
            'summary' => $this->reportService->generateSummaryReport($periodStart, $periodEnd),
        };

        return redirect()
            ->route('reports.show', $report)
            ->with('success', 'Report generated successfully');
    }

    public function show(Report $report)
    {
        return view('reports.show', compact('report'));
    }

    public function download(Report $report)
    {
        $path = $this->reportService->exportReportAsCsv($report);

        return Storage::disk('local')->download($path, $report->name . '.csv');
    }

    public function destroy(Report $report)
    {
        if ($report->file_path && Storage::disk('local')->exists($report->file_path)) {
            Storage::disk('local')->delete($report->file_path);
        }

        $report->delete();

        return redirect()
            ->route('reports.index')
            ->with('success', 'Report deleted successfully');
    }
}
