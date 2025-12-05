<?php

namespace NazmulHasan\SmartQueryOptimizer\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use NazmulHasan\SmartQueryOptimizer\Helpers\OptimizerLog;
use NazmulHasan\SmartQueryOptimizer\Services\OpenAIClient;
use NazmulHasan\SmartQueryOptimizer\Services\AIRecommender;

class QueryExecutedListener
{
    public function handle(QueryExecuted $event)
    {
        $sql = $event->sql;
        $time = $event->time;

        $recommender = new AIRecommender(
            config('smart-optimize.ai_enabled'),
            app()->make(OpenAIClient::class)
        );

        $result = $recommender->analyze($sql, [
            'execution_ms' => $time
        ]);

        if (config('smart-optimize.log_results')) {
            OptimizerLog::save($sql, $result);
        }
    }
}
