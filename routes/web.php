<?php

use Illuminate\Support\Facades\Route;
use SmartQueryOptimizer\Http\Controllers\OptimizerController;

Route::get('optimizer/logs', [OptimizerController::class, 'logs']);
