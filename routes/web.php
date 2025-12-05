<?php

use Illuminate\Support\Facades\Route;
use NazmulHasan\SmartQueryOptimizer\Http\Controllers\OptimizerController;

Route::get('optimizer/logs', [OptimizerController::class, 'logs']);
