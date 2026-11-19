<?php

namespace App\Services\Chatbot;

use App\Models\Booking;
use App\Models\BorrowItem;
use App\Models\BorrowRequest;
use App\Models\Inventory;
use App\Models\ScheduledTask;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Read-only record retrieval for the reports assistant.
 *
 * Every method here reads the live database and returns the rows it found.
 * Nothing is cached, estimated or written, so answers can be traced back to
 * actual records.
 */
class DatabaseAssistant
{
    /**
     * Find people matching the question. Users are matched first, then booking
     * contacts that were captured without an employee account.
     *
     * @param  array<int, string>  $phrases
     * @return array<int, array<string, mixed>>
     */
    public function findPeople(array $phrases, string $message): array
    {
        $terms = $this->searchTerms($phrases, $message);

        if ($terms === []) {
            return [];
        }

        $users = User::query()
            ->with('roles')
            ->where(function (Builder $query) use ($terms) {
                foreach ($terms as $term) {
                    $query->orWhereRaw('LOWER(name) LIKE ?', ['%'.$term.'%']);
                }
            })
            ->orderBy('name')
            ->limit(25)
            ->get();

        $people = [];

        foreach ($users as $user) {
            $people[] = [
                'type' => 'user',
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames()->implode(', ') ?: null,
                'is_active' => (bool) $user->is_active,
                'matched_term' => $this->matchingTerm($user->name, $terms),
                'exact_match' => in_array(mb_strtolower(trim($user->name)), $terms, true),
            ];
        }

        $knownNames = array_map(fn (array $person) => mb_strtolower((string) $person['name']), $people);

        $contacts = Booking::query()
            ->where(function (Builder $query) use ($terms) {
                foreach ($terms as $term) {
                    $query->orWhereRaw('LOWER(full_name) LIKE ?', ['%'.$term.'%']);
                }
            })
            ->orderBy('full_name')
            ->limit(50)
            ->get(['full_name', 'email'])
            ->unique(fn (Booking $booking) => mb_strtolower(trim((string) $booking->full_name)))
            ->take(25);

        foreach ($contacts as $booking) {
            $name = trim((string) $booking->full_name);
            $key = mb_strtolower($name);

            if ($name === '' || in_array($key, $knownNames, true) || $this->contactMatchesUser($name, $people)) {
                continue;
            }

            $knownNames[] = $key;
            $people[] = [
                'type' => 'booking_contact',
                'id' => null,
                'name' => $name,
                'email' => $booking->email,
                'role' => 'booking contact (no user account)',
                'is_active' => null,
                'matched_term' => $this->matchingTerm($name, $terms),
                'exact_match' => in_array($key, $terms, true),
            ];
        }

        usort($people, function (array $left, array $right): int {
            if ($left['exact_match'] !== $right['exact_match']) {
                return $left['exact_match'] ? -1 : 1;
            }

            return strcmp($left['name'], $right['name']);
        });

        return array_slice($this->narrowToStrongMatches($people, $terms), 0, 25);
    }

    /**
     * When the question contains a full name, only people matching that full
     * name are relevant; a shared surname must not pull in other records.
     *
     * @param  array<int, array<string, mixed>>  $people
     * @param  array<int, string>  $terms
     * @return array<int, array<string, mixed>>
     */
    private function narrowToStrongMatches(array $people, array $terms): array
    {
        $fullNameTerms = array_values(array_filter($terms, fn (string $term) => str_contains($term, ' ')));

        if ($fullNameTerms === [] || $people === []) {
            return $people;
        }

        $strong = array_values(array_filter($people, function (array $person) use ($fullNameTerms): bool {
            $name = mb_strtolower((string) $person['name']);

            foreach ($fullNameTerms as $term) {
                if (str_contains($name, $term)) {
                    return true;
                }
            }

            return false;
        }));

        return $strong !== [] ? $strong : $people;
    }

