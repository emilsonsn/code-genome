<?php

namespace App\Infrastructure\Metrics;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class AccessMetricsRecorder
{
    public function record(string $method, string $path, ?string $routeName, ?string $ipAddress): void
    {
        $date = now()->toDateString();
        $normalizedRouteName = $routeName ?: null;
        $normalizedIpAddress = $ipAddress ?: 'unknown';
        $now = now();

        try {
            $updatedRows = DB::table('access_metrics')
                ->where('metric_date', $date)
                ->where('method', $method)
                ->where('path', $path)
                ->where('route_name', $normalizedRouteName)
                ->where('ip_address', $normalizedIpAddress)
                ->increment('hits');

            if ($updatedRows > 0) {
                return;
            }

            try {
                DB::table('access_metrics')->insert([
                    'metric_date' => $date,
                    'route_name' => $normalizedRouteName,
                    'path' => $path,
                    'method' => $method,
                    'ip_address' => $normalizedIpAddress,
                    'hits' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } catch (QueryException $exception) {
                if ($this->isMissingTableException($exception)) {
                    return;
                }

                DB::table('access_metrics')
                    ->where('metric_date', $date)
                    ->where('method', $method)
                    ->where('path', $path)
                    ->where('route_name', $normalizedRouteName)
                    ->where('ip_address', $normalizedIpAddress)
                    ->increment('hits');
            }
        } catch (QueryException $exception) {
            if ($this->isMissingTableException($exception)) {
                return;
            }

            throw $exception;
        }
    }

    private function isMissingTableException(QueryException $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return str_contains($message, 'no such table')
            || str_contains($message, 'doesn\'t exist')
            || str_contains($message, 'sqlstate[42s02]');
    }
}
