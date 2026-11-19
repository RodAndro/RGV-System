<?php

namespace App\Services\Chatbot;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Throwable;

/**
 * Turns a free-text administrator question into structured lookup hints:
 * the topic, candidate person names, a date range and status filters.
 *
 * The parser never guesses facts. Name candidates are only hints; they are
 * confirmed against the database before anything is reported.
 */
class QuestionParser
{
    /** @var array<string, int> */
    private const MONTHS = [
        'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4,
        'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8,
        'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
        'sept' => 9, 'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4,
        'jun' => 6, 'jul' => 7, 'aug' => 8, 'sep' => 9, 'oct' => 10,
        'nov' => 11, 'dec' => 12,
    ];

    /** @var array<int, string> */
    public const STOPWORDS = [
        'the', 'a', 'an', 'is', 'are', 'was', 'were', 'am', 'be', 'been', 'being',
        'do', 'does', 'did', 'done', 'has', 'have', 'had', 'can', 'could', 'should',
        'would', 'will', 'shall', 'may', 'might', 'must', 'what', 'who', 'whom', 'whose',
        'which', 'when', 'where', 'why', 'how', 'any', 'anyone', 'anybody', 'someone',
        'somebody', 'everyone', 'everybody', 'nobody', 'name', 'named', 'names', 'called',
        'person', 'persons', 'people', 'user', 'users', 'employee', 'employees', 'admin',
        'admins', 'staff', 'account', 'accounts', 'report', 'reports', 'system', 'record',
        'records', 'data', 'dataset', 'database', 'db', 'info', 'information', 'details',
        'detail', 'show', 'list', 'display', 'give', 'tell', 'need', 'want', 'know', 'check',
        'find', 'search', 'look', 'see', 'view', 'explain', 'analyze', 'analyse', 'analysis',
        'book', 'booked', 'booking', 'bookings', 'reserve', 'reserved', 'reservation',
        'reservations', 'borrow', 'borrows', 'borrowed', 'borrowing', 'borrower', 'borrowers',
        'request', 'requests', 'loan', 'loans', 'return', 'returned', 'returns', 'returning',
        'item', 'items', 'inventory', 'stock', 'stocks', 'task', 'tasks', 'activity',
        'activities', 'history', 'status', 'pending', 'approved', 'completed', 'complete',
        'rejected', 'cancelled', 'canceled', 'overdue', 'due', 'date', 'dates', 'range',
        'between', 'from', 'to', 'until', 'through', 'and', 'or', 'in', 'on', 'at', 'for',
        'of', 'by', 'under', 'with', 'about', 'this', 'that', 'these', 'those', 'specific',
        'certain', 'there', 'here', 'me', 'my', 'our', 'your', 'their', 'his', 'her', 'its',
        'it', 'they', 'them', 'we', 'you', 'he', 'she', 'i', 's', 't', 'current', 'recent',
        'recently', 'latest', 'last', 'today', 'yesterday', 'week', 'month', 'year', 'all',
        'total', 'count', 'number', 'many', 'much', 'best', 'top', 'per', 'each', 'every',
        'pattern', 'patterns', 'trend', 'trends', 'summary', 'summaries', 'overview',
        'insight', 'insights', 'statistic', 'statistics', 'stats', 'metric', 'metrics',
        'available', 'maintenance', 'damaged', 'active', 'inactive', 'same', 'other',
        'matches', 'match', 'matching', 'exact', 'similar', 'please', 'thanks', 'thank',
        'january', 'february', 'march', 'april', 'may', 'june', 'july', 'august',
        'september', 'october', 'november', 'december', 'jan', 'feb', 'mar', 'apr',
        'jun', 'jul', 'aug', 'sep', 'sept', 'oct', 'nov', 'dec', 'rgv', 'rgvtech',
        'services', 'admin dashboard', 'dashboard', 'chatbot', 'assistant',
        'low', 'high', 'open', 'closed', 'missing', 'broken', 'late', 'early', 'old', 'new',
        'next', 'previous', 'monthly', 'daily', 'weekly', 'yearly', 'quarterly', 'unpaid', 'paid',
        'recorded', 'saved', 'registered', 'exists', 'existing', 'stored', 'listed',
        'hello', 'hi', 'hey', 'yes', 'no', 'ok', 'okay', 'sure', 'then', 'there',
        'audit', 'audits', 'import', 'imports', 'notification', 'notifications',
        'settings', 'backup', 'backups', 'trash', 'health', 'schedule', 'scheduled', 'login',
    ];

