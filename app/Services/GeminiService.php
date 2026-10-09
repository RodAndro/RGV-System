<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    protected ?string $apiKey;

    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';
    }

    public function generateText(string $prompt, string $model = 'gemini-2.0-flash', ?string $systemPrompt = null): array
    {
        if (! $this->apiKey) {
            return ['success' => false, 'error' => 'Gemini is not configured.'];
        }

        $url = "{$this->baseUrl}/{$model}:generateContent?key={$this->apiKey}";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt,
                        ],
                    ],
                ],
            ],
        ];
        if ($systemPrompt) {
            $payload['systemInstruction'] = ['parts' => [['text' => $systemPrompt]]];
        }

        try {
            $response = Http::timeout((int) config('services.gemini.timeout', 30))->post($url, $payload);

            if ($response->successful()) {
                $text = $response->json('candidates.0.content.parts.0.text');
                if (! $text) {
                    $text = $response->body();
                }

                return ['success' => true, 'text' => $text, 'raw' => $response->json()];
            }

            return ['success' => false, 'status' => $response->status(), 'body' => $response->body()];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

}
