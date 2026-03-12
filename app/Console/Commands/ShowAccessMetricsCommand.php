<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ShowAccessMetricsCommand extends Command
{
    protected $signature = 'metrics:access {--days=7}';

    protected $description = 'Show access metrics collected from web requests.';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $startDate = now()->subDays($days - 1)->toDateString();
        $totalSavedRepositories = DB::table('repository_analyses')->count();

        $this->info('Total repositories saved: '.$totalSavedRepositories);

        $totalsByDay = DB::table('access_metrics')
            ->selectRaw('metric_date, SUM(hits) AS total_hits')
            ->where('metric_date', '>=', $startDate)
            ->groupBy('metric_date')
            ->orderByDesc('metric_date')
            ->get();

        if ($totalsByDay->isEmpty()) {
            $this->warn('No access metrics found for the selected period.');

            return self::SUCCESS;
        }

        $this->info("Access totals for the last {$days} day(s):");
        $this->table(
            ['Date', 'Hits'],
            $totalsByDay->map(fn ($row) => [$row->metric_date, (int) $row->total_hits])->all()
        );

        $topRoutes = DB::table('access_metrics')
            ->selectRaw('method, path, route_name, SUM(hits) AS total_hits')
            ->where('metric_date', '>=', $startDate)
            ->groupBy('method', 'path', 'route_name')
            ->orderByDesc('total_hits')
            ->limit(15)
            ->get();

        $this->info('Top routes in the period:');
        $this->table(
            ['Method', 'Path', 'Route', 'Hits'],
            $topRoutes->map(fn ($row) => [
                $row->method,
                $row->path,
                $row->route_name ?? '(unnamed)',
                (int) $row->total_hits,
            ])->all()
        );

        $topIps = DB::table('access_metrics')
            ->selectRaw('ip_address, SUM(hits) AS total_hits')
            ->where('metric_date', '>=', $startDate)
            ->groupBy('ip_address')
            ->orderByDesc('total_hits')
            ->limit(15)
            ->get();

        $this->info('Top IPs in the period:');
        $this->table(
            ['IP', 'Hits'],
            $topIps->map(fn ($row) => [
                $row->ip_address,
                (int) $row->total_hits,
            ])->all()
        );

        return self::SUCCESS;
    }
}