    /**
     * A booking contact whose name contains every part of a matched user's
     * name is the same person under a longer spelling, not a second person.
     *
     * @param  array<int, array<string, mixed>>  $people
     */
    private function contactMatchesUser(string $contactName, array $people): bool
    {
        $contact = mb_strtolower($contactName);

        foreach ($people as $person) {
            if ($person['type'] !== 'user') {
                continue;
            }

            $tokens = $this->nameTokens((string) $person['name']);

            if (count($tokens) < 2) {
                continue;
            }

            $matches = true;

            foreach ($tokens as $token) {
                if (! str_contains($contact, $token)) {
                    $matches = false;

                    break;
                }
            }

            if ($matches) {
                return true;
            }
        }

        return false;
    }

    /**
     * Full cross-module profile for one confirmed person.
     *
     * @param  array<string, mixed>  $person
     * @param  array<string, mixed>|null  $range
     * @return array<string, mixed>
     */
    public function profile(array $person, ?array $range = null): array
    {
        $bookingQuery = $this->bookingQueryFor($person);

        if ($range !== null) {
            $bookingQuery->whereBetween('preferred_date', [$range['start'], $range['end']]);
        }

        $linkedBookingIds = (clone $bookingQuery)->pluck('id')->all();

        $bookings = [
            'total' => count($linkedBookingIds),
            'by_status' => (clone $bookingQuery)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all(),
            'rows' => (clone $bookingQuery)
                ->orderByDesc('preferred_date')
                ->limit(15)
                ->get(),
        ];

        $borrowQuery = $this->borrowQueryFor($person);

        if ($borrowQuery !== null && $range !== null) {
            $borrowQuery->whereBetween('borrow_date', [$range['start'], $range['end']]);
        }

        $borrowings = $borrowQuery !== null
            ? [
                'total' => (clone $borrowQuery)->count(),
                'by_status' => (clone $borrowQuery)
                    ->selectRaw('status, COUNT(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status')
                    ->all(),
                'rows' => (clone $borrowQuery)
                    ->with(['borrowItems.inventory'])
                    ->orderByDesc('created_at')
                    ->limit(15)
                    ->get(),
            ]
            : ['total' => 0, 'by_status' => [], 'rows' => collect()];

        return [
            'person' => $person,
            'range' => $range,
            'bookings' => $bookings,
            'similar_bookings' => $this->similarBookingsFor($person, $range, $linkedBookingIds),
            'borrowings' => $borrowings,
            'returns' => $this->returnedItemsFor($person, $range),
            'returns_total' => $this->returnedCountFor($person, $range),
            'tasks' => $this->taskCountsFor($person),
        ];
    }

    /**
     * Compact record counts for several similarly named people.
     *
     * @param  array<int, array<string, mixed>>  $people
     * @return array<int, array<string, mixed>>
     */
    public function summariesFor(array $people): array
    {
        $summaries = [];

        foreach (array_slice($people, 0, 8) as $person) {
            $bookingQuery = $this->bookingQueryFor($person);
            $borrowQuery = $this->borrowQueryFor($person);

            $summaries[] = [
                'person' => $person,
                'bookings' => (clone $bookingQuery)->count(),
                'borrowings' => $borrowQuery !== null ? (clone $borrowQuery)->count() : 0,
                'returns' => $this->returnedCountFor($person),
                'pending' => (clone $bookingQuery)->where('status', 'pending')->count()
                    + ($borrowQuery !== null ? (clone $borrowQuery)->whereIn('status', ['pending', 'approved'])->count() : 0),
            ];
        }

        return $summaries;
    }