    /**
     * @return array{
     *     topic: string,
     *     name_phrases: array<int, string>,
     *     date_range: array<string, mixed>|null,
     *     statuses: array<int, string>,
     *     wants_count: bool,
     *     is_person_question: bool,
     *     asks_for_name: bool,
     *     report_meta: bool,
     *     wants_activity: bool
     * }
     */
    public function parse(string $message): array
    {
        $text = Str::lower($message);

        return [
            'topic' => $this->topic($text),
            'name_phrases' => $this->namePhrases($message),
            'date_range' => $this->dateRange($text),
            'statuses' => $this->statuses($text),
            'wants_count' => (bool) preg_match('/\b(how\s+many|count|number\s+of|total)\b/', $text),
            'is_person_question' => (bool) preg_match(
                '/\b(named|called|who|whom|whose|person|people|profile|user|users|employee|employees|did|does|has|have|about|booked|borrowed|returned)\b/',
                $text
            ) || (bool) preg_match(
                '/\b(is\s+there|are\s+there|do\s+we\s+have|do\s+you\s+have|anyone|anybody|recorded|registered|saved|stored|exists|in\s+the\s+(system|database|records))\b/',
                $text
            ),
            'asks_for_name' => (bool) preg_match('/(\bnamed\b|\bcalled\b|\bname\b|\bperson\'?s\b)/', $text),
            'report_meta' => (bool) preg_match('/\b(which|what|where)\b[^?]*\breports?\b/', $text),
            'wants_activity' => (bool) preg_match('/\b(activity|activities|recent|latest|happening|log|logs)\b/', $text),
            'wants_list' => (bool) preg_match('/\b(list|show|display|enumerate|all|every|each|who\s+are|which\s+(items?|bookings?|requests?|users?|employees?|admins?)|what\s+(items?|bookings?|requests?|users?|employees?|admins?))\b/', $text),
            'low_stock' => (bool) preg_match('/\b(low[\s-]*stock|below\s+(the\s+)?(low[\s-]*stock\s+)?threshold|at\s+or\s+below\s+(the\s+)?threshold)\b/', $text),
            'out_of_stock' => (bool) preg_match('/\b(out[\s-]*of[\s-]*stock|zero\s+stock|no\s+stock)\b/', $text),
        ];
    }

    private function topic(string $text): string
    {
        if (preg_match('/\b(tasks?|to-?do|pending\s+work|overdue)\b/', $text) === 1) {
            return 'tasks';
        }

        if (preg_match('/\b(return|returns|returned|returning)\b/', $text) === 1) {
            return 'returns';
        }

        if (preg_match('/\b(borrow|borrows|borrowed|borrowing|borrower|loan|loans|request|requests)\b/', $text) === 1) {
            return 'borrowings';
        }

        if (preg_match('/\b(booking|bookings|booked|reservation|reservations|reserve|reserved)\b/', $text) === 1) {
            return 'bookings';
        }

        if (preg_match('/\b(inventory|inventories|stock|stocks|item|items|equipment|tools?)\b/', $text) === 1) {
            return 'inventory';
        }

        if (preg_match('/\b(user|users|employee|employees|person|people|profile|account|accounts)\b/', $text) === 1) {
            return 'people';
        }

        return 'overview';
    }

