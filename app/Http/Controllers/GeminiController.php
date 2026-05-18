<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GeminiService;

class GeminiController extends Controller
{
    protected GeminiService $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    public function ask(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string',
        ]);

        $prompt = $request->input('prompt');

        $result = $this->gemini->generateText($prompt);

        if (isset($result['success']) && $result['success']) {
            return response()->json(['text' => $result['text']]);
        }

        return response()->json(['error' => 'AI request failed', 'details' => $result], 500);
    }
}
