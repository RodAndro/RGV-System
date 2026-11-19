<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BorrowRequest;
use App\Models\ChatbotInteraction;
use App\Models\Inventory;
use App\Services\OllamaService;
use App\Support\SqlDialect;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Versioned AI endpoints.
 *
 * Requirement T2: the enabling technology is reachable only through the
 * team's REST API. The client never holds model files or service keys.
 *
 * Every call is recorded for traceability (T4): input reference, output,
 * confidence, model name, timestamp and user.
 */
class AiInsightController extends Controller
{
    public function __construct(private readonly OllamaService $ollama) {}

    /**
     * Aggregate business insights (admin only).
     */
    public function insights(Request $request): JsonResponse
    {
        $data = [
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'approved_bookings' => Booking::where('status', 'approved')->count(),
            'completed_bookings' => Booking::where('status', 'completed')->count(),
            'total_inventory' => Inventory::count(),
            'low_stock_items' => Inventory::lowStock()->count(),
            'total_borrow_requests' => BorrowRequest::count(),
            'pending_borrow_requests' => BorrowRequest::where('status', 'pending')->count(),
            'approved_borrow_requests' => BorrowRequest::where('status', 'approved')->count(),
            'overdue_borrow_requests' => BorrowRequest::where('status', 'borrowed')
                ->whereDate('due_date', '<', now()->toDateString())
                ->count(),
        ];

        return $this->respond(
            $request,
            'insights',
            $data,
            'Analyze the following business data for RGV Multi-Tech Services and provide concise, actionable insights.',
            fn (array $data) => $this->fallbackInsights($data),
        );
    }

