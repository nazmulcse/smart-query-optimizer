<?php

return [

    'enabled' => true,

    'slow_query_ms' => 200,

    // Enable or disable AI-powered optimization
    'ai_enabled' => env('SMART_OPTIMIZE_AI', false),
    'openai_api_key' => env('OPENAI_API_KEY'),
    'openai_model' => 'gpt-4.1-mini',

    'store_logs' => true,
    'log_path' => storage_path('logs/query-optimizer.log'),

     // Save results to log?
    'log_results' => true,
];