    /**
     * @return array<int, string>
     */
    private function namePhrases(string $message): array
    {
        $phrases = [];

        if (preg_match_all('/["\x{201C}\x{201D}]([^"\x{201C}\x{201D}]{2,60})["\x{201C}\x{201D}]/u', $message, $matches) > 0) {
            $phrases = array_merge($phrases, $matches[1]);
        }

        if (preg_match_all('/\b(?:named|called)\s+([\p{L}][\p{L}.\'-]*(?:\s+[\p{L}][\p{L}.\'-]*){0,3})/iu', $message, $matches) > 0) {
            $phrases = array_merge($phrases, $matches[1]);
        }

        if (preg_match_all('/\b(?:name\s+is|name\'s)\s+([\p{L}][\p{L}.\'-]*(?:\s+[\p{L}][\p{L}.\'-]*){0,3})/iu', $message, $matches) > 0) {
            $phrases = array_merge($phrases, $matches[1]);
        }

        if (preg_match_all('/\b(?:is|are)\s+there\s+(?:anyone|anybody|someone|somebody|any|some|a|an|the)?\s*([\p{L}][\p{L}.\'-]{1,30}(?:\s+[\p{L}][\p{L}.\'-]{1,30})?)/iu', $message, $matches) > 0) {
            $phrases = array_merge($phrases, $matches[1]);
        }

        if (preg_match_all('/\b(?:do\s+we\s+have|do\s+you\s+have|have\s+we\s+got)\s+(?:any\s+|a\s+|an\s+)?([\p{L}][\p{L}.\'-]{1,30}(?:\s+[\p{L}][\p{L}.\'-]{1,30})?)/iu', $message, $matches) > 0) {
            $phrases = array_merge($phrases, $matches[1]);
        }

        if (preg_match_all('/\b(?:is|are)\s+([\p{L}][\p{L}.\'-]{2,30})\s+(?:recorded|registered|saved|stored|listed|in\s+the\s+(?:system|database|users|records?))\b/iu', $message, $matches) > 0) {
            $phrases = array_merge($phrases, $matches[1]);
        }

        if (preg_match_all('/\b(?:how\s+about|what\s+about|try|maybe)\s+([\p{L}][\p{L}.\'-]{1,30}(?:\s+[\p{L}][\p{L}.\'-]{1,30})?)/iu', $message, $matches) > 0) {
            $phrases = array_merge($phrases, $matches[1]);
        }

        if (preg_match_all('/\b(?:who\s+is|who\'s|find|search(?:\s+for)?|look\s+(?:up|for)|check|show|display|get)\s+(?:me\s+)?(?:the\s+)?(?:user|employee|person)?\s*([\p{L}][\p{L}.\'-]{1,30}(?:\s+[\p{L}][\p{L}.\'-]{1,30})?)/iu', $message, $matches) > 0) {
            $phrases = array_merge($phrases, $matches[1]);
        }

        if (preg_match_all('/\b([\p{L}][\p{L}.\'-]{1,30})\'s\b/u', $message, $matches) > 0) {
            $phrases = array_merge($phrases, $matches[1]);
        }

        if (preg_match_all('/\b(?:did|does|has|have|had)\s+([\p{L}][\p{L}.\'-]{2,30}(?:\s+[\p{L}][\p{L}.\'-]{2,30})?)/iu', $message, $matches) > 0) {
            $phrases = array_merge($phrases, $matches[1]);
        }

        if (preg_match_all('/\b(\p{Lu}[\p{Ll}]+(?:\s+\p{Lu}[\p{Ll}]+){0,2})\b/u', $message, $matches) > 0) {
            $phrases = array_merge($phrases, $matches[1]);
        }

        $bare = trim((string) preg_replace('/[^\p{L}\p{N}\s\'\-]/u', '', $message));
        $bare = trim((string) preg_replace('/\s+/', ' ', $bare));
        $bareWords = $bare === '' ? [] : (preg_split('/\s+/', $bare) ?: []);

        $isCapitalised = preg_match('/\p{Lu}/u', $bare) === 1;

        if ($bareWords !== []
            && count($bareWords) <= 3
            && (count($bareWords) === 1 || $isCapitalised)
            && ! $this->isStopwordPhrase(Str::lower($bare))
        ) {
            $phrases[] = $bare;
        }

        $phrases = array_map(fn ($phrase) => $this->trimNamePhrase((string) $phrase), $phrases);
        $phrases = array_filter($phrases, fn (string $phrase) => $this->isUsablePhrase($phrase));

        return array_values(array_unique($phrases));
    }

    /**
     * Keep only the leading run of non-stopword words so a capture such as
     * "Rod in the users" becomes "Rod".
     */
    private function trimNamePhrase(string $phrase): string
    {
        $kept = [];

        foreach (preg_split('/\s+/', trim($phrase)) ?: [] as $word) {
            if ($this->isStopword($word)) {
                break;
            }

            $kept[] = $word;
        }

        return trim(implode(' ', $kept));
    }

