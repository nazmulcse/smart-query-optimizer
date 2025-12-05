<?php

namespace NazmulHasan\SmartQueryOptimizer\Helpers;

class OptimizerLog
{
    public static function save(string $sql, array $result)
    {
        logger()->channel('daily')
            ->info("SmartQueryOptimizer", [
                'sql' => $sql,
                'result' => $result
            ]);
    }
}
