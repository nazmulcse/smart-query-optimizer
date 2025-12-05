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
        $analysis = app('query-analyzer')->analyze($queries);
        $ai = app('ai-recommender')->recommend($analysis);

        $this->info("Query Analysis:");
        $this->line(json_encode($analysis, JSON_PRETTY_PRINT));

        $this->info("\nAI Recommendations:");
        $this->line(json_encode($ai, JSON_PRETTY_PRINT));
    }
}
