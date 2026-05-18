<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $bruteForceIps = AuditLog::where('event', 'failed login')
            ->where('created_at', '>=', now()->subHour())
            ->groupBy('ip_address')
            ->havingRaw('COUNT(*) >= 3')
            ->pluck('ip_address')
            ->toArray();

        $rapidFireIps = AuditLog::where('created_at', '>=', now()->subMinute())
            ->groupBy('ip_address')
            ->havingRaw('COUNT(*) >= 10')
            ->pluck('ip_address')
            ->toArray();

        $rapidFireUsers = AuditLog::where('created_at', '>=', now()->subMinute())
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) >= 10')
            ->pluck('user_id')
            ->toArray();

        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 25;
        $logs = $this->query($request)
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());

        return view('admin.audit.index', compact('logs', 'bruteForceIps', 'rapidFireIps', 'rapidFireUsers'));
    }

    public function show(AuditLog $auditLog)
    {
        return view('admin.audit.show', compact('auditLog'));
    }

    public function clearAll()
    {
        AuditLog::truncate();

        return back()->with('success', 'All audit logs have been cleared.');
    }

    public function export(Request $request)
    {
        $format = $request->input('format', 'csv');
        $maxRows = $format === 'pdf' ? 500 : 50000;
        $logs = $this->query($request)->latest()->limit($maxRows)->get();

        if ($format === 'pdf') {
            return Pdf::loadView('admin.audit.export-pdf', compact('logs'))
                ->download('audit-logs-' . now()->format('Y-m-d') . '.pdf');
        }

        $filename = 'audit-logs-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Event', 'User', 'Auditable', 'IP', 'Checksum Valid']);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    optional($log->created_at)->toDateTimeString(),
                    $log->event,
                    $log->user?->email,
                    trim(($log->auditable_type ?? '') . ' #' . ($log->auditable_id ?? '')),
                    $log->ip_address,
                    $log->isChecksumValid() ? 'yes' : 'no',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function query(Request $request)
    {
        return AuditLog::query()
            ->with('user')
            ->when(!$request->boolean('archived'), fn ($query) => $query->whereNull('archived_at'))
            ->when($request->filled('event'), fn ($query) => $query->where('event', 'like', '%' . $request->event . '%'))
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->user_id))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date_to));
    }
}
