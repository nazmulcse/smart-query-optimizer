<?php

namespace NazmulHasan\SmartQueryOptimizer\Console\Commands;

use Illuminate\Console\Command;

class AnalyzeQueriesCommand extends Command
{
    protected $signature = 'optimizer:analyze';
    protected $description = 'Analyze database queries and show optimization suggestions';

    public function handle()
    {
        $queries = app('query-collector')->getQueries();
        if (empty($queries)) {
            $this->info("No queries captured.");
            return 0;
        }

        $this->info("✅ Captured " . count($queries) . " queries. Analyzing...");

        $analysis = app('query-analyzer')->analyze($queries);
        $aiRecommender = app('smart-query-optimizer');

        $this->info("Query Analysis:");
        $this->line(json_encode($analysis, JSON_PRETTY_PRINT));

        $this->info("\nAI Recommendations:");
        // Instantiate AIRecommender

        foreach ($queries as $idx => $q) {
            $this->line("\n🔹 Query #" . ($idx + 1));
            $this->line($q['sql']);

            $result = $aiRecommender->analyze($q['sql'], [
                'execution_ms' => $q['time'],
            ]);

            $this->info("Mode: " . ucfirst($result['mode']));
            $this->info("Suggestions:");

            foreach ($result['suggestions'] as $suggestion) {
                if (trim($suggestion)) {
                    $this->line(" - " . $suggestion);
                }
            }
        }

        $this->info("\n✨ Analysis complete.");
    }
}
