<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\OllamaService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChatbotTest extends TestCase
{
    use RefreshDatabase;

    public function test_chatbot_returns_ollama_response()
    {
        $mock = new class extends OllamaService {
            public function __construct() {}
            public function generateFromConversation(array $messages, ?string $model = null): array
            {
                return ['success' => true, 'text' => 'Mocked AI response'];
            }
        };

        $this->instance(OllamaService::class, $mock);

        $response = $this->postJson('/api/chatbot/query', [
            'message' => 'Give me a summary',
            'pageType' => 'bookings',
            'history' => []
        ]);

        $response->assertStatus(200)->assertJson([
            'success' => true,
            'response' => 'Mocked AI response'
        ]);
    }
}
