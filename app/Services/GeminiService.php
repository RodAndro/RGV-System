<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    protected ?string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
        $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';
    }

    public function generateText(string $prompt, string $model = 'gemini-2.0-flash'): array
    {
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

        try {
            $response = Http::post($url, $payload);

            if ($response->successful()) {
                $text = $response->json('candidates.0.content.parts.0.text');
                if (!$text) {
                    $text = $response->body();
                }
                return ['success' => true, 'text' => $text, 'raw' => $response->json()];
            }

            return ['success' => false, 'status' => $response->status(), 'body' => $response->body()];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send a pre-built conversation payload to Gemini and return parsed result
     *
     * @param array $conversation
     * @param string $model
     * @return array
     */
    public function generateFromConversation(array $conversation, string $model = 'gemini-pro') : array
    {
        $url = "{$this->baseUrl}/{$model}:generateContent?key={$this->apiKey}";

        $payload = [
            'contents' => $conversation,
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 1024,
            ],
        ];

        try {
            $response = Http::post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if ($text) {
                    return ['success' => true, 'text' => $text, 'raw' => $data];
                }
                return ['success' => false, 'error' => 'No text candidate', 'raw' => $data];
            }

            return ['success' => false, 'status' => $response->status(), 'body' => $response->body()];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
