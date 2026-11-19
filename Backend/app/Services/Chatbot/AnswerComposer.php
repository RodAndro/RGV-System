<?php

namespace App\Services\Chatbot;

use App\Models\Booking;
use App\Models\BorrowItem;
use App\Models\BorrowRequest;
use App\Models\Inventory;
use App\Models\ScheduledTask;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

/**
 * Formats retrieved database rows into deterministic answers.
 *
 * Every number and name printed here comes from a row that was read from the
 * database. No language model is involved, so a record cannot be invented.
 */
class AnswerComposer
{
    /**
     * @param  array<string, mixed>  $profile
     */
    public function profile(array $profile): string
    {
        $person = $profile['person'];
        $range = $profile['range'];
        $bookings = $profile['bookings'];
        $borrowings = $profile['borrowings'];
        $returns = $profile['returns'];
        $tasks = $profile['tasks'];

        $lines = [$this->personHeading($person)];

        if ($range !== null) {
            $lines[] = '';
            $lines[] = '_Records limited to '.$range['label'].'._';
        }

        $lines[] = '';
        $lines[] = '**Bookings ('.(int) $bookings['total'].' total)**';

        if ($bookings['total'] === 0) {
            $lines[] = '• No booking records were found for this person.';
        } else {
            $lines[] = '• Status counts: '.$this->statusSummary($bookings['by_status']);

            foreach ($bookings['rows'] as $booking) {
                $lines[] = $this->bookingLine($booking);
            }

            if ($bookings['total'] > $bookings['rows']->count()) {
                $lines[] = '• Showing the latest '.$bookings['rows']->count().' of '.(int) $bookings['total'].' bookings.';
            }
        }

        $similarBookings = $profile['similar_bookings'] ?? collect();

        if ($similarBookings->isNotEmpty()) {
            $lines[] = '';
            $lines[] = '**Bookings under a similar name (not linked by user ID or email)**';

            foreach ($similarBookings as $booking) {
                $lines[] = $this->bookingLine($booking, includeName: true);
            }

            $lines[] = '• These records match every part of this user\'s name but are not linked to the account, so they are listed separately.';
        }

        $lines[] = '';
        $lines[] = '**Borrowing history ('.(int) $borrowings['total'].' total)**';

        if ($borrowings['total'] === 0) {
            $lines[] = '• No borrow request records were found for this person.';
        } else {
            $lines[] = '• Status counts: '.$this->statusSummary($borrowings['by_status']);

            foreach ($borrowings['rows'] as $request) {
                $lines[] = $this->borrowLine($request);
            }

            if ($borrowings['total'] > $borrowings['rows']->count()) {
                $lines[] = '• Showing the latest '.$borrowings['rows']->count().' of '.(int) $borrowings['total'].' borrow requests.';
            }
        }

        $lines[] = '';
        $lines[] = '**Returned items ('.(int) $profile['returns_total'].' recorded)**';

        if ($returns->isEmpty()) {
            $lines[] = '• No returned items were found for this person.';
        } else {
            foreach ($returns as $item) {
                $lines[] = $this->returnedItemLine($item);
            }

            if ($profile['returns_total'] > $returns->count()) {
                $lines[] = '• Showing the latest '.$returns->count().' of '.(int) $profile['returns_total'].' returned items.';
            }
        }

        $lines[] = '';
        $lines[] = '**Pending and completed tasks**';
        $lines[] = '• Pending bookings: '.(int) $tasks['pending_bookings'];
        $lines[] = '• Pending borrow requests: '.(int) $tasks['pending_borrow_requests'];
        $lines[] = '• Approved but not collected: '.(int) $tasks['approved_borrow_requests'];
        $lines[] = '• Currently borrowed: '.(int) $tasks['active_borrow_requests'];
        $lines[] = '• Overdue borrow requests: '.(int) $tasks['overdue_borrow_requests'];
        $lines[] = '• Completed bookings: '.(int) $tasks['completed_bookings'];
        $lines[] = '• Returned borrow requests: '.(int) $tasks['returned_borrow_requests'];

        if ($tasks['pending_bookings'] === 0 && $tasks['pending_borrow_requests'] === 0 && $tasks['overdue_borrow_requests'] === 0) {
            $lines[] = '• No outstanding tasks were found for this person.';
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<int, array<string, mixed>>  $summaries
     */
    public function multipleMatches(array $summaries, string $searchedFor): string
    {
        $lines = ['I found '.count($summaries).' matching records for "'.$searchedFor.'". They are listed separately so their records are not mixed:'];

        foreach ($summaries as $summary) {
            $person = $summary['person'];
            $identity = $person['type'] === 'user'
                ? 'user #'.$person['id'].($person['email'] ? ' · '.$person['email'] : '').($person['role'] ? ' · '.$person['role'] : '')
                : 'booking contact'.($person['email'] ? ' · '.$person['email'] : '').' · no user account';

            $lines[] = '';
            $lines[] = '**'.$person['name'].'** — '.$identity;
            $lines[] = '• Bookings: '.(int) $summary['bookings']
                .' · Borrow requests: '.(int) $summary['borrowings']
                .' · Returned items: '.(int) $summary['returns']
                .' · Pending tasks: '.(int) $summary['pending'];
        }

        $lines[] = '';
        $lines[] = 'Ask for one person by full name or user ID to see their complete record.';

        return implode("\n", $lines);
    }

    public function noPersonMatch(string $searchedFor): string
    {
        return '**No matching records were found for "'.$searchedFor.'".**'."\n\n"
            .'I searched the users table (names, emails and roles) and the bookings table (customer names). '
            .'No user, employee, or booking contact matches that name.'."\n\n"
            .'Check the spelling, or ask **list all users** to see the names currently stored. You can also provide a full name, email address, or booking reference number.';
    }

    public function missingPersonName(): string
    {
        return 'Please tell me the person\'s full or partial name, and I will look up their bookings, borrowing history, returns, and outstanding tasks in the database.';
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $range
     */
    public function bookingRange(array $result, array $range): string
    {
        $filter = $result['statuses'] !== [] ? ' (status: '.implode(', ', $result['statuses']).')' : '';
        $lines = ['**Bookings from '.$range['label'].$filter.'**'];

        if ($result['total'] === 0) {
            $lines[] = '';
            $lines[] = 'No booking records were found in that date range.';

            if (($range['year_assumed'] ?? false) === true) {
                $lines[] = '';
                $lines[] = 'The year was not specified, so '.(int) $range['start']->year.' was used.';
            }

            if ($result['other_years'] !== []) {
                $years = [];

                foreach ($result['other_years'] as $year => $count) {
                    $years[] = $year.' ('.$count.' '.Str::plural('booking', $count).')';
                }

                $lines[] = 'Matching records exist for the same months in: '.implode(', ', $years).'.';
                $lines[] = 'Ask, for example, "January to February '.array_key_first($result['other_years']).'".';
            }

            return implode("\n", $lines);
        }

        $lines[] = '• Total bookings: '.(int) $result['total'];
        $lines[] = '• Distinct people who booked: '.(int) $result['people'];
        $lines[] = '• Status counts: '.$this->statusSummary($result['by_status']);

        $lines[] = '';
        $lines[] = '**Booking records ('.$result['rows']->count().' shown)**';

        foreach ($result['rows'] as $booking) {
            $lines[] = $this->bookingLine($booking, includeName: true);
        }

        if ($result['total'] > $result['rows']->count()) {
            $lines[] = '• Showing the first '.$result['rows']->count().' of '.(int) $result['total'].' bookings.';
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $range
     */
    public function borrowRange(array $result, array $range): string
    {
        $filter = $result['statuses'] !== [] ? ' (status: '.implode(', ', $result['statuses']).')' : '';
        $lines = ['**Borrow requests from '.$range['label'].$filter.'**'];

        if ($result['total'] === 0) {
            $lines[] = '';
            $lines[] = 'No borrow request records were found in that date range.';

            return implode("\n", $lines);
        }

        $lines[] = '• Total borrow requests: '.(int) $result['total'];
        $lines[] = '• Distinct employees: '.(int) $result['people'];
        $lines[] = '• Status counts: '.$this->statusSummary($result['by_status']);
        $lines[] = '';
        $lines[] = '**Borrow request records ('.$result['rows']->count().' shown)**';

        foreach ($result['rows'] as $request) {
            $lines[] = $this->borrowLine($request, includeName: true);
        }

        if ($result['total'] > $result['rows']->count()) {
            $lines[] = '• Showing the first '.$result['rows']->count().' of '.(int) $result['total'].' borrow requests.';
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>|null  $range
     */
    public function returnRange(array $result, ?array $range = null): string
    {
        $lines = ['**Returns from '.($range['label'] ?? 'all recorded dates').'**'];

        if ($result['request_total'] === 0 && $result['line_item_total'] === 0) {
            $lines[] = '';
            $lines[] = 'No returned borrow requests or returned line items were found'
                .($range !== null ? ' in that date range.' : ' in the database.');

            return implode("\n", $lines);
        }

        $lines[] = '• Returned borrow requests: '.(int) $result['request_total'];
        $lines[] = '• Returned line items: '.(int) $result['line_item_total'];

        if ($result['requests']->isNotEmpty()) {
            $lines[] = '';
            $lines[] = '**Returned requests**';

            foreach ($result['requests'] as $request) {
                $lines[] = '• '.$this->date($request->returned_at).' · '.$request->request_number
                    .' · '.($request->employee?->name ?? 'unknown employee')
                    .' · status: '.$request->status;

                if ($request->borrowItems->isNotEmpty()) {
                    $items = $request->borrowItems->map(fn (BorrowItem $item) => ($item->inventory?->name ?? 'unknown item')
                        .' x'.$item->quantity
                        .($item->is_returned ? ' (returned)' : ' (not marked returned)'))->implode(', ');

                    $lines[] = '   Items: '.$items;
                }
            }

            if ($result['request_total'] > $result['requests']->count()) {
                $lines[] = '• Showing the latest '.$result['requests']->count().' of '.(int) $result['request_total'].' returned requests.';
            }
        }

        if ($result['line_items']->isNotEmpty()) {
            $lines[] = '';
            $lines[] = '**Returned line items**';

            foreach ($result['line_items'] as $item) {
                $lines[] = $this->returnedItemLine($item);
            }

            if ($result['line_item_total'] > $result['line_items']->count()) {
                $lines[] = '• Showing the latest '.$result['line_items']->count().' of '.(int) $result['line_item_total'].' returned line items.';
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    public function userList(array $result): string
    {
        $heading = 'Users';

        if ($result['role'] !== null) {
            $heading = ucfirst((string) $result['role']).'s';
        } elseif ($result['active'] === true) {
            $heading = 'Active users';
        } elseif ($result['active'] === false) {
            $heading = 'Inactive users';
        }

        $lines = ['**'.$heading.' ('.(int) $result['total'].' found)**'];
        $lines[] = '• Admins: '.(int) $result['admins']
            .' · Employees: '.(int) $result['employees']
            .' · Active: '.(int) $result['active_count']
            .' · Inactive: '.(int) $result['inactive_count'];

        if ($result['total'] === 0) {
            $lines[] = '• No user records match that request.';

            return implode("\n", $lines);
        }

        $lines[] = '';

        foreach ($result['rows'] as $user) {
            $lines[] = '• #'.$user->id.' · '.$user->name
                .' · '.($user->email ?: 'no email')
                .' · '.($user->getRoleNames()->implode(', ') ?: 'no role')
                .' · '.($user->is_active ? 'Active' : 'Inactive');
        }

        if ($result['total'] > $result['rows']->count()) {
            $lines[] = '• Showing the first '.$result['rows']->count().' of '.(int) $result['total'].' users.';
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    public function bookingList(array $result): string
    {
        $filter = $result['statuses'] !== [] ? ' — status: '.implode(', ', $result['statuses']) : '';
        $range = $result['range'] !== null ? ' from '.$result['range']['label'] : '';
        $lines = ['**Bookings'.$range.$filter.' ('.(int) $result['total'].' found)**'];

        if ($result['total'] === 0) {
            $lines[] = '• No booking records match that request.';

            return implode("\n", $lines);
        }

        $lines[] = '• Status counts: '.$this->statusSummary($result['by_status']);
        $lines[] = '';

        foreach ($result['rows'] as $booking) {
            $lines[] = $this->bookingLine($booking, includeName: true);
        }

        if ($result['total'] > $result['rows']->count()) {
            $lines[] = '• Showing the first '.$result['rows']->count().' of '.(int) $result['total'].' bookings.';
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    public function borrowList(array $result): string
    {
        $filter = $result['statuses'] !== [] ? ' — status: '.implode(', ', $result['statuses']) : '';
        $range = $result['range'] !== null ? ' from '.$result['range']['label'] : '';
        $lines = ['**Borrow requests'.$range.$filter.' ('.(int) $result['total'].' found)**'];

        if ($result['total'] === 0) {
            $lines[] = '• No borrow request records match that request.';

            return implode("\n", $lines);
        }

        $lines[] = '• Status counts: '.$this->statusSummary($result['by_status']);

        if ($result['overdue']) {
            $lines[] = '• These are approved or borrowed requests past their due date.';
        }

        $lines[] = '';

        foreach ($result['rows'] as $request) {
            $lines[] = $this->borrowLine($request, includeName: true);
        }

        if ($result['total'] > $result['rows']->count()) {
            $lines[] = '• Showing the first '.$result['rows']->count().' of '.(int) $result['total'].' borrow requests.';
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    public function inventoryList(array $result): string
    {
        $heading = 'Inventory items';

        if ($result['out_of_stock']) {
            $heading = 'Out of stock items';
        } elseif ($result['low_stock']) {
            $heading = 'Low stock items';
        } elseif ($result['statuses'] !== []) {
            $heading = 'Inventory items — status: '.implode(', ', $result['statuses']);
        }

        $lines = ['**'.$heading.' ('.(int) $result['total'].' found)**'];
        $lines[] = '• Total quantity: '.(int) $result['total_quantity']
            .' · Total value: ₱'.number_format((float) $result['total_value'], 2);

        if ($result['total'] === 0) {
            $lines[] = '• No inventory records match that request.';

            return implode("\n", $lines);
        }

        $lines[] = '';

        foreach ($result['rows'] as $item) {
            $quantity = (int) $item->quantity;
            $threshold = (int) $item->low_stock_threshold;
            $flag = $quantity === 0 ? 'OUT OF STOCK' : ($quantity <= $threshold ? 'LOW STOCK' : null);

            $lines[] = '• '.$item->item_code.' · '.$item->name
                .' · qty '.$quantity.' / threshold '.$threshold
                .' · status: '.$item->status
                .($item->category?->name ? ' · '.$item->category->name : '')
                .($item->location ? ' · '.$item->location : '')
                .($flag !== null ? ' · '.$flag : '');
        }

        if ($result['total'] > $result['rows']->count()) {
            $lines[] = '• Showing the first '.$result['rows']->count().' of '.(int) $result['total'].' inventory items.';
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<int, array<string, mixed>>  $records
     */
    public function referenceRecords(array $records): string
    {
        $blocks = [];

        foreach ($records as $entry) {
            $record = $entry['record'];

            if ($entry['type'] === 'booking' && $record instanceof Booking) {
                $lines = ['**Booking '.$record->reference_number.'**'];
                $lines[] = '• Customer: '.$record->full_name;
                $lines[] = '• Email: '.($record->email ?: 'not recorded');
                $lines[] = '• Contact: '.($record->contact_number ?: 'not recorded');
                $lines[] = '• Preferred: '.$this->date($record->preferred_date)
                    .($record->preferred_time_formatted ? ' at '.$record->preferred_time_formatted : '');
                $lines[] = '• Status: '.$record->status;
                $lines[] = '• Purpose: '.$record->purpose_category;
                $lines[] = '• Assigned employee: '.($record->employee?->name ?? 'not assigned');

                if ($record->remarks) {
                    $lines[] = '• Remarks: '.$record->remarks;
                }

                $blocks[] = implode("\n", $lines);

                continue;
            }

            if ($entry['type'] === 'borrow_request' && $record instanceof BorrowRequest) {
                $lines = ['**Borrow request '.$record->request_number.'**'];
                $lines[] = '• Employee: '.($record->employee?->name ?? 'unknown employee');
                $lines[] = '• Status: '.$record->status;
                $lines[] = '• Borrow date: '.$this->date($record->borrow_date).' · Due: '.$this->date($record->due_date);

                if ($record->return_date !== null) {
                    $lines[] = '• Return date: '.$this->date($record->return_date);
                }

                if ($record->borrowItems->isNotEmpty()) {
                    $lines[] = '• Items: '.$record->borrowItems->map(fn (BorrowItem $item) => ($item->inventory?->name ?? 'unknown item')
                        .' x'.$item->quantity
                        .($item->is_returned ? ' (returned)' : ''))->implode(', ');
                }

                if ($record->admin_remarks) {
                    $lines[] = '• Remarks: '.$record->admin_remarks;
                }

                $blocks[] = implode("\n", $lines);

                continue;
            }

            if ($entry['type'] === 'inventory' && $record instanceof Inventory) {
                $quantity = (int) $record->quantity;
                $threshold = (int) $record->low_stock_threshold;

                $lines = ['**Inventory '.$record->item_code.' — '.$record->name.'**'];
                $lines[] = '• Quantity: '.$quantity.' (low stock threshold '.$threshold.')';
                $lines[] = '• Status: '.$record->status.' · Condition: '.$record->condition;
                $lines[] = '• Category: '.($record->category?->name ?? 'uncategorised')
                    .' · Location: '.($record->location ?: 'not recorded');
                $lines[] = '• Unit cost: ₱'.number_format((float) $record->unit_cost, 2)
                    .' · Total value: ₱'.number_format($quantity * (float) $record->unit_cost, 2);
                $lines[] = '• Stock flag: '.($quantity === 0 ? 'Out of stock' : ($quantity <= $threshold ? 'Low stock' : 'OK'));

                $blocks[] = implode("\n", $lines);
            }
        }

        return implode("\n\n", $blocks);
    }

    /**
     * @param  array<int, string>  $tokens
     */
    public function noReferenceMatch(array $tokens): string
    {
        return '**No records were found for "'.implode('", "', $tokens).'".**'."\n\n"
            .'I searched booking references, borrow request numbers, and inventory item codes. '
            .'Check the reference, or ask "list bookings" or "list borrow requests" to see the available records.';
    }

    /**
     * @param  array<string, mixed>  $tasks
     */
    public function tasks(array $tasks): string
    {
        $lines = ['**Pending work across the system**'];
        $lines[] = '• Bookings awaiting action: '.(int) $tasks['pending_bookings'];
        $lines[] = '• Borrow requests awaiting action: '.(int) $tasks['pending_borrow_requests'];
        $lines[] = '• Overdue borrow requests: '.(int) $tasks['overdue_borrow_requests'];

        if ($tasks['pending_bookings'] === 0 && $tasks['pending_borrow_requests'] === 0 && $tasks['overdue_borrow_requests'] === 0) {
            $lines[] = '• No pending bookings, pending borrow requests, or overdue items were found in the database.';
        }

        if ($tasks['pending_booking_rows']->isNotEmpty()) {
            $lines[] = '';
            $lines[] = '**Pending bookings ('.$tasks['pending_booking_rows']->count().' shown)**';

            foreach ($tasks['pending_booking_rows'] as $booking) {
                $lines[] = $this->bookingLine($booking, includeName: true);
            }
        }

        if ($tasks['pending_borrow_rows']->isNotEmpty()) {
            $lines[] = '';
            $lines[] = '**Borrow requests awaiting action ('.$tasks['pending_borrow_rows']->count().' shown)**';

            foreach ($tasks['pending_borrow_rows'] as $request) {
                $lines[] = $this->borrowLine($request, includeName: true);
            }
        }

        if ($tasks['overdue_rows']->isNotEmpty()) {
            $lines[] = '';
            $lines[] = '**Overdue borrow requests ('.$tasks['overdue_rows']->count().' shown)**';

            foreach ($tasks['overdue_rows'] as $request) {
                $lines[] = $this->borrowLine($request, includeName: true);
            }
        }

        $lines[] = '';
        $lines[] = '**Completed records**';
        $lines[] = '• Completed bookings: '.(int) $tasks['completed_bookings'];
        $lines[] = '• Returned borrow requests: '.(int) $tasks['returned_requests'];

        if ($tasks['scheduled_tasks']->isNotEmpty()) {
            $lines[] = '';
            $lines[] = '**Recent scheduled system tasks**';

            foreach ($tasks['scheduled_tasks'] as $task) {
                $lines[] = $this->scheduledTaskLine($task);
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $activity
     */
    public function recentActivity(array $activity): string
    {
        $lines = ['**Recent system activity**'];

        $lines[] = '';
        $lines[] = '**Latest bookings ('.$activity['bookings']->count().' shown)**';

        if ($activity['bookings']->isEmpty()) {
            $lines[] = '• No booking records were found.';
        }

        foreach ($activity['bookings'] as $booking) {
            $lines[] = $this->bookingLine($booking, includeName: true);
        }

        $lines[] = '';
        $lines[] = '**Latest borrow requests ('.$activity['borrow_requests']->count().' shown)**';

        if ($activity['borrow_requests']->isEmpty()) {
            $lines[] = '• No borrow request records were found.';
        }

        foreach ($activity['borrow_requests'] as $request) {
            $lines[] = $this->borrowLine($request, includeName: true);
        }

        $lines[] = '';
        $lines[] = '**Latest returned items ('.$activity['returns']->count().' shown)**';

        if ($activity['returns']->isEmpty()) {
            $lines[] = '• No returned item records were found.';
        }

        foreach ($activity['returns'] as $item) {
            $lines[] = $this->returnedItemLine($item);
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $person
     */
    private function personHeading(array $person): string
    {
        if ($person['type'] === 'user') {
            return '**User found: '.$person['name'].'** (#'.$person['id'].')'."\n"
                .'• Email: '.($person['email'] ?: 'not recorded')."\n"
                .'• Role: '.($person['role'] ?: 'no role assigned')."\n"
                .'• Status: '.($person['is_active'] ? 'Active' : 'Inactive');
        }

        return '**Booking contact found: '.$person['name'].'**'."\n"
            .'• Email: '.($person['email'] ?: 'not recorded')."\n"
            .'• Account: no user or employee account is linked to this name';
    }

    private function bookingLine(Booking $booking, bool $includeName = false): string
    {
        $parts = [$this->date($booking->preferred_date)];

        if ($booking->preferred_time_formatted) {
            $parts[] = $booking->preferred_time_formatted;
        }

        if ($includeName) {
            $parts[] = $booking->full_name;
        }

        $parts[] = $booking->reference_number;
        $parts[] = 'status: '.$booking->status;
        $parts[] = $booking->purpose_category;

        return '• '.implode(' · ', array_filter($parts, fn ($part) => $part !== null && $part !== ''));
    }

    private function borrowLine(BorrowRequest $request, bool $includeName = false): string
    {
        $parts = ['Borrowed '.$this->date($request->borrow_date)];
        $parts[] = 'due '.$this->date($request->due_date);

        if ($request->return_date !== null) {
            $parts[] = 'returned '.$this->date($request->return_date);
        }

        $parts[] = $request->request_number;

        if ($includeName) {
            $parts[] = $request->employee?->name ?? 'unknown employee';
        }

        $parts[] = 'status: '.$request->status;

        $line = '• '.implode(' · ', $parts);

        if ($request->relationLoaded('borrowItems') && $request->borrowItems->isNotEmpty()) {
            $items = $request->borrowItems->map(fn (BorrowItem $item) => ($item->inventory?->name ?? 'unknown item')
                .' x'.$item->quantity
                .($item->is_returned ? ' (returned)' : ''))->implode(', ');

            $line .= "\n   Items: ".$items;
        }

        return $line;
    }

    private function returnedItemLine(BorrowItem $item): string
    {
        $returnedAt = $item->returned_at ?? $item->borrowRequest?->returned_at;

        return '• '.$this->date($returnedAt)
            .' · '.($item->inventory?->name ?? 'unknown item')
            .' x'.$item->quantity
            .($item->borrowRequest?->request_number ? ' · '.$item->borrowRequest->request_number : '')
            .($item->condition_returned ? ' · condition: '.$item->condition_returned : '');
    }

    private function scheduledTaskLine(ScheduledTask $task): string
    {
        $parts = [$task->command, 'status: '.$task->status];

        if ($task->started_at !== null) {
            $parts[] = 'started '.$this->dateTime($task->started_at);
        }

        if ($task->finished_at !== null) {
            $parts[] = 'finished '.$this->dateTime($task->finished_at);
        }

        if ($task->failure_message) {
            $parts[] = 'failure: '.$task->failure_message;
        }

        return '• '.implode(' · ', $parts);
    }

    /**
     * @param  array<string, int|string>  $counts
     */
    private function statusSummary(array $counts): string
    {
        if ($counts === []) {
            return 'none';
        }

        $order = ['pending', 'approved', 'borrowed', 'completed', 'returned', 'rejected', 'cancelled'];
        $parts = [];

        foreach ($order as $status) {
            if (isset($counts[$status])) {
                $parts[] = ucfirst($status).': '.(int) $counts[$status];
            }
        }

        foreach ($counts as $status => $count) {
            if (! in_array($status, $order, true)) {
                $parts[] = ucfirst((string) $status).': '.(int) $count;
            }
        }

        return $parts === [] ? 'none' : implode(' · ', $parts);
    }

    private function date(?CarbonInterface $date): string
    {
        return $date?->format('M j, Y') ?? 'date not recorded';
    }

    private function dateTime(?CarbonInterface $date): string
    {
        return $date?->format('M j, Y g:i A') ?? 'time not recorded';
    }
}
