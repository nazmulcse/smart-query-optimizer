<?php

namespace NazmulHasan\SmartQueryOptimizer\Services;

class QueryAnalyzer
{
    public function analyze(array $queries): array
    {
        $results = [];

        foreach ($queries as $query) {
            $item = [
                'sql' => $query['sql'],
                'issues' => [],
            ];

            // detect N+1
            if (substr_count($query['sql'], 'select') > 1) {
                $item['issues'][] = 'Possible N+1 query detected.';
            }

            // detect missing index warnings (very basic)
            if (preg_match('/where `(.*)` = \?/', $query['sql'])) {
                $item['issues'][] = 'Check index on WHERE column.';
            }

            // slow query
            if ($query['time'] > config('query-optimizer.slow_query_ms')) {
                $item['issues'][] = 'Slow query detected: '.$query['time'].'ms';
            }

            $results[] = $item;
        }

        return $results;
    }
}
