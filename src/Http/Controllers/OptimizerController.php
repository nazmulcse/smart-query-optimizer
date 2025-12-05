<?php

namespace NazmulHasan\SmartQueryOptimizer\Http\Controllers;

use Illuminate\Routing\Controller;

class OptimizerController extends Controller
{
    public function logs()
    {
        return response()->file(storage_path('logs/laravel.log'));
    }
}
