<?php

namespace NazmulHasan\SmartQueryOptimizer\Services;

use Illuminate\Support\Facades\DB;

class QueryCollector
{
    protected array $queries = [];

    public function start()
    {
        DB::listen(function ($query) {
            $this->queries[] = [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
                'connection' => $query->connectionName,
            ];
        });
    }

    public function getQueries(): array
    {
        return $this->queries;
    }
}
