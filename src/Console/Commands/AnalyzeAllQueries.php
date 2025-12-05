<?php

namespace NazmulHasan\SmartQueryOptimizer\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use NazmulHasan\SmartQueryOptimizer\Services\OpenAIClient;
use NazmulHasan\SmartQueryOptimizer\Services\AIRecommender;

class AnalyzeAllQueries extends Command
{
    protected $signature = 'smart-optimize:analyze-all 
                            {--minutes=1 : Monitor queries for N minutes before analyzing}';

    protected $description = 'Automatically analyze all executed queries over a period of time';

    protected array $queries = [];

    public function handle()
    {
        $minutes = (int) $this->option('minutes');
        $this->info("🕒 Listening for queries for {$minutes} minute(s)...");

        // Listen to all queries
        DB::listen(function ($query) {
            $this->queries[] = [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time
            ];
        });

        // Sleep for the monitoring period
        sleep($minutes * 60);

        if (empty($this->queries)) {
            $this->info("No queries captured.");
            return 0;
        }

        $this->info("✅ Captured " . count($this->queries) . " queries. Analyzing...");

        // Instantiate AIRecommender
        $recommender = new AIRecommender(
            config('smart-optimize.ai_enabled'),
            app()->make(OpenAIClient::class)
        );

        foreach ($this->queries as $idx => $q) {
            $this->line("\n🔹 Query #" . ($idx + 1));
            $this->line($q['sql']);

            $result = $recommender->analyze($q['sql'], [
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

        return 0;
    }
}
