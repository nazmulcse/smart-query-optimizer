<?php

namespace NazmulHasan\SmartQueryOptimizer\Services;

use OpenAI\Client as OpenAIClient;

class AIRecommender
{
    protected bool $aiEnabled;
    protected ?OpenAIClient $client;

    public function __construct(bool $aiEnabled, ?OpenAIClient $client = null)
    {
        $this->aiEnabled = $aiEnabled;
        $this->client = $client;
    }

    /**
     * Main handler
     */
     public function analyze(string $sql, array $stats = []): array
    {
        return $this->aiEnabled
            ? $this->aiAnalysis($sql, $stats)
            : $this->ruleBasedAnalysis($sql, $stats);
    }

    /**
     * -------------------------------------------------------------
     * 1. RULE-BASED ANALYSIS (NO AI)
     * -------------------------------------------------------------
     */
    protected function ruleBasedAnalysis(string $sql, array $stats): array
    {
        $suggestions = [];

        if (preg_match('/select\s+\*/i', $sql)) {
            $suggestions[] = "Avoid using SELECT * — specify explicit columns.";
        }

        if (stripos($sql, 'like "%') !== false) {
            $suggestions[] = "Using LIKE with a leading wildcard (%) prevents index use.";
        }

        if (preg_match('/order by .*rand\(/i', $sql)) {
            $suggestions[] = "Avoid ORDER BY RAND() — it's slow for large datasets.";
        }

        if (preg_match('/where\s+.*\s+or\s+.*/i', $sql)) {
            $suggestions[] = "OR conditions may cause full table scans — consider UNION or indexes.";
        }

        if (preg_match('/join/i', $sql) && !preg_match('/on/i', $sql)) {
            $suggestions[] = "JOIN without ON clause may create a Cartesian product.";
        }

        if (preg_match('/where\s+.*function/i', $sql)) {
            $suggestions[] = "Avoid using SQL functions on indexed columns in WHERE clause.";
        }

        // Add runtime statistics if available
        if (isset($stats['execution_ms']) && $stats['execution_ms'] > 500) {
            $suggestions[] = "Query took {$stats['execution_ms']} ms — consider indexing.";
        }

        if (empty($suggestions)) {
            $suggestions[] = "Query looks OK. No major bottlenecks detected.";
        }

        return [
            'mode' => 'rule_based',
            'suggestions' => $suggestions,
        ];
    }

    /**
     * -------------------------------------------------------------
     * 2. AI POWERED ANALYSIS (OpenAI or any other LLM)
     * -------------------------------------------------------------
     */
    protected function aiAnalysis(string $sql, array $stats): array
    {
        if (! $this->client) {
            return [
                "mode" => "ai",
                "error" => "AI is enabled but OpenAI client is not configured.",
            ];
        }

        $prompt = $this->buildPrompt($sql, $stats);

        $response = $this->client->chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'You are an expert SQL performance tuner.'],
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        $content = trim($response->choices[0]->message->content ?? '');

        return [
            'mode' => 'ai',
            'suggestions' => explode("\n", $content),
        ];
    }

    /**
     * Build prompt for AI.
     */
    protected function buildPrompt(string $sql, array $stats): string
    {
        $statsText = json_encode($stats, JSON_PRETTY_PRINT);

        return <<<PROMPT
        Analyze the following SQL query and give a list of optimization recommendations.

        SQL:
        {$sql}

        Runtime Stats:
        {$statsText}

        Output format:
        - Bullet points
        - Short and actionable
        - No extra explanation
        PROMPT;
    }
}