    /**
     * @param  array<string, mixed>  $range
     * @param  array<int, string>  $statuses
     * @return array<string, mixed>
     */
    public function bookingsInRange(array $range, array $statuses = []): array
    {
        $query = Booking::query()->whereBetween('preferred_date', [$range['start'], $range['end']]);

        $bookingStatuses = array_values(array_intersect($statuses, ['pending', 'approved', 'completed', 'rejected', 'cancelled']));

        if ($bookingStatuses !== []) {
            $query->whereIn('status', $bookingStatuses);
        }

        $total = (clone $query)->count();

        return [
            'total' => $total,
            'people' => (clone $query)->distinct()->count('full_name'),
            'by_status' => (clone $query)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all(),
            'rows' => (clone $query)->orderBy('preferred_date')->limit(30)->get(),
            'other_years' => $total === 0 && ($range['year_assumed'] ?? false)
                ? $this->otherYearBookingCounts($range)
                : [],
            'statuses' => $bookingStatuses,
        ];
    }

    /**
     * @param  array<string, mixed>  $range
     * @param  array<int, string>  $statuses
     * @return array<string, mixed>
     */
    public function borrowingsInRange(array $range, array $statuses = []): array
    {
        $query = BorrowRequest::query()->whereBetween('borrow_date', [$range['start'], $range['end']]);

        $borrowStatuses = array_values(array_intersect($statuses, ['pending', 'approved', 'borrowed', 'returned', 'rejected', 'cancelled']));

        if ($borrowStatuses !== []) {
            $query->whereIn('status', $borrowStatuses);
        }

        return [
            'total' => (clone $query)->count(),
            'people' => (clone $query)->distinct()->count('employee_id'),
            'by_status' => (clone $query)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all(),
            'rows' => (clone $query)
                ->with(['employee', 'borrowItems.inventory'])
                ->orderBy('borrow_date')
                ->limit(30)
                ->get(),
            'statuses' => $borrowStatuses,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $range
     * @return array<string, mixed>
     */
    public function returns(?array $range = null): array
    {
        $lineItems = BorrowItem::query()
            ->with(['inventory', 'borrowRequest.employee'])
            ->where('is_returned', true)
            ->when($range !== null, fn (Builder $query) => $query->whereBetween('returned_at', [$range['start'], $range['end']]))
            ->orderByDesc('returned_at')
            ->limit(50)
            ->get();

        $requests = BorrowRequest::query()
            ->with(['employee', 'borrowItems.inventory'])
            ->where('status', 'returned')
            ->when($range !== null, fn (Builder $query) => $query->whereBetween('returned_at', [$range['start'], $range['end']]))
            ->orderByDesc('returned_at')
            ->limit(50)
            ->get();

        return [
            'line_items' => $lineItems,
            'requests' => $requests,
            'line_item_total' => BorrowItem::query()
                ->where('is_returned', true)
                ->when($range !== null, fn (Builder $query) => $query->whereBetween('returned_at', [$range['start'], $range['end']]))
                ->count(),
            'request_total' => BorrowRequest::query()
                ->where('status', 'returned')
                ->when($range !== null, fn (Builder $query) => $query->whereBetween('returned_at', [$range['start'], $range['end']]))
                ->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function usersList(?string $role = null, ?bool $active = null, int $limit = 30): array
    {
        $query = User::query()->with('roles');

        if ($role !== null) {
            $query->role($role);
        }

        if ($active !== null) {
            $query->where('is_active', $active);
        }

        return [
            'total' => (clone $query)->count(),
            'rows' => (clone $query)->orderBy('name')->limit($limit)->get(),
            'role' => $role,
            'active' => $active,
            'admins' => User::role('admin')->count(),
            'employees' => User::role('employee')->count(),
            'active_count' => User::query()->where('is_active', true)->count(),
            'inactive_count' => User::query()->where('is_active', false)->count(),
        ];
    }

    /**
     * @param  array<int, string>  $statuses
     * @param  array<string, mixed>|null  $range
     * @return array<string, mixed>
     */
    public function bookingsList(array $statuses = [], ?array $range = null, int $limit = 30): array
    {
        $query = Booking::query();
        $validStatuses = array_values(array_intersect($statuses, ['pending', 'approved', 'completed', 'rejected', 'cancelled']));

        if ($validStatuses !== []) {
            $query->whereIn('status', $validStatuses);
        }

        if ($range !== null) {
            $query->whereBetween('preferred_date', [$range['start'], $range['end']]);
        }

        return [
            'total' => (clone $query)->count(),
            'by_status' => (clone $query)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all(),
            'rows' => (clone $query)->orderByDesc('preferred_date')->limit($limit)->get(),
            'statuses' => $validStatuses,
            'range' => $range,
        ];
    }

    /**
     * @param  array<int, string>  $statuses
     * @param  array<string, mixed>|null  $range
     * @return array<string, mixed>
     */
    public function borrowingsList(array $statuses = [], ?array $range = null, int $limit = 30): array
    {
        $query = BorrowRequest::query()->with(['employee', 'borrowItems.inventory']);
        $overdue = in_array('overdue', $statuses, true);
        $validStatuses = array_values(array_intersect($statuses, ['pending', 'approved', 'borrowed', 'returned', 'rejected', 'cancelled']));

        if ($overdue) {
            $query->whereIn('status', ['approved', 'borrowed'])->whereDate('due_date', '<', today());
        } elseif ($validStatuses !== []) {
            $query->whereIn('status', $validStatuses);
        }

        if ($range !== null) {
            $query->whereBetween('borrow_date', [$range['start'], $range['end']]);
        }

        return [
            'total' => (clone $query)->count(),
            'by_status' => (clone $query)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all(),
            'rows' => (clone $query)->orderBy('due_date')->limit($limit)->get(),
            'statuses' => $overdue ? ['overdue'] : $validStatuses,
            'overdue' => $overdue,
            'range' => $range,
        ];
    }

    /**
     * @param  array<int, string>  $statuses
     * @return array<string, mixed>
     */
    public function inventoryList(array $statuses = [], bool $lowStock = false, bool $outOfStock = false, int $limit = 30): array
    {
        $query = Inventory::query()->with('category');
        $validStatuses = array_values(array_intersect($statuses, ['available', 'borrowed', 'maintenance', 'damaged']));

        if ($validStatuses !== []) {
            $query->whereIn('status', $validStatuses);
        }

        if ($outOfStock) {
            $query->where('quantity', 0);
        } elseif ($lowStock) {
            $query->whereColumn('quantity', '<=', 'low_stock_threshold');
        }

        return [
            'total' => (clone $query)->count(),
            'rows' => (clone $query)->orderBy('name')->limit($limit)->get(),
            'statuses' => $validStatuses,
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
            'total_quantity' => (int) (clone $query)->sum('quantity'),
            'total_value' => (float) (clone $query)->selectRaw('COALESCE(SUM(quantity * unit_cost), 0) as total_value')->value('total_value'),
        ];
    }

    /**
     * Resolve booking references, borrow request numbers, inventory codes and
     * explicit numeric IDs mentioned in the question.
     *
     * @return array{tokens: array<int, string>, records: array<int, array<string, mixed>>}
     */
    public function referenceLookup(string $message): array
    {
        $tokens = [];

        foreach ([
            '/\b(?:DEMO-)?BK-[A-Z0-9-]+\b/i',
            '/\b(?:DEMO-)?BR-[A-Z0-9-]+\b/i',
            '/\bRGV-[A-Z0-9-]+\b/i',
        ] as $pattern) {
            if (preg_match_all($pattern, $message, $matches) > 0) {
                $tokens = array_merge($tokens, $matches[0]);
            }
        }

        $tokens = array_values(array_unique(array_map(fn (string $token) => strtoupper(trim($token)), $tokens)));
        $records = [];

        foreach ($tokens as $token) {
            $booking = Booking::query()->with('employee')->whereRaw('UPPER(reference_number) = ?', [$token])->first();

            if ($booking !== null) {
                $records[] = ['type' => 'booking', 'record' => $booking];

                continue;
            }

            $request = BorrowRequest::query()
                ->with(['employee', 'borrowItems.inventory'])
                ->whereRaw('UPPER(request_number) = ?', [$token])
                ->first();

            if ($request !== null) {
                $records[] = ['type' => 'borrow_request', 'record' => $request];

                continue;
            }

            $item = Inventory::query()->with('category')->whereRaw('UPPER(item_code) = ?', [$token])->first();

            if ($item !== null) {
                $records[] = ['type' => 'inventory', 'record' => $item];
            }
        }

        if (preg_match('/\b(?:booking|book)\s+(?:id\s*|#)\s*(\d+)\b/i', $message, $matches) === 1) {
            $booking = Booking::query()->with('employee')->find((int) $matches[1]);
            $tokens[] = 'booking #'.$matches[1];

            if ($booking !== null) {
                $records[] = ['type' => 'booking', 'record' => $booking];
            }
        }

        if (preg_match('/\b(?:borrow\s+)?request\s+(?:id\s*|#)\s*(\d+)\b/i', $message, $matches) === 1) {
            $request = BorrowRequest::query()->with(['employee', 'borrowItems.inventory'])->find((int) $matches[1]);
            $tokens[] = 'request #'.$matches[1];

            if ($request !== null) {
                $records[] = ['type' => 'borrow_request', 'record' => $request];
            }
        }

        if (preg_match('/\bitem\s+(?:id\s*|#)\s*(\d+)\b/i', $message, $matches) === 1) {
            $item = Inventory::query()->with('category')->find((int) $matches[1]);
            $tokens[] = 'item #'.$matches[1];

            if ($item !== null) {
                $records[] = ['type' => 'inventory', 'record' => $item];
            }
        }

        return ['tokens' => array_values(array_unique($tokens)), 'records' => $records];
    }

    /**
     * @return array<string, mixed>
     */
    public function tasks(): array
    {
        $pendingBookingQuery = Booking::query()->where('status', 'pending');
        $pendingBorrowQuery = BorrowRequest::query()->whereIn('status', ['pending', 'approved']);
        $overdueQuery = BorrowRequest::query()
            ->whereIn('status', ['approved', 'borrowed'])
            ->whereDate('due_date', '<', today());

        return [
            'pending_bookings' => (clone $pendingBookingQuery)->count(),
            'pending_booking_rows' => (clone $pendingBookingQuery)
                ->orderBy('preferred_date')
                ->limit(15)
                ->get(),
            'pending_borrow_requests' => (clone $pendingBorrowQuery)->count(),
            'pending_borrow_rows' => (clone $pendingBorrowQuery)
                ->with('employee')
                ->orderBy('due_date')
                ->limit(15)
                ->get(),
            'overdue_borrow_requests' => (clone $overdueQuery)->count(),
            'overdue_rows' => (clone $overdueQuery)
                ->with('employee')
                ->orderBy('due_date')
                ->limit(15)
                ->get(),
            'completed_bookings' => Booking::query()->where('status', 'completed')->count(),
            'returned_requests' => BorrowRequest::query()->where('status', 'returned')->count(),
            'scheduled_tasks' => ScheduledTask::query()
                ->orderByDesc('started_at')
                ->limit(10)
                ->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function recentActivity(): array
    {
        return [
            'bookings' => Booking::query()->orderByDesc('created_at')->limit(10)->get(),
            'borrow_requests' => BorrowRequest::query()
                ->with('employee')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get(),
            'returns' => BorrowItem::query()
                ->with(['inventory', 'borrowRequest.employee'])
                ->where('is_returned', true)
                ->orderByDesc('returned_at')
                ->limit(10)
                ->get(),
        ];
    }

    /**
     * A compact sample of real records for the language model to summarise.
     * The deterministic record answers never rely on this digest.
     */
    public function evidenceDigest(): string
    {
        $lines = [];

        $lines[] = '- Users: '.User::query()->count()
            .' (active '.User::query()->where('is_active', true)->count()
            .', inactive '.User::query()->where('is_active', false)->count().')';
        $lines[] = '- Bookings: '.Booking::query()->count()
            .' (pending '.Booking::query()->where('status', 'pending')->count()
            .', approved '.Booking::query()->where('status', 'approved')->count()
            .', completed '.Booking::query()->where('status', 'completed')->count()
            .', rejected '.Booking::query()->where('status', 'rejected')->count()
            .', cancelled '.Booking::query()->where('status', 'cancelled')->count().')';
        $lines[] = '- Borrow requests: '.BorrowRequest::query()->count()
            .' (pending '.BorrowRequest::query()->where('status', 'pending')->count()
            .', approved '.BorrowRequest::query()->where('status', 'approved')->count()
            .', borrowed '.BorrowRequest::query()->where('status', 'borrowed')->count()
            .', returned '.BorrowRequest::query()->where('status', 'returned')->count()
            .', rejected '.BorrowRequest::query()->where('status', 'rejected')->count().')';
        $lines[] = '- Returned line items: '.BorrowItem::query()->where('is_returned', true)->count();
        $lines[] = '- Inventory items: '.Inventory::query()->count()
            .' (low stock '.Inventory::query()->whereColumn('quantity', '<=', 'low_stock_threshold')->count().')';

        $lines[] = '';
        $lines[] = 'Latest bookings:';
        $bookings = Booking::query()->orderByDesc('created_at')->limit(8)->get();

        if ($bookings->isEmpty()) {
            $lines[] = '- none';
        }

        foreach ($bookings as $booking) {
            $lines[] = '- '.$booking->reference_number
                .' | '.$booking->status
                .' | preferred '.($booking->preferred_date?->format('Y-m-d') ?? 'n/a');
        }

        $lines[] = '';
        $lines[] = 'Latest borrow requests:';
        $requests = BorrowRequest::query()->orderByDesc('created_at')->limit(8)->get();

        if ($requests->isEmpty()) {
            $lines[] = '- none';
        }

        foreach ($requests as $request) {
            $lines[] = '- '.$request->request_number
                .' | '.$request->status
                .' | due '.($request->due_date?->format('Y-m-d') ?? 'n/a');
        }

        $lines[] = '';
        $lines[] = 'Latest returned items:';
        $returns = BorrowItem::query()
            ->with('inventory')
            ->where('is_returned', true)
            ->orderByDesc('returned_at')
            ->limit(5)
            ->get();

        if ($returns->isEmpty()) {
            $lines[] = '- none';
        }

        foreach ($returns as $item) {
            $lines[] = '- '.($item->inventory?->name ?? 'unknown item')
                .' x'.$item->quantity
                .' | returned '.($item->returned_at?->format('Y-m-d') ?? 'n/a');
        }

        return implode("\n", $lines);
    }

    /**
     * @return array<int, string>
     */
    public function sourceRefs(): array
    {
        return ['db:users', 'db:bookings', 'db:borrow_requests', 'db:borrow_items'];
    }

    /**
     * @param  array<string, mixed>  $person
     */
    private function bookingQueryFor(array $person): Builder
    {
        $query = Booking::query();

        if ($person['type'] === 'user') {
            $name = mb_strtolower((string) $person['name']);
            $email = $person['email'];

            return $query->where(function (Builder $inner) use ($person, $name, $email) {
                $inner->where('employee_id', $person['id'])
                    ->orWhereRaw('LOWER(full_name) = ?', [$name]);

                if (! empty($email)) {
                    $inner->orWhere('email', $email);
                }
            });
        }

        return $query->whereRaw('LOWER(full_name) = ?', [mb_strtolower((string) $person['name'])]);
    }

    /**
     * @param  array<string, mixed>  $person
     */
    private function borrowQueryFor(array $person): ?Builder
    {
        return $person['type'] === 'user'
            ? BorrowRequest::query()->where('employee_id', $person['id'])
            : null;
    }

    /**
     * @param  array<string, mixed>  $person
     * @param  array<string, mixed>|null  $range
     * @return Collection<int, BorrowItem>
     */
    private function returnedItemsFor(array $person, ?array $range = null): Collection
    {
        if ($person['type'] !== 'user') {
            return collect();
        }

        return BorrowItem::query()
            ->with(['inventory', 'borrowRequest'])
            ->whereHas('borrowRequest', fn (Builder $query) => $query->where('employee_id', $person['id']))
            ->where('is_returned', true)
            ->when($range !== null, fn (Builder $query) => $query->whereBetween('returned_at', [$range['start'], $range['end']]))
            ->orderByDesc('returned_at')
            ->limit(20)
            ->get();
    }

    /**
     * @param  array<string, mixed>  $person
     * @param  array<string, mixed>|null  $range
     */
    private function returnedCountFor(array $person, ?array $range = null): int
    {
        if ($person['type'] !== 'user') {
            return 0;
        }

        return BorrowItem::query()
            ->whereHas('borrowRequest', fn (Builder $query) => $query->where('employee_id', $person['id']))
            ->where('is_returned', true)
            ->when($range !== null, fn (Builder $query) => $query->whereBetween('returned_at', [$range['start'], $range['end']]))
            ->count();
    }

    /**
     * @param  array<string, mixed>  $person
     * @return array<string, int>
     */
    private function taskCountsFor(array $person): array
    {
        $bookingQuery = $this->bookingQueryFor($person);
        $borrowQuery = $this->borrowQueryFor($person);

        return [
            'pending_bookings' => (clone $bookingQuery)->where('status', 'pending')->count(),
            'completed_bookings' => (clone $bookingQuery)->where('status', 'completed')->count(),
            'pending_borrow_requests' => $borrowQuery !== null ? (clone $borrowQuery)->where('status', 'pending')->count() : 0,
            'approved_borrow_requests' => $borrowQuery !== null ? (clone $borrowQuery)->where('status', 'approved')->count() : 0,
            'active_borrow_requests' => $borrowQuery !== null ? (clone $borrowQuery)->where('status', 'borrowed')->count() : 0,
            'overdue_borrow_requests' => $borrowQuery !== null
                ? (clone $borrowQuery)->whereIn('status', ['approved', 'borrowed'])->whereDate('due_date', '<', today())->count()
                : 0,
            'returned_borrow_requests' => $borrowQuery !== null ? (clone $borrowQuery)->where('status', 'returned')->count() : 0,
            'rejected_borrow_requests' => $borrowQuery !== null ? (clone $borrowQuery)->where('status', 'rejected')->count() : 0,
        ];
    }

    /**
     * Bookings whose customer name contains every part of the user's name,
     * even though the booking is not linked by employee ID or email. These are
     * reported separately so they are never mixed into linked records.
     *
     * @param  array<string, mixed>  $person
     * @param  array<string, mixed>|null  $range
     * @param  array<int, int|string>  $excludeIds
     * @return Collection<int, Booking>
     */
    private function similarBookingsFor(array $person, ?array $range, array $excludeIds): Collection
    {
        if ($person['type'] !== 'user') {
            return collect();
        }

        $tokens = $this->nameTokens((string) $person['name']);

        if (count($tokens) < 2) {
            return collect();
        }

        $query = Booking::query();

        foreach ($tokens as $token) {
            $query->whereRaw('LOWER(full_name) LIKE ?', ['%'.$token.'%']);
        }

        if ($excludeIds !== []) {
            $query->whereNotIn('id', $excludeIds);
        }

        if ($range !== null) {
            $query->whereBetween('preferred_date', [$range['start'], $range['end']]);
        }

        return $query->orderByDesc('preferred_date')->limit(10)->get();
    }

    /**
     * @return array<int, string>
     */
    private function nameTokens(string $name): array
    {
        $tokens = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($name)) ?: [];
        $tokens = array_filter(
            $tokens,
            fn (string $token) => mb_strlen($token) >= 2 && ! in_array($token, QuestionParser::STOPWORDS, true)
        );

        return array_values(array_unique($tokens));
    }

    /**
     * @param  array<string, mixed>  $range
     * @return array<int, int>
     */
    private function otherYearBookingCounts(array $range): array
    {
        $startMonth = (int) ($range['start_month'] ?? 1);
        $endMonth = (int) ($range['end_month'] ?? 12);
        $currentYear = (int) CarbonImmutable::now()->year;
        $counts = [];

        for ($year = $currentYear - 5; $year <= $currentYear + 1; $year++) {
            if ($year === $currentYear) {
                continue;
            }

            [$start, $end] = $this->monthWindow($year, $startMonth, $endMonth);
            $count = Booking::query()->whereBetween('preferred_date', [$start, $end])->count();

            if ($count > 0) {
                $counts[$year] = $count;
            }
        }

        return $counts;
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function monthWindow(int $year, int $startMonth, int $endMonth): array
    {
        $start = CarbonImmutable::create($year, $startMonth, 1)?->startOfMonth() ?? CarbonImmutable::now()->startOfMonth();
        $endYear = $endMonth < $startMonth ? $year + 1 : $year;
        $end = CarbonImmutable::create($endYear, $endMonth, 1)?->endOfMonth() ?? CarbonImmutable::now()->endOfMonth();

        return [$start, $end];
    }

    /**
     * @param  array<int, string>  $phrases
     * @return array<int, string>
     */
    private function searchTerms(array $phrases, string $message): array
    {
        $terms = [];

        foreach ($phrases as $phrase) {
            $normalised = $this->normalise($phrase);

            if ($normalised === '') {
                continue;
            }

            if (! $this->isStopwordPhrase($normalised)) {
                $terms[] = $normalised;
            }

            foreach (preg_split('/\s+/', $normalised) ?: [] as $word) {
                if (mb_strlen($word) >= 3 && ! $this->isStopword($word)) {
                    $terms[] = $word;
                }
            }
        }

        if ($phrases === []) {
            foreach (preg_split('/[^\p{L}\p{N}\'\-]+/u', Str::lower($message)) ?: [] as $word) {
                if (mb_strlen($word) >= 4 && ! $this->isStopword($word)) {
                    $terms[] = $word;
                }
            }
        }

        $terms = array_filter(
            array_unique($terms),
            fn (string $term) => mb_strlen($term) >= 3 && ! $this->isStopword($term)
        );

        return array_values($terms);
    }

    private function normalise(string $term): string
    {
        $term = Str::lower(trim($term));
        $term = (string) preg_replace("/['\x{2019}]s\b/u", '', $term);
        $term = (string) preg_replace('/[^\p{L}\p{N}\s\'\-]/u', '', $term);
        $term = (string) preg_replace('/\s+/', ' ', $term);

        return trim($term);
    }

    private function isStopwordPhrase(string $phrase): bool
    {
        foreach (preg_split('/\s+/', $phrase) ?: [] as $word) {
            if (! $this->isStopword($word)) {
                return false;
            }
        }

        return true;
    }

    private function isStopword(string $word): bool
    {
        static $stopwords = null;

        if ($stopwords === null) {
            $stopwords = array_flip(QuestionParser::STOPWORDS);
        }

        return isset($stopwords[trim($word)]);
    }

    /**
     * @param  array<int, string>  $terms
     */
    private function matchingTerm(string $name, array $terms): string
    {
        $lowerName = mb_strtolower($name);

        foreach ($terms as $term) {
            if (str_contains($lowerName, $term)) {
                return $term;
            }
        }

        return $terms[0] ?? '';
    }
}
