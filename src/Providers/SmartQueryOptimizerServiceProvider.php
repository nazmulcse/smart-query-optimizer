<?php

namespace NazmulHasan\SmartQueryOptimizer\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Events\QueryExecuted;
use SmartQueryOptimizer\Listeners\QueryExecutedListener;

class SmartQueryOptimizerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/query-optimizer.php', 'smart-optimize');

        $this->app->singleton('smart-query-optimizer', function () {
            return new \SmartQueryOptimizer\Services\AIRecommender(
                config('smart-optimize.ai_enabled'),
                app()->make(\OpenAI\Client::class)
            );
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/smart-optimize.php' => config_path('smart-optimize.php'),
        ]);

        \Event::listen(QueryExecuted::class, QueryExecutedListener::class);
    }
}
