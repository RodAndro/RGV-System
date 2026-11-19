<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\PaginatesResources;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreBorrowRequest as StoreBorrowRequestRequest;
use App\Http\Requests\Api\V1\UpdateBorrowRequestStatus;
use App\Http\Resources\BorrowRequestResource;
use App\Models\BorrowRequest;
use App\Models\User;
use App\Services\BorrowService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BorrowRequestController extends Controller
{
    use PaginatesResources;

    private const SORTABLE = ['id', 'request_number', 'status', 'borrow_date', 'due_date', 'created_at'];

    public function __construct(private readonly BorrowService $borrowService) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = BorrowRequest::query()->with(['employee:id,name,email', 'borrowItems.inventory:id,item_code,name,unit']);

        // Employees only ever see their own requests (B4).
        if (! $user->isAdmin()) {
            $query->where('employee_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('employee_id') && $user->isAdmin()) {
            $query->where('employee_id', $request->integer('employee_id'));
        }

        $this->applyListQuery($query, $request, ['request_number', 'reason'], self::SORTABLE);

        $paginator = $query->paginate($this->perPage($request))->appends($request->query());

        return response()->json([
            'data' => BorrowRequestResource::collection($paginator->getCollection())->resolve(),
            'meta' => [
                'page' => $paginator->currentPage(),
                'limit' => $paginator->perPage(),
                'total' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
            ],
        ]);
    }

    public function store(StoreBorrowRequestRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $employee = $user;

        if ($user->isAdmin() && $request->filled('employee_id')) {
            $employee = User::findOrFail($request->integer('employee_id'));
        }

        try {
            $borrowRequest = $this->borrowService->createPending(
                $employee,
                (string) $request->input('reason'),
                (string) $request->input('borrow_date'),
                (string) $request->input('due_date'),
                $request->input('items', []),
            );
        } catch (DomainException $exception) {
            return $this->businessRuleError($exception->getMessage());
        }

        $borrowRequest->load(['employee:id,name,email', 'borrowItems.inventory:id,item_code,name,unit']);

        return (new BorrowRequestResource($borrowRequest))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('api.v1.borrow-requests.show', $borrowRequest));
    }

    public function show(Request $request, BorrowRequest $borrowRequest): BorrowRequestResource|JsonResponse
    {
        $user = $request->user();

        if (! $user->isAdmin() && $borrowRequest->employee_id !== $user->id) {
            return $this->error('FORBIDDEN', 'You may only view your own borrow requests.', 403);
        }

        $borrowRequest->load(['employee:id,name,email', 'borrowItems.inventory:id,item_code,name,unit']);

        return new BorrowRequestResource($borrowRequest);
    }

    /**
     * State transitions (approve/reject/mark-borrowed/return) are modelled
     * as PATCH status changes, per the REST conventions in section 6.1.
     */
    public function update(UpdateBorrowRequestStatus $request, BorrowRequest $borrowRequest): BorrowRequestResource|JsonResponse
    {
        $status = (string) $request->input('status');
        $admin = $request->user();

        try {
            match ($status) {
                'approved' => $this->borrowService->approve($borrowRequest, $admin),
                'rejected' => $this->borrowService->reject($borrowRequest, (string) $request->input('remarks', '')),
                'borrowed' => $this->borrowService->markBorrowed($borrowRequest, $admin),
                'returned' => $this->borrowService->returnItems(
                    $borrowRequest,
                    $request->input('items', []),
                    null,
                    $request->input('return_location'),
                    $admin,
                ),
            };
        } catch (DomainException $exception) {
            return $this->businessRuleError($exception->getMessage());
        }

        $borrowRequest->refresh()->load(['employee:id,name,email', 'borrowItems.inventory:id,item_code,name,unit']);

        return new BorrowRequestResource($borrowRequest);
    }

    public function destroy(BorrowRequest $borrowRequest): Response
    {
        $borrowRequest->delete();

        return response()->noContent();
    }

    private function businessRuleError(string $message): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => 'UNPROCESSABLE_ENTITY',
                'message' => $message,
                'details' => [],
            ],
        ], 422);
    }

    private function error(string $code, string $message, int $status): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => [],
            ],
        ], $status);
    }
}
