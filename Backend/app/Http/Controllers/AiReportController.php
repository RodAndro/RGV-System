<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\V1\AiInsightController;
use Illuminate\Http\Request;

/**
 * Session-authenticated adapter over the versioned AI endpoints.
 *
 * The admin portal and the API share one implementation so the traceability
 * record and fallback behaviour cannot diverge. The portal keeps its original
 * top-level response keys for backward compatibility; the API returns the
 * richer `data` envelope.
 */
class AiReportController extends Controller
{
    public function __construct(private readonly AiInsightController $insights) {}

    public function generateInsights(Request $request)
    {
        return $this->legacy($this->insights->insights($request), function (array $data) {
            $inputs = $data['inputs'];

            return [
                'data' => $inputs,
                'insights' => $data['generated_text'],
            ];
        });
    }

    public function generateBookingForecast(Request $request)
    {
        return $this->legacy($this->insights->forecast($request), function (array $data) {
            return [
                'historical_data' => $data['inputs']['monthly_bookings'],
                'forecast' => $data['generated_text'],
            ];
        });
    }

    public function generateInventoryRecommendations(Request $request)
    {
        return $this->legacy($this->insights->inventoryRecommendations($request), function (array $data) {
            return [
                'inventory_data' => $data['inputs']['items'],
                'recommendations' => $data['generated_text'],
            ];
        });
    }

    /**
     * Reshape a v1 payload into the portal's original response keys.
     *
     * @param  callable(array<string, mixed>): array<string, mixed>  $map
     */
    private function legacy($response, callable $map)
    {
        $payload = json_decode($response->getContent(), true);
        $data = $payload['data'] ?? null;

        if (! is_array($data)) {
            return $response;
        }

        return response()->json($map($data));
    }
}
