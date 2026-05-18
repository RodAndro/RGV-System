<?php

namespace App\Http\Controllers\Admin;

use App\Exports\BookingsExport;
use App\Exports\InventoryExport;
use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateExport;
use App\Jobs\ProcessInventoryImport;
use App\Jobs\ProcessUsersImport;
use App\Models\Booking;
use App\Models\ExportLog;
use App\Models\ImportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportExportController extends Controller
{
    public function index()
    {
        return view('admin.import-export.index', [
            'imports' => ImportLog::latest()->paginate(10, ['*'], 'imports_page'),
            'exports' => ExportLog::latest()->paginate(10, ['*'], 'exports_page'),
        ]);
    }

    public function importInventory(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:51200'],
            'duplicate_strategy' => ['required', 'in:skip,update'],
        ]);

        $file = $validated['file'];
        $path = $file->store('imports', 'local');

        $log = ImportLog::create([
            'user_id' => $request->user()?->id,
            'type' => 'inventory',
            'file_name' => $file->getClientOriginalName(),
            'status' => 'queued',
            'duplicate_strategy' => $validated['duplicate_strategy'],
        ]);

        ProcessInventoryImport::dispatch($log->id, Storage::disk('local')->path($path), $validated['duplicate_strategy']);

        return back()->with('success', 'Inventory import queued. Progress will update as the queue processes it.');
    }

    public function importUsers(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:51200'],
        ]);

        $file = $validated['file'];
        $path = $file->store('imports', 'local');

        $log = ImportLog::create([
            'user_id' => $request->user()?->id,
            'type' => 'users',
            'file_name' => $file->getClientOriginalName(),
            'status' => 'queued',
        ]);

        ProcessUsersImport::dispatch($log->id, Storage::disk('local')->path($path));

        return back()->with('success', 'User import queued.');
    }

    public function previewImport(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:51200'],
            'type' => ['required', 'in:inventory,users'],
            'duplicate_strategy' => ['nullable', 'in:skip,update'],
        ]);

        $file = $validated['file'];
        $type = $validated['type'];
        $path = $file->store('imports', 'local');
        $fullPath = Storage::disk('local')->path($path);

        try {
            if ($type === 'inventory') {
                $preview = $this->previewInventoryImport($fullPath);
            } else {
                $preview = $this->previewUsersImport($fullPath);
            }

            Storage::disk('local')->delete($path);

            return response()->json($preview);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function importStatus(ImportLog $importLog)
    {
        return response()->json([
            'id' => $importLog->id,
            'status' => $importLog->status,
            'progress' => $importLog->progress(),
            'processed_rows' => $importLog->processed_rows,
            'successful_rows' => $importLog->successful_rows,
            'failed_rows' => $importLog->failed_rows,
            'error_report_path' => $importLog->error_report_path,
        ]);
    }

    public function downloadImportErrors(ImportLog $importLog)
    {
        abort_unless($importLog->error_report_path && Storage::disk('local')->exists($importLog->error_report_path), 404);

        return Storage::disk('local')->download($importLog->error_report_path);
    }

    public function exportInventory(Request $request)
    {
        $filters = $request->only(['category_id', 'status', 'stock_status', 'min_price', 'max_price', 'date_from', 'date_to']);
        $columns = $this->columnsFromRequest($request);
        $format = $request->input('format', 'xlsx');
        $query = InventoryExport::buildQuery($filters);
        $count = (clone $query)->count();

        return $this->exportOrQueue(
            $request,
            'inventory',
            $format,
            $filters,
            $columns,
            $count,
            fn () => new InventoryExport($filters, $columns),
            'inventory-export-' . now()->format('Y-m-d') . '.' . $format
        );
    }

    public function exportBookings(Request $request)
    {
        $filters = $request->only(['status', 'customer', 'date_from', 'date_to']);
        $columns = $this->columnsFromRequest($request);
        $format = $request->input('format', 'xlsx');
        $query = BookingsExport::buildQuery($filters);
        $count = (clone $query)->count();

        return $this->exportOrQueue(
            $request,
            'bookings',
            $format,
            $filters,
            $columns,
            $count,
            fn () => new BookingsExport($filters, $columns),
            'bookings-export-' . now()->format('Y-m-d') . '.' . $format
        );
    }

    public function exportUsers(Request $request)
    {
        $format = $request->input('format', 'xlsx');
        abort_if($format === 'pdf', 422, 'User PDF export is not available; use XLSX or CSV.');
        $count = \App\Models\User::count();

        return $this->exportOrQueue(
            $request,
            'users',
            $format,
            ['pii_redacted' => true],
            [],
            $count,
            fn () => new UsersExport(true),
            'users-redacted-' . now()->format('Y-m-d') . '.' . $format
        );
    }

    public function customerInvoice(Booking $booking)
    {
        return app(\App\Http\Controllers\PdfExportController::class)->exportBooking($booking);
    }

    public function downloadExport(ExportLog $exportLog)
    {
        abort_unless($exportLog->file_path && Storage::disk('local')->exists($exportLog->file_path), 404);

        return Storage::disk('local')->download($exportLog->file_path);
    }

    private function exportOrQueue(Request $request, string $type, string $format, array $filters, array $columns, int $count, callable $exportFactory, string $fileName)
    {
        abort_unless(in_array($format, ['xlsx', 'csv', 'pdf', 'json', 'xml'], true), 422);

        $log = ExportLog::create([
            'user_id' => $request->user()?->id,
            'type' => $type,
            'format' => $format,
            'status' => $count > 10000 ? 'queued' : 'completed',
            'filters' => $filters,
            'columns' => $columns,
            'record_count' => $count,
        ]);

        if ($count > 10000) {
            GenerateExport::dispatch($log->id);

            return back()->with('success', 'Large export queued. You can download it from the export log once completed.');
        }

        if ($format === 'pdf') {
            GenerateExport::dispatchSync($log->id);
            $log->refresh();

            return $this->downloadExport($log);
        }

        if ($format === 'json') {
            $export = $exportFactory();
            $data = $export->query()->get()->map(fn ($row) => $export->map($row));
            return response()->json($data)->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        }

        if ($format === 'xml') {
            $export = $exportFactory();
            $data = $export->query()->get()->map(fn ($row) => $export->map($row))->toArray();
            $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><root></root>');
            $this->arrayToXml($data, $xml);
            return response($xml->asXML(), 200, [
                'Content-Type' => 'application/xml',
                'Content-Disposition' => 'attachment; filename="' . str_replace('.json', '.xml', $fileName) . '"',
            ]);
        }

        return Excel::download($exportFactory(), $fileName);
    }

    private function arrayToXml(array $data, \SimpleXMLElement &$xml): void
    {
        foreach ($data as $key => $value) {
            $key = is_int($key) ? 'item' : $key;
            if (is_array($value)) {
                $child = $xml->addChild($key);
                $this->arrayToXml($value, $child);
            } else {
                $xml->addChild($key, htmlspecialchars((string) $value));
            }
        }
    }

    private function columnsFromRequest(Request $request): array
    {
        $columns = $request->input('columns', []);

        if (is_string($columns)) {
            return array_filter(array_map('trim', explode(',', $columns)));
        }

        return is_array($columns) ? $columns : [];
    }

    private function previewInventoryImport(string $path): array
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        $headers = array_map('strtolower', array_shift($rows));

        $validRows = [];
        $invalidRows = [];

        foreach (array_slice($rows, 0, 20) as $index => $row) {
            $data = array_combine($headers, array_pad($row, count($headers), null));
            $normalized = $this->normalizePreviewRow($data);

            $errors = $this->validatePreviewRow($normalized, 'inventory');
            if (empty($errors)) {
                $validRows[] = ['row' => $index + 2, 'data' => $normalized];
            } else {
                $invalidRows[] = ['row' => $index + 2, 'errors' => $errors];
            }
        }

        return [
            'headers' => $headers,
            'valid' => count($validRows),
            'invalid' => count($invalidRows),
            'total' => min(count($rows), 20),
            'valid_rows' => $validRows,
            'invalid_rows' => $invalidRows,
        ];
    }

    private function previewUsersImport(string $path): array
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        $headers = array_map('strtolower', array_shift($rows));

        $validRows = [];
        $invalidRows = [];

        foreach (array_slice($rows, 0, 20) as $index => $row) {
            $data = array_combine($headers, array_pad($row, count($headers), null));
            $errors = $this->validatePreviewRow($data, 'users');
            if (empty($errors)) {
                $validRows[] = ['row' => $index + 2, 'data' => $data];
            } else {
                $invalidRows[] = ['row' => $index + 2, 'errors' => $errors];
            }
        }

        return [
            'headers' => $headers,
            'valid' => count($validRows),
            'invalid' => count($invalidRows),
            'total' => min(count($rows), 20),
            'valid_rows' => $validRows,
            'invalid_rows' => $invalidRows,
        ];
    }

    private function normalizePreviewRow(array $row): array
    {
        $mapped = [];

        foreach ($row as $key => $value) {
            $normalizedKey = strtolower(trim((string) $key));
            $value = is_string($value) ? trim($value) : $value;

            if (in_array($normalizedKey, ['isbn', 'item_code', 'code'], true)) {
                $mapped['item_code'] = $value;
            } elseif (in_array($normalizedKey, ['title', 'name'], true)) {
                $mapped['name'] = $value;
            } elseif (in_array($normalizedKey, ['stock', 'quantity', 'qty'], true)) {
                $mapped['quantity'] = (int) $value;
            } elseif (in_array($normalizedKey, ['price', 'unit_cost', 'cost'], true)) {
                $mapped['unit_cost'] = (float) $value;
            } elseif ($normalizedKey === 'category') {
                $mapped['category'] = $value;
            }
        }

        return $mapped;
    }

    private function validatePreviewRow(array $data, string $type): array
    {
        $errors = [];

        if ($type === 'inventory') {
            if (empty($data['name'] ?? null)) {
                $errors[] = 'Name is required';
            }
            if (isset($data['quantity']) && (!is_numeric($data['quantity']) || $data['quantity'] < 0)) {
                $errors[] = 'Quantity must be 0 or greater';
            }
            if (isset($data['unit_cost']) && (!is_numeric($data['unit_cost']) || $data['unit_cost'] < 0)) {
                $errors[] = 'Unit cost must be 0 or greater';
            }
        } else {
            if (empty($data['name'] ?? null)) {
                $errors[] = 'Name is required';
            }
            if (empty($data['email'] ?? null) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Valid email is required';
            }
        }

        return $errors;
    }
}