    private function isStopword(string $word): bool
    {
        return in_array(trim($word, " \t\n\r\0\x0B.,!?;:\"'()[]"), self::STOPWORDS, true);
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

    private function isUsablePhrase(string $phrase): bool
    {
        $phrase = trim((string) preg_replace('/\s+/', ' ', $phrase));

        if (mb_strlen($phrase) < 2 || mb_strlen($phrase) > 60) {
            return false;
        }

        $words = preg_split('/\s+/', Str::lower($phrase)) ?: [];
        $words = array_filter($words, fn (string $word) => ! in_array(trim($word, " \t\n\r\0\x0B.,!?;:\"'()[]"), self::STOPWORDS, true));

        return $words !== [];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function dateRange(string $text): ?array
    {
        $now = CarbonImmutable::now();

        if (preg_match('/\btoday\b/', $text) === 1) {
            return $this->makeRange($now->startOfDay(), $now->endOfDay(), false);
        }

        if (preg_match('/\byesterday\b/', $text) === 1) {
            $day = $now->subDay();

            return $this->makeRange($day->startOfDay(), $day->endOfDay(), false);
        }

        if (preg_match('/\bthis\s+week\b/', $text) === 1) {
            return $this->makeRange($now->startOfWeek(), $now->endOfWeek(), false);
        }

        if (preg_match('/\blast\s+week\b/', $text) === 1) {
            $week = $now->subWeek();

            return $this->makeRange($week->startOfWeek(), $week->endOfWeek(), false);
        }

        if (preg_match('/\bthis\s+month\b/', $text) === 1) {
            return $this->makeRange($now->startOfMonth(), $now->endOfMonth(), false);
        }

        if (preg_match('/\blast\s+month\b/', $text) === 1) {
            $month = $now->subMonthNoOverflow();

            return $this->makeRange($month->startOfMonth(), $month->endOfMonth(), false);
        }

        if (preg_match('/\bthis\s+year\b/', $text) === 1) {
            return $this->makeRange($now->startOfYear(), $now->endOfYear(), false);
        }

        if (preg_match('/\blast\s+year\b/', $text) === 1) {
            $year = $now->subYearNoOverflow();

            return $this->makeRange($year->startOfYear(), $year->endOfYear(), false);
        }

        if (preg_match('/\b(\d{4})-(\d{1,2})-(\d{1,2})\b\s*(?:to|until|through|and|–|—|-)\s*(\d{4})-(\d{1,2})-(\d{1,2})\b/', $text, $matches) === 1) {
            try {
                $start = CarbonImmutable::create((int) $matches[1], (int) $matches[2], (int) $matches[3]);
                $end = CarbonImmutable::create((int) $matches[4], (int) $matches[5], (int) $matches[6]);

                if ($start && $end) {
                    return $this->makeRange($start->startOfDay(), $end->endOfDay(), false);
                }
            } catch (Throwable) {
                // Fall through to the month-name parsers.
            }
        }

        $months = implode('|', array_keys(self::MONTHS));

        if (preg_match('/\b('.$months.')\s+(\d{1,2})(?:st|nd|rd|th)?(?:\s*,?\s*(\d{4}))?\s*(?:to|until|through|and|–|—|-)\s*('.$months.')\s+(\d{1,2})(?:st|nd|rd|th)?(?:\s*,?\s*(\d{4}))?/i', $text, $matches) === 1) {
            $startMonth = self::MONTHS[Str::lower($matches[1])];
            $endMonth = self::MONTHS[Str::lower($matches[4])];
            $startYearValue = isset($matches[3]) && $matches[3] !== '' ? (int) $matches[3] : null;
            $endYearValue = isset($matches[6]) && $matches[6] !== '' ? (int) $matches[6] : null;
            $yearsProvided = $startYearValue !== null || $endYearValue !== null;

            $startYear = $startYearValue ?? $endYearValue ?? (int) $now->year;
            $endYear = $endYearValue ?? $startYear;

            try {
                $start = CarbonImmutable::create($startYear, $startMonth, (int) $matches[2]);
                $end = CarbonImmutable::create($endYear, $endMonth, (int) $matches[5]);

                if ($start && $end) {
                    if (! $yearsProvided && $end->lessThan($start)) {
                        $end = $end->addYear();
                    }

                    return $this->makeRange($start->startOfDay(), $end->endOfDay(), ! $yearsProvided, $startMonth, $endMonth);
                }
            } catch (Throwable) {
                // Fall through to the remaining date patterns.
            }
        }

        if (preg_match('/\b('.$months.')\b(?:\s*,?\s*(\d{4}))?\s*(?:to|until|through|and|–|—|-)\s*\b('.$months.')\b(?:\s*,?\s*(\d{4}))?/i', $text, $matches) === 1) {
            $startMonth = self::MONTHS[Str::lower($matches[1])];
            $endMonth = self::MONTHS[Str::lower($matches[3])];
            $startYear = isset($matches[2]) && $matches[2] !== '' ? (int) $matches[2] : null;
            $endYear = isset($matches[4]) && $matches[4] !== '' ? (int) $matches[4] : null;
            $assumed = $startYear === null && $endYear === null;

            $startYear ??= $endYear ?? (int) $now->year;
            $endYear ??= $startYear;

            if ($endMonth < $startMonth && $endYear === $startYear) {
                $endYear++;
            }

            return $this->monthRange($startYear, $startMonth, $endYear, $endMonth, $assumed);
        }

        if (preg_match('/\b('.$months.')\s+(\d{4})\b/i', $text, $matches) === 1) {
            $month = self::MONTHS[Str::lower($matches[1])];
            $year = (int) $matches[2];

            return $this->monthRange($year, $month, $year, $month, false);
        }

        if (preg_match('/\b(?:in|during|for|of|from|since)\s+('.$months.')\b(?:\s*,?\s*(\d{4}))?/i', $text, $matches) === 1) {
            $month = self::MONTHS[Str::lower($matches[1])];
            $hasYear = isset($matches[2]) && $matches[2] !== '';
            $year = $hasYear ? (int) $matches[2] : (int) $now->year;

            return $this->monthRange($year, $month, $year, $month, ! $hasYear);
        }

        if (preg_match('/\b(?:in|during|for|of|year)\s+(\d{4})\b/', $text, $matches) === 1) {
            $year = (int) $matches[1];

            if ($year >= 2000 && $year <= 2100) {
                try {
                    $start = CarbonImmutable::create($year, 1, 1);
                    $end = CarbonImmutable::create($year, 12, 31);

                    if ($start && $end) {
                        return $this->makeRange($start->startOfYear(), $end->endOfYear(), false, 1, 12);
                    }
                } catch (Throwable) {
                    return null;
                }
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function statuses(string $text): array
    {
        $statuses = [];

        foreach (['pending', 'approved', 'completed', 'rejected', 'cancelled', 'canceled', 'borrowed', 'returned', 'overdue', 'active', 'inactive', 'available', 'maintenance', 'damaged'] as $status) {
            if (preg_match('/\b'.$status.'\b/', $text) === 1) {
                $statuses[] = $status === 'canceled' ? 'cancelled' : $status;
            }
        }

        return array_values(array_unique($statuses));
    }

    /**
     * @return array<string, mixed>
     */
    private function monthRange(int $startYear, int $startMonth, int $endYear, int $endMonth, bool $yearAssumed): array
    {
        $start = CarbonImmutable::create($startYear, $startMonth, 1);
        $end = CarbonImmutable::create($endYear, $endMonth, 1);

        $start = $start ? $start->startOfMonth() : CarbonImmutable::now()->startOfMonth();
        $end = $end ? $end->endOfMonth() : CarbonImmutable::now()->endOfMonth();

        return $this->makeRange($start, $end, $yearAssumed, $startMonth, $endMonth);
    }

    /**
     * @return array<string, mixed>
     */
    private function makeRange(CarbonImmutable $start, CarbonImmutable $end, bool $yearAssumed, ?int $startMonth = null, ?int $endMonth = null): array
    {
        if ($start->greaterThan($end)) {
            [$start, $end] = [$end, $start];
        }

        $label = $start->format('M j, Y') === $end->format('M j, Y')
            ? $start->format('M j, Y')
            : $start->format('M j, Y').' – '.$end->format('M j, Y');

        return [
            'start' => $start->startOfDay(),
            'end' => $end->endOfDay(),
            'label' => $label,
            'year_assumed' => $yearAssumed,
            'start_month' => $startMonth ?? (int) $start->month,
            'end_month' => $endMonth ?? (int) $end->month,
        ];
    }
}
