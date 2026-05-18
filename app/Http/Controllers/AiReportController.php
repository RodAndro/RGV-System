<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Inventory;
use App\Models\BorrowRequest;
use App\Services\OllamaService;

class AiReportController extends Controller
{
    public function __construct(private OllamaService $ollama)
    {
    }

    public function generateInsights()
    {
        $bookings = Booking::all();
        $inventory = Inventory::all();
        $borrowRequests = BorrowRequest::all();

        $data = [
            'total_bookings' => $bookings->count(),
            'pending_bookings' => $bookings->where('status', 'pending')->count(),
            'approved_bookings' => $bookings->where('status', 'approved')->count(),
            'completed_bookings' => $bookings->where('status', 'completed')->count(),
            'total_inventory' => $inventory->count(),
            'low_stock_items' => $inventory->filter(fn($item) => $item->isLowStock())->count(),
            'total_borrow_requests' => $borrowRequests->count(),
            'pending_borrow_requests' => $borrowRequests->where('status', 'pending')->count(),
            'approved_borrow_requests' => $borrowRequests->where('status', 'approved')->count(),
            'overdue_borrow_requests' => $borrowRequests->where('status', 'borrowed')
                ->filter(fn($req) => $req->due_date < now())->count(),
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
        $bookings = Booking::where('preferred_date', '>=', now()->subMonths(6))
            ->orderBy('preferred_date')
            ->get()
            ->groupBy(function($booking) {
                return $booking->preferred_date->format('Y-m');
            });

        $monthlyData = [];
        foreach ($bookings as $month => $monthBookings) {
            $monthlyData[] = [
                'month' => $month,
                'count' => $monthBookings->count(),
            ];
        }

        $prompt = "Based on the following monthly booking data, provide a 3-month forecast: " . json_encode($monthlyData);

        $forecast = $this->callOllama($prompt);

        return response()->json([
            'historical_data' => $monthlyData,
            'forecast' => $forecast,
        ]);
    }

    public function generateInventoryRecommendations()
    {
        $inventory = Inventory::with(['category', 'borrowItems'])->get();
        
        $inventoryData = $inventory->map(function($item) {
            return [
                'name' => $item->name,
                'category' => $item->category->name,
                'quantity' => $item->quantity,
                'threshold' => $item->low_stock_threshold,
                'status' => $item->status,
                'borrow_count' => $item->borrowItems->count(),
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
