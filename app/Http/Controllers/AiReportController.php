<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Inventory;
use App\Models\BorrowRequest;
use App\Services\OllamaService;
use Illuminate\Support\Facades\DB;

class AiReportController extends Controller
{
    public function __construct(private OllamaService $ollama)
    {
    }

    public function generateInsights()
    {
        $bookingStats = Booking::query()->selectRaw("COUNT(*) as total_bookings, COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_bookings, COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_bookings, COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_bookings")
            ->first();
        $inventoryStats = Inventory::query()->selectRaw('COUNT(*) as total_inventory, COUNT(CASE WHEN quantity <= low_stock_threshold THEN 1 END) as low_stock_items')
            ->first();
        $borrowStats = BorrowRequest::query()->selectRaw("COUNT(*) as total_borrow_requests, COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_borrow_requests, COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_borrow_requests, COUNT(CASE WHEN status = 'borrowed' AND due_date < ? THEN 1 END) as overdue_borrow_requests", [now()])
            ->first();

        $data = [
            'total_bookings' => (int) $bookingStats->total_bookings,
            'pending_bookings' => (int) $bookingStats->pending_bookings,
            'approved_bookings' => (int) $bookingStats->approved_bookings,
            'completed_bookings' => (int) $bookingStats->completed_bookings,
            'total_inventory' => (int) $inventoryStats->total_inventory,
            'low_stock_items' => (int) $inventoryStats->low_stock_items,
            'total_borrow_requests' => (int) $borrowStats->total_borrow_requests,
            'pending_borrow_requests' => (int) $borrowStats->pending_borrow_requests,
            'approved_borrow_requests' => (int) $borrowStats->approved_borrow_requests,
            'overdue_borrow_requests' => (int) $borrowStats->overdue_borrow_requests,
        ];

        $prompt = "Analyze the following data for RGV Multi-Tech Services and provide insights: " . json_encode($data);

        $insights = $this->callOllama($prompt);

        return response()->json([
            'data' => $data,
            'insights' => $insights,
        ]);
    }

    public function generateBookingForecast()
    {
        $monthExpression = DB::connection()->getDriverName() === 'pgsql'
            ? "TO_CHAR(preferred_date, 'YYYY-MM')"
            : "strftime('%Y-%m', preferred_date)";

        $monthlyData = Booking::selectRaw("{$monthExpression} as month, COUNT(*) as count")
            ->where('preferred_date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => ['month' => $row->month, 'count' => (int) $row->count])
            ->all();

        $prompt = "Based on the following monthly booking data, provide a 3-month forecast: " . json_encode($monthlyData);

        $forecast = $this->callOllama($prompt);

        return response()->json([
            'historical_data' => $monthlyData,
            'forecast' => $forecast,
        ]);
    }

    public function generateInventoryRecommendations()
    {
        $inventory = Inventory::with('category')->withCount('borrowItems')->get();
        
        $inventoryData = $inventory->map(function($item) {
            return [
                'name' => $item->name,
                'category' => $item->category->name,
                'quantity' => $item->quantity,
                'threshold' => $item->low_stock_threshold,
                'status' => $item->status,
                'borrow_count' => $item->borrow_items_count,
            ];
        });

        $prompt = "Based on the following inventory data, provide recommendations for stock management and reordering: " . json_encode($inventoryData);

        $recommendations = $this->callOllama($prompt);

        return response()->json([
            'inventory_data' => $inventoryData,
            'recommendations' => $recommendations,
        ]);
    }

    private function callOllama($prompt)
    {
        $response = $this->ollama->generateText(
            $prompt,
            null,
            'You are a helpful business analyst for RGV Multi-Tech Services. Provide concise, actionable insights.'
        );

        if ($response['success']) {
            return $response['text'];
        }

        return [
            'error' => 'Ollama request failed',
            'message' => $response['error'] ?? $response['body'] ?? 'Unknown error',
        ];
    }
}
