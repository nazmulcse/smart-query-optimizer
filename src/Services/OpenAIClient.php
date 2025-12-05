<?php

namespace NazmulHasan\SmartQueryOptimizer\Services;

use Illuminate\Support\Facades\Http;

class OpenAIClient
{
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('smart-optimize.openai_api_key');
        $this->model = config('smart-optimize.openai_model');
    }

    public function chat(string $prompt): string
    {
        if (!$this->apiKey) {
            return "OpenAI API key missing. Add OPENAI_API_KEY in .env";
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(30)
            ->post("https://api.openai.com/v1/chat/completions", [
                "model" => $this->model,
                "messages" => [
                    ["role" => "user", "content" => $prompt]
                ]
            ]);

        if ($response->failed()) {
            return "Failed to connect to OpenAI: " . $response->body();
        }

        return $response->json()['choices'][0]['message']['content'] ?? "No response";
    }
}
