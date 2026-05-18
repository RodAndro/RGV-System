<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Inventory;
use App\Models\BorrowRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\OllamaService;
use App\Services\GeminiService;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ChatbotController extends Controller
{
    private OllamaService $ollama;
    private GeminiService $gemini;

    public function __construct(OllamaService $ollama, GeminiService $gemini)
    {
        $this->ollama = $ollama;
        $this->gemini = $gemini;
    }

    /**
     * Handle chatbot queries
     */
    public function query(Request $request)
    {
        $message = '';
        $pageType = 'reports';
        $requestedPageType = 'reports';

        try {
            $data = $request->validate([
                'message' => 'required|string',
                'pageType' => 'sometimes|string',
                'history' => 'sometimes|array',
            ]);

            $message = $data['message'];
            $pageType = $data['pageType'] ?? 'reports';
            $history = $data['history'] ?? [];
            $requestedPageType = $this->resolvePageType($message, $pageType);

            if ($this->isGreeting($message)) {
                return response()->json([
                    'success' => true,
                    'response' => $this->generateGreetingResponse(),
                ]);
            }

            // Get context based on the user's requested report, not only the current page.
            $context = $this->getContext($requestedPageType);

            if ($this->shouldUseDirectReportResponse($message)) {
                return response()->json([
                    'success' => true,
                    'response' => $this->generateResponse(strtolower($message), $context, $requestedPageType),
                ]);
            }

            // Create system prompt with context
            $systemPrompt = $this->createSystemPrompt($context, $requestedPageType);

            // Build Ollama chat messages
            $conversation = [];
            $conversation[] = ['role' => 'system', 'content' => $systemPrompt];

            // Add history context
            foreach (array_slice($history, -5) as $msg) {
                $role = ($msg['type'] ?? null) === 'user' ? 'user' : 'assistant';
                $content = trim((string) ($msg['content'] ?? ''));

                if ($content !== '') {
                    $conversation[] = ['role' => $role, 'content' => $content];
                }
            }

            // Add current message
            $conversation[] = ['role' => 'user', 'content' => $message];

            // Try Ollama first
            $response = $this->ollama->generateFromConversation($conversation);

            if ($response['success']) {
                return response()->json([
                    'success' => true,
                    'response' => $response['text']
                ]);
            }

            // Fallback to Gemini if Ollama fails
            $geminiPrompt = $systemPrompt . "\n\nUser: " . $message;
            $geminiResponse = $this->gemini->generateText($geminiPrompt, 'gemini-2.0-flash');

            if ($geminiResponse['success']) {
                return response()->json([
                    'success' => true,
                    'response' => $geminiResponse['text']
                ]);
            }

            // Final fallback to rule-based response if both AI services fail
            $fallbackResponse = $this->generateResponse(strtolower($message), $context, $requestedPageType);
            return response()->json([
                'success' => true,
                'response' => $fallbackResponse
            ]);
        } catch (\Exception $e) {
            // Fallback to rule-based response on error
            $context = $this->getContext($requestedPageType);
            $fallbackResponse = $this->generateResponse(strtolower($message), $context, $requestedPageType);
            return response()->json([
                'success' => true,
                'response' => $fallbackResponse
            ]);
        }
    }

    /**
     * Create system prompt with context
     */
    private function createSystemPrompt(array $context, string $pageType): string
    {
        $contextText = $this->formatContext($context, $pageType);

        return "You are an AI Support Assistant for RGV Multi-Tech Services, a technical services company. You help users with reports, data analysis, and system navigation. Always provide factual, accurate information based on the context data provided.

Selected Report Context: {$pageType} Report

Context Data:
{$contextText}

IMPORTANT INSTRUCTIONS:
1. Always base your answers on the exact data provided in Context Data above
2. The user can ask about any report. Answer using the Selected Report Context, even if earlier chat history mentioned a different report.
3. Do NOT provide placeholder text like '[Insert Total]' or 'Please note that this information is not available'
4. Format numerical data clearly with proper labels
5. Use markdown formatting for better readability
6. Include relevant emojis for visual appeal
7. For questions about inventory, always include: Total Items, Available, Low Stock, and Total Quantity
8. Be specific with numbers and facts - avoid vague answers

Your capabilities:
- Answer questions about reports and data with factual accuracy
- Generate summaries and analytics from the provided context
- Explain inventory and booking data in detail
- Assist with borrow request tracking
- Provide user statistics and insights
- Help with filtering, searching, and exporting reports
- Provide step-by-step guidance for using report pages";
    }

    /**
     * Resolve which report the user is asking about.
     */
    private function resolvePageType(string $message, string $defaultPageType): string
    {
        $message = Str::lower($message);

        if (Str::contains($message, ['inventory', 'stock', 'stocks', 'item', 'items', 'low stock', 'out of stock'])) {
            return 'inventory';
        }

        if (Str::contains($message, ['borrow', 'borrowing', 'borrow request', 'borrow requests', 'request tracking'])) {
            return 'borrow_requests';
        }

        if (Str::contains($message, ['booking', 'bookings', 'reservation', 'reservations'])) {
            return 'bookings';
        }

        if (Str::contains($message, ['user', 'users', 'employee', 'employees', 'admin', 'admins'])) {
            return 'users';
        }

        return in_array($defaultPageType, ['bookings', 'inventory', 'borrow_requests', 'users'], true)
            ? $defaultPageType
            : 'reports';
    }

    /**
     * Use deterministic responses for report facts so stale chat history cannot override data.
     */
    private function shouldUseDirectReportResponse(string $message): bool
    {
        return Str::contains(Str::lower($message), [
            'status',
            'inventory',
            'stock',
            'booking',
            'borrow',
            'request',
            'user',
            'employee',
            'export',
            'download',
            'filter',
            'search',
            'help',
        ]);
    }

    /**
     * Detect simple greetings so the assistant does not dump the current report immediately.
     */
    private function isGreeting(string $message): bool
    {
        return (bool) preg_match('/^\s*(hi|hello|hey|good morning|good afternoon|good evening)\s*[.!?]*\s*$/i', $message);
    }

    /**
     * Format context data for the prompt
     */
    private function formatContext(array $context, string $pageType): string
    {
        $text = "";

        switch ($pageType) {
            case 'bookings':
                $text .= "- Total Bookings: {$context['total_bookings']}\n";
                $text .= "- Pending: {$context['pending_bookings']}\n";
                $text .= "- Approved: {$context['approved_bookings']}\n";
                $text .= "- Completed: {$context['completed_bookings']}\n";
                $text .= "- Rejected: {$context['rejected_bookings']}\n";
                break;

            case 'inventory':
                $text .= "- Total Items: {$context['total_items']}\n";
                $text .= "- Available Items: {$context['available_items']}\n";
                $text .= "- Total Quantity: {$context['total_quantity']}\n";
                $text .= "- Low Stock Items: {$context['low_stock_items']}\n";
                $text .= "- Out of Stock: {$context['out_of_stock']}\n";
                $text .= "- Total Value: ₱" . \number_format($context['total_value'], 2) . "\n";
                break;

            case 'borrow_requests':
                $text .= "- Total Requests: {$context['total_requests']}\n";
                $text .= "- Pending: {$context['pending_requests']}\n";
                $text .= "- Approved: {$context['approved_requests']}\n";
                $text .= "- Currently Borrowed: {$context['borrowed_items']}\n";
                $text .= "- Returned: {$context['returned_items']}\n";
                break;

            case 'users':
                $text .= "- Total Users: {$context['total_users']}\n";
                $text .= "- Active Users: {$context['active_users']}\n";
                $text .= "- Admins: {$context['admin_users']}\n";
                $text .= "- Employees: {$context['employee_users']}\n";
                break;

            default:
                $text .= "- Total Bookings: {$context['total_bookings']}\n";
                $text .= "- Total Inventory: {$context['total_inventory']}\n";
                $text .= "- Total Borrow Requests: {$context['total_borrow_requests']}\n";
                $text .= "- Total Users: {$context['total_users']}\n";
        }

        return $text;
    }

    

    /**
     * Get context data based on page type
     * @noinspection PhpUndefinedMethodInspection
     */
    private function getContext(string $pageType): array
    {
        $context = [];

        switch ($pageType) {
            case 'bookings':
                $context = [
                    'total_bookings' => Booking::query()->count('*'),
                    'pending_bookings' => Booking::query()->where('status', '=', 'pending')->count('*'),
                    'approved_bookings' => Booking::query()->where('status', '=', 'approved')->count('*'),
                    'completed_bookings' => Booking::query()->where('status', '=', 'completed')->count('*'),
                    'rejected_bookings' => Booking::query()->where('status', '=', 'rejected')->count('*'),
                    'recent_bookings' => Booking::query()->latest('created_at')->take(5)->get(),
                    'monthly_bookings' => (function () {
                        $results = Booking::query()
                            ->selectRaw("strftime('%m', preferred_date) as month, COUNT(*) as count", [])
                            ->whereRaw("strftime('%Y', preferred_date) = ?", [Carbon::now()->year])
                            ->groupBy('month')
                            ->orderBy('month')
                            ->get();

                        return $results->pluck('count', 'month')->toArray();
                    })(),
                ];
                break;

            case 'inventory':
                $context = [
                    'total_items' => Inventory::query()->count('*'),
                    'available_items' => Inventory::query()->where('status', '=', 'available')->count('*'),
                    'total_quantity' => (int) Inventory::query()->sum('quantity'),
                    'low_stock_items' => Inventory::query()->whereColumn('quantity', '<=', 'low_stock_threshold', 'and')->count('*'),
                    'total_value' => (float) Inventory::query()
                        ->selectRaw('COALESCE(SUM(quantity * unit_cost), 0) as total_value', [])
                        ->value('total_value'),
                    'categories' => (function () {
                        $results = Inventory::query()
                            ->select(['category_id'])
                            ->get()
                            ->groupBy('category_id')
                            ->map(function ($group) {
                                return ['count' => $group->count()];
                            });
                        return $results;
                    })(),
                    'recent_items' => Inventory::query()->latest('created_at')->take(5)->get(),
                    'out_of_stock' => Inventory::query()->where('quantity', '=', 0)->count('*'),
                ];
                break;

            case 'borrow_requests':
                $context = [
                    'total_requests' => BorrowRequest::query()->count('*'),
                    'pending_requests' => BorrowRequest::query()->where('status', '=', 'pending')->count('*'),
                    'approved_requests' => BorrowRequest::query()->where('status', '=', 'approved')->count('*'),
                    'borrowed_items' => BorrowRequest::query()->where('status', '=', 'borrowed')->count('*'),
                    'returned_items' => BorrowRequest::query()->where('status', '=', 'returned')->count('*'),
                    'rejected_requests' => BorrowRequest::query()->where('status', '=', 'rejected')->count('*'),
                    'recent_requests' => BorrowRequest::query()->latest('created_at')->take(5)->get(),
                ];
                break;

            case 'users':
                $context = [
                    'total_users' => User::query()->count('*'),
                    'active_users' => User::query()->where('is_active', '=', true)->count('*'),
                    'admin_users' => User::query()->role('admin')->count('*'),
                    'employee_users' => User::query()->role('employee')->count('*'),
                    'recent_users' => User::query()->latest('created_at')->take(5)->get(),
                    'users_today' => User::query()->whereDate('created_at', '=', today(), 'and')->count('*'),
                ];
                break;

            default:
                $context = [
                    'total_bookings' => Booking::query()->count('*'),
                    'total_inventory' => Inventory::query()->count('*'),
                    'total_borrow_requests' => BorrowRequest::query()->count('*'),
                    'total_users' => User::query()->count('*'),
                ];
        }

        return $context;
    }

    /**
     * Generate AI response based on message and context
     */
    private function generateResponse(string $message, array $context, string $pageType): string
    {
        // Check for specific keywords and generate appropriate responses
        if (isset($context['total_items']) && (Str::contains($message, 'inventory') || Str::contains($message, 'stock'))) {
            return $this->generateInventoryResponse($context);
        }

        if (isset($context['total_bookings']) && Str::contains($message, 'booking')) {
            return $this->generateBookingResponse($context);
        }

        if (isset($context['total_requests']) && (Str::contains($message, 'borrow') || Str::contains($message, 'request'))) {
            return $this->generateBorrowRequestResponse($context);
        }

        if (isset($context['total_users']) && (Str::contains($message, 'user') || Str::contains($message, 'employee'))) {
            return $this->generateUserResponse($context);
        }

        if (Str::contains($message, 'summary') || Str::contains($message, 'overview')) {
            return $this->generateSummary($context, $pageType);
        }

        if (Str::contains($message, 'export') || Str::contains($message, 'download')) {
            return $this->generateExportGuide($pageType);
        }

        if (Str::contains($message, 'filter') || Str::contains($message, 'search')) {
            return $this->generateFilterGuide($pageType);
        }

        if (Str::contains($message, 'help') || Str::contains($message, 'how')) {
            return $this->generateHelpResponse($pageType);
        }

        if (Str::contains($message, 'status') || Str::contains($message, 'current')) {
            return $this->generateStatusResponse($context, $pageType);
        }

        // Default response
        return $this->generateDefaultResponse($pageType);
    }

    /**
     * Generate summary response
     */
    private function generateSummary(array $context, string $pageType): string
    {
        switch ($pageType) {
            case 'bookings':
                return "**📊 Bookings Summary**\n\n" .
                       "• Total Bookings: {$context['total_bookings']}\n" .
                       "• Pending: {$context['pending_bookings']}\n" .
                       "• Approved: {$context['approved_bookings']}\n" .
                       "• Completed: {$context['completed_bookings']}\n" .
                       "• Rejected: {$context['rejected_bookings']}\n\n" .
                       "The booking system is performing well with a completion rate of " . 
                       \round(($context['completed_bookings'] / ($context['total_bookings'] > 0 ? $context['total_bookings'] : 1)) * 100, 1) . "%.";

            case 'inventory':
                return "**📦 Inventory Summary**\n\n" .
                       "• Total Items: {$context['total_items']}\n" .
                       "• Low Stock Alerts: {$context['low_stock_items']}\n" .
                       "• Out of Stock: {$context['out_of_stock']}\n" .
                       "• Total Value: ₱" . \number_format($context['total_value'], 2) . "\n\n" .
                       "Recommendation: Review low stock items and place orders soon.";

            case 'borrow_requests':
                return "**🔍 Borrow Requests Summary**\n\n" .
                       "• Total Requests: {$context['total_requests']}\n" .
                       "• Pending: {$context['pending_requests']}\n" .
                       "• Approved: {$context['approved_requests']}\n" .
                       "• Currently Borrowed: {$context['borrowed_items']}\n" .
                       "• Returned: {$context['returned_items']}\n\n" .
                       "Action needed: {$context['pending_requests']} requests await your review.";

            case 'users':
                return "**👥 Users Summary**\n\n" .
                       "• Total Users: {$context['total_users']}\n" .
                       "• Active Users: {$context['active_users']}\n" .
                       "• Admins: {$context['admin_users']}\n" .
                       "• Employees: {$context['employee_users']}\n" .
                       "• New Today: {$context['users_today']}\n\n" .
                       "User activity is healthy with " . \round(($context['active_users'] / ($context['total_users'] > 0 ? $context['total_users'] : 1)) * 100, 1) . "% active users.";

            default:
                return "**System Overview**\n\nI can provide summaries for bookings, inventory, borrow requests, and users. Please specify which report you'd like to know more about.";
        }
    }

    /**
     * Generate export guide
     */
    private function generateExportGuide(string $pageType): string
    {
        $guides = [
            'bookings' => "**📥 Export Bookings Report**\n\nTo export bookings:\n1. Navigate to Reports > Bookings\n2. Click the 'Export Bookings' button\n3. Choose your preferred format (PDF)\n4. The report will download automatically\n\nTip: You can filter by date range before exporting.",
            'inventory' => "**📥 Export Inventory Report**\n\nTo export inventory:\n1. Navigate to Reports > Inventory\n2. Click the 'Export Inventory' button\n3. The PDF report will download\n\nTip: Include low stock items in your export for reordering.",
            'borrow_requests' => "**📥 Export Borrow Requests**\n\nTo export borrow requests:\n1. Navigate to Reports > Borrow Requests\n2. Click the export button\n3. Select your preferred format\n4. Download the report",
            'users' => "**📥 Export Users Report**\n\nTo export user data:\n1. Navigate to Reports > Users\n2. Click the export button\n3. The PDF will download with all user statistics",
        ];

        return $guides[$pageType] ?? $guides['bookings'];
    }

    /**
     * Generate filter guide
     */
    private function generateFilterGuide(string $pageType): string
    {
        return "**🔍 Filtering & Searching**\n\n" .
               "You can filter and search records by:\n\n" .
               "• **Date Range**: Select start and end dates\n" .
               "• **Status**: Filter by pending, approved, completed, etc.\n" .
               "• **Search Bar**: Type keywords to find specific records\n" .
               "• **Categories**: Filter by category (for inventory)\n\n" .
               "Pro tip: Combine multiple filters for precise results.";
    }

    /**
     * Generate inventory response
     */
    private function generateInventoryResponse(array $context): string
    {
        if (empty($context) || !isset($context['total_items'])) {
            return "**📦 Inventory Status**\n\nTo view inventory details, please navigate to the Inventory Report page. I can provide detailed analysis there.";
        }

        if (isset($context['total_quantity'])) {
            $availableItems = $context['available_items'] ?? 0;
            $status = $context['low_stock_items'] > 0
                ? "Action Required: Review low stock items and place orders."
                : "Inventory levels are healthy.";

            return "**Inventory Summary**\n\n" .
                   "- **Total Items:** {$context['total_items']}\n" .
                   "- **Available:** {$availableItems}\n" .
                   "- **Total Quantity:** {$context['total_quantity']}\n" .
                   "- **Low Stock Alerts:** {$context['low_stock_items']}\n" .
                   "- **Out of Stock:** {$context['out_of_stock']}\n" .
                   ($context['total_value'] > 0 ? "- **Total Value:** PHP " . \number_format($context['total_value'], 2) . "\n" : "") .
                   "\n{$status}";
        }

        $availableItems = $context['available_items'] ?? (($context['total_items'] ?? 0) - ($context['low_stock_items'] ?? 0) - ($context['out_of_stock'] ?? 0));
        $status = $context['low_stock_items'] > 0 ? "⚠️ Action Required: Review low stock items and place orders." : "✅ Inventory levels are healthy.";

        return "**📦 Inventory Summary**\n\n" .
               "• **Total Items:** {$context['total_items']}\n" .
               "• **Available:** " . max(0, $availableItems) . "\n" .
               "• **Low Stock Alerts:** {$context['low_stock_items']}\n" .
               "• **Out of Stock:** {$context['out_of_stock']}\n" .
               ($context['total_value'] > 0 ? "• **Total Value:** ₱" . \number_format($context['total_value'], 2) . "\n" : "") .
               "\n{$status}";
    }

    /**
     * Generate booking response
     */
    private function generateBookingResponse(array $context): string
    {
        if (empty($context)) {
            return "**📊 Booking Status**\n\nTo view booking details, please navigate to the Bookings Report page. I can provide detailed analysis there.";
        }

        $completionRate = \round(($context['completed_bookings'] / ($context['total_bookings'] > 0 ? $context['total_bookings'] : 1)) * 100, 1);

        return "**📊 Booking Status**\n\n" .
               "• Total Bookings: {$context['total_bookings']}\n" .
               "• Pending: {$context['pending_bookings']}\n" .
               "• Completion Rate: {$completionRate}%\n\n" .
               ($context['pending_bookings'] > 0 ? "⚠️ You have {$context['pending_bookings']} pending bookings requiring attention." : "✅ All bookings are processed.");
    }

    /**
     * Generate borrow request response
     */
    private function generateBorrowRequestResponse(array $context): string
    {
        if (empty($context)) {
            return "**🔍 Borrow Requests Status**\n\nTo view borrow request details, please navigate to the Borrow Requests Report page.";
        }

        return "**🔍 Borrow Requests Status**\n\n" .
               "• Total Requests: {$context['total_requests']}\n" .
               "• Pending Review: {$context['pending_requests']}\n" .
               "• Currently Borrowed: {$context['borrowed_items']}\n" .
               "• Returned: {$context['returned_items']}\n\n" .
               ($context['pending_requests'] > 0 ? "⚠️ {$context['pending_requests']} requests need your approval." : "✅ All requests are processed.");
    }

    /**
     * Generate user response
     */
    private function generateUserResponse(array $context): string
    {
        if (empty($context)) {
            return "**👥 User Status**\n\nTo view user details, please navigate to the Users Report page.";
        }

        $activeRate = \round(($context['active_users'] / ($context['total_users'] > 0 ? $context['total_users'] : 1)) * 100, 1);

        return "**👥 User Status**\n\n" .
               "• Total Users: {$context['total_users']}\n" .
               "• Active Users: {$context['active_users']} ({$activeRate}%)\n" .
               "• Admins: {$context['admin_users']}\n" .
               "• Employees: {$context['employee_users']}\n\n" .
               "✅ User base is healthy and active.";
    }

    /**
     * Generate help response
     */
    private function generateHelpResponse(string $pageType): string
    {
        return "**❓ How Can I Help You?**\n\n" .
               "I can assist you with:\n\n" .
               "• **Summaries**: Get an overview of your data\n" .
               "• **Analytics**: Understand trends and patterns\n" .
               "• **Export Guide**: Learn how to download reports\n" .
               "• **Filtering**: Search and filter records\n" .
               "• **Status Checks**: Current system status\n" .
               "• **Step-by-step guidance**: Navigate the system\n\n" .
               "Just ask me anything about the " . ucfirst($pageType) . " report!";
    }

    /**
     * Generate status response
     */
    private function generateStatusResponse(array $context, string $pageType): string
    {
        return $this->generateSummary($context, $pageType);
    }

    /**
     * Generate default response
     */
    private function generateDefaultResponse(string $pageType): string
    {
        return "**I'm here to help!** 🤖\n\n" .
               "I can assist you with the " . ucfirst($pageType) . " report. Try asking me:\n\n" .
               "• \"Show me a summary\"\n" .
               "• \"What's the current status?\"\n" .
               "• \"How do I export this report?\"\n" .
               "• \"Help me filter the data\"\n" .
               "• \"Give me analytics\"\n\n" .
               "What would you like to know?";
    }

    /**
     * Generate greeting response
     */
    private function generateGreetingResponse(): string
    {
        return "Hello! I can help with bookings, inventory, borrow requests, users, and report exports. Ask for a summary or status of any report and I'll pull the right data.";
    }
}
