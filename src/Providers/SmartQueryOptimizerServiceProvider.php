<?php

namespace NazmulHasan\SmartQueryOptimizer\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Events\QueryExecuted;
use NazmulHasan\SmartQueryOptimizer\Services\OpenAIClient;
use NazmulHasan\SmartQueryOptimizer\Services\AIRecommender;
use NazmulHasan\SmartQueryOptimizer\Services\QueryAnalyzer;
use NazmulHasan\SmartQueryOptimizer\Services\QueryCollector;
use NazmulHasan\SmartQueryOptimizer\Listeners\QueryExecutedListener;
use NazmulHasan\SmartQueryOptimizer\Console\Commands\AnalyzeAllQueries;
use NazmulHasan\SmartQueryOptimizer\Console\Commands\AnalyzeQueriesCommand;

class SmartQueryOptimizerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/query-optimizer.php', 'smart-optimize');

        $this->app->singleton('query-collector', fn() => new QueryCollector());
        $this->app->singleton('query-analyzer', fn() => new QueryAnalyzer());
        // $this->app->singleton('ai-recommender', fn() => new AIRecommender());
        
        $this->app->singleton('smart-query-optimizer', function () {
            return new AIRecommender(
                config('smart-optimize.ai_enabled'),
                app()->make(OpenAIClient::class)
            );
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/query-optimizer.php' => config_path('smart-optimize.php'),
        ], 'smart-optimize');

        if ($this->app->runningInConsole()) {
            $this->commands([
                AnalyzeQueriesCommand::class,
                AnalyzeAllQueries::class,
            ]);
        }

        \Event::listen(QueryExecuted::class, QueryExecutedListener::class);
    }
}
