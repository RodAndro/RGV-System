<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\PaginatesResources;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use PaginatesResources;

    private const SORTABLE = ['id', 'reference_number', 'status', 'preferred_date', 'created_at'];

    public function index(Request $request): JsonResponse
    {
        $query = Booking::query()->with('employee:id,name');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('preferred_date', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('preferred_date', '<=', $request->date('date_to'));
        }

        $this->applyListQuery(
            $query,
            $request,
            ['reference_number', 'full_name', 'email', 'purpose_category'],
            self::SORTABLE,
        );

        $paginator = $query->paginate($this->perPage($request))->appends($request->query());

        return response()->json([
            'data' => $paginator->getCollection()->map(fn (Booking $booking) => [
                'id' => $booking->id,
                'reference_number' => $booking->reference_number,
                'full_name' => $booking->full_name,
                'email' => $booking->email,
                'contact_number' => $booking->contact_number,
                'preferred_date' => $booking->preferred_date?->toDateString(),
                'preferred_time' => $booking->preferred_time_24_hour,
                'purpose_category' => $booking->purpose_category,
                'status' => $booking->status,
                'employee' => $booking->employee ? [
                    'id' => $booking->employee->id,
                    'name' => $booking->employee->name,
                ] : null,
                'created_at' => $booking->created_at?->toIso8601String(),
            ])->all(),
            'meta' => [
                'page' => $paginator->currentPage(),
                'limit' => $paginator->perPage(),
                'total' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
            ],
        ]);
    }

    public function show(Booking $booking): JsonResponse
    {
        $booking->load('employee:id,name');

        return response()->json([
            'data' => [
                'id' => $booking->id,
                'reference_number' => $booking->reference_number,
                'full_name' => $booking->full_name,
                'email' => $booking->email,
                'contact_number' => $booking->contact_number,
                'address' => $booking->address,
                'preferred_date' => $booking->preferred_date?->toDateString(),
                'preferred_time' => $booking->preferred_time_24_hour,
                'purpose_category' => $booking->purpose_category,
                'reason' => $booking->reason,
                'status' => $booking->status,
                'remarks' => $booking->remarks,
                'employee' => $booking->employee ? [
                    'id' => $booking->employee->id,
                    'name' => $booking->employee->name,
                ] : null,
                'created_at' => $booking->created_at?->toIso8601String(),
            ],
        ]);
    }
}
