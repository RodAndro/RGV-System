<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $borrowRequests = $user->borrowRequests();

        $summary = [
            'total' => (clone $borrowRequests)->count(),
            'pending' => (clone $borrowRequests)->where('status', 'pending')->count(),
            'approved' => (clone $borrowRequests)->where('status', 'approved')->count(),
            'borrowed' => (clone $borrowRequests)->where('status', 'borrowed')->count(),
            'returned' => (clone $borrowRequests)->where('status', 'returned')->count(),
            'overdue' => (clone $borrowRequests)->where('status', 'borrowed')->whereDate('due_date', '<', now()->toDateString())->count(),
        ];

        $recent = $user->borrowRequests()
            ->with('borrowItems.inventory')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($borrowRequest) => [
                'id' => $borrowRequest->id,
                'request_number' => $borrowRequest->request_number,
                'status' => $borrowRequest->status,
                'borrow_date' => optional($borrowRequest->borrow_date)->format('Y-m-d'),
                'due_date' => optional($borrowRequest->due_date)->format('Y-m-d'),
                'items' => $borrowRequest->borrowItems->map(fn ($item) => $item->inventory?->name)->filter()->values(),
            ]);

        return response()->json([
            'employee' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'summary' => $summary,
            'recent_requests' => $recent,
        ]);
    }
}