    /**
     * Booking forecast (admin only).
     */
    public function forecast(Request $request): JsonResponse
    {
        $monthExpression = SqlDialect::monthExpression('preferred_date', 'YYYY-MM');

        $monthly = Booking::query()
            ->selectRaw("{$monthExpression} as month, COUNT(*) as count")
            ->where('preferred_date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => ['month' => (string) $row->month, 'count' => (int) $row->count])
            ->all();

        return $this->respond(
            $request,
            'forecast',
            ['monthly_bookings' => $monthly],
            'Based on the following monthly booking counts, provide a three-month forecast.',
            fn (array $data) => $this->fallbackForecast($data['monthly_bookings']),
        );
    }

    /**
     * Inventory reorder recommendations (admin only).
     */
    public function inventoryRecommendations(Request $request): JsonResponse
    {
        $items = Inventory::query()
            ->with('category:id,name')
            ->withCount('borrowItems')
            ->get()
            ->map(fn (Inventory $item) => [
                'name' => $item->name,
                'category' => $item->category?->name,
                'quantity' => (int) $item->quantity,
                'threshold' => (int) $item->low_stock_threshold,
                'status' => $item->status,
                'borrow_count' => (int) $item->borrow_items_count,
            ])
            ->all();

        return $this->respond(
            $request,
            'inventory-recommendations',
            ['items' => $items],
            'Based on the following inventory data, recommend stock management and reordering actions.',
            fn (array $data) => $this->fallbackInventory($data['items']),
        );
    }

    /**
     * Call the model, record the interaction, and fall back deterministically
     * when it is unavailable (T3).
     *
     * @param  callable(array<string, mixed>): string  $fallback
     */
    private function respond(
        Request $request,
        string $reportType,
        array $data,
        string $instruction,
        callable $fallback,
    ): JsonResponse {
        $startedAt = hrtime(true);
        $prompt = $instruction.' '.json_encode($data);
        $system = 'You are a business analyst for RGV Multi-Tech Services. '
            .'Answer in at most four sentences. Base every statement strictly on the data provided. '
            .'This output is advisory and must be confirmed by a staff member.';

        $generated = $this->ollama->generateText($prompt, null, $system);
        $elapsedMs = (int) round((hrtime(true) - $startedAt) / 1_000_000);

        $succeeded = ($generated['success'] ?? false) === true
            && trim((string) ($generated['text'] ?? '')) !== '';

        if ($succeeded) {
            $text = trim((string) $generated['text']);
            // A single-pass model answer carries no calibrated probability, so
            // report a conservative score and say so in the payload.
            $confidence = 0.6;
            $source = 'model';
            $outcome = 'success';
        } else {
            $text = $fallback($data);
            $confidence = 0.0;
            $source = 'deterministic-fallback';
            $outcome = 'fallback';

            Log::warning('AI model unavailable; served deterministic fallback.', [
                'report' => $reportType,
                'reason' => $generated['error'] ?? 'unknown',
            ]);
        }

        $this->record($request, $reportType, $prompt, $text, $source, $confidence, $elapsedMs, $outcome);

        return response()->json([
            'data' => [
                'report' => $reportType,
                'generated_text' => $text,
                'source' => $source,
                'confidence' => $confidence,
                'confidence_note' => $source === 'model'
                    ? 'Single-pass generation; not a calibrated probability. Treat as advisory only.'
                    : 'Deterministic fallback used because the model was unavailable.',
                'model' => $source === 'model' ? config('services.ollama.model') : null,
                'response_time_ms' => $elapsedMs,
                'disclosure' => 'Machine-generated. Confirm with a staff member before acting.',
                'inputs' => $data,
            ],
        ]);
    }

    /**
     * Persist the traceability record required by T4.
     */
    private function record(
        Request $request,
        string $reportType,
        string $prompt,
        string $response,
        string $source,
        float $confidence,
        int $elapsedMs,
        string $outcome,
    ): void {
        try {
            ChatbotInteraction::create([
                'user_id' => $request->user()?->id,
                'question_hash' => hash('sha256', $prompt),
                'page_type' => $reportType,
                'source_refs' => ['source' => $source, 'confidence' => $confidence],
                'response' => $response,
                'model_name' => $source === 'model' ? (string) config('services.ollama.model') : null,
                'confidence' => $confidence,
                'response_time_ms' => $elapsedMs,
                'outcome' => $outcome,
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            // Traceability must never take the endpoint down.
            Log::error('Failed to record AI interaction.', ['exception' => $e::class]);
        }
    }

    /**
     * @param  array<string, int>  $data
     */
    private function fallbackInsights(array $data): string
    {
        return sprintf(
            'There are %d bookings (%d pending, %d approved) and %d inventory items, of which %d are at or below the low-stock threshold. '
            .'%d borrow requests are open with %d pending approval and %d overdue.',
            $data['total_bookings'],
            $data['pending_bookings'],
            $data['approved_bookings'],
            $data['total_inventory'],
            $data['low_stock_items'],
            $data['total_borrow_requests'],
            $data['pending_borrow_requests'],
            $data['overdue_borrow_requests'],
        );
    }

    /**
     * @param  array<int, array{month: string, count: int}>  $monthly
     */
    private function fallbackForecast(array $monthly): string
    {
        if ($monthly === []) {
            return 'No booking history is available yet, so no forecast can be produced.';
        }

        $counts = array_column($monthly, 'count');
        $average = array_sum($counts) / max(1, count($counts));

        return sprintf(
            'Across the last %d months bookings averaged %.1f per month (range %d to %d). '
            .'A reasonable planning figure for the next three months is about %d bookings per month.',
            count($counts),
            $average,
            min($counts),
            max($counts),
            (int) round($average),
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function fallbackInventory(array $items): string
    {
        $low = array_values(array_filter(
            $items,
            fn (array $item) => $item['quantity'] <= $item['threshold'],
        ));

        if ($low === []) {
            return sprintf(
                'All %d inventory items are above their low-stock threshold. No reordering is required right now.',
                count($items),
            );
        }

        $names = array_map(
            fn (array $item) => sprintf('%s (%d left)', $item['name'], $item['quantity']),
            array_slice($low, 0, 5),
        );

        return sprintf(
            '%d of %d items are at or below their threshold and should be reordered: %s.',
            count($low),
            count($items),
            implode(', ', $names),
        );
    }
}
