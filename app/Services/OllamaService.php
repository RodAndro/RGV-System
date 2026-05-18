<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaService
{
    protected string $baseUrl;
    protected string $model;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.ollama.base_url', 'http://127.0.0.1:11434'), '/');
        $this->model = (string) config('services.ollama.model', 'llama3.1');
        $this->timeout = (int) config('services.ollama.timeout', 60);
    }

    public function generateText(string $prompt, ?string $model = null, ?string $systemPrompt = null): array
    {
        $messages = [];

        if ($systemPrompt) {
            $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        }

        $messages[] = ['role' => 'user', 'content' => $prompt];

        return $this->generateFromConversation($messages, $model);
    }

    public function generateFromConversation(array $messages, ?string $model = null): array
    {
        try {
            $modelToUse = $model ?: $this->model;
            
            $response = Http::timeout($this->timeout)->post("{$this->baseUrl}/api/chat", [
                'model' => $modelToUse,
                'messages' => $messages,
                'stream' => false,
                'options' => [
                    'temperature' => (float) config('services.ollama.temperature', 0.5),
                    'num_predict' => 500,
                    'top_k' => 40,
                    'top_p' => 0.9,
                ],
            ]);

            if ($response->successful()) {
                $text = $response->json('message.content');

                if ($text) {
                    return ['success' => true, 'text' => trim($text), 'raw' => $response->json()];
                }

                Log::warning('Ollama returned no message content', ['response' => $response->json()]);
                return ['success' => false, 'error' => 'Ollama returned no message content', 'raw' => $response->json()];
            }

            Log::warning('Ollama API error', ['status' => $response->status(), 'body' => $response->body()]);
            return [
                'success' => false,
                'status' => $response->status(),
                'body' => $response->body(),
                'error' => "Ollama API returned status {$response->status()}"
            ];
        } catch (\Throwable $e) {
            Log::error('Ollama service error: ' . $e->getMessage(), ['exception' => get_class($e)]);
            return ['success' => false, 'error' => "Ollama service unavailable: {$e->getMessage()}"];
        }
    }

    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/api/tags");
            return $response->successful();
        } catch (\Throwable $e) {
            Log::debug('Ollama availability check failed: ' . $e->getMessage());
            return false;
        }
    }
}
