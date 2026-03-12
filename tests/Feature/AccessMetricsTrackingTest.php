<?php

namespace Tests\Feature;

use App\Models\RepositoryAnalysis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AccessMetricsTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function testTracksAccessCountPerRouteAndMethod(): void
    {
        $ip = '203.0.113.10';

        $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->get(route('repository-analyses.index'))
            ->assertOk();
        $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->get(route('repository-analyses.index'))
            ->assertOk();

        $metric = DB::table('access_metrics')
            ->where('metric_date', now()->toDateString())
            ->where('method', 'GET')
            ->where('path', '/')
            ->where('route_name', 'repository-analyses.index')
            ->where('ip_address', $ip)
            ->first();

        $this->assertNotNull($metric);
        $this->assertSame(2, (int) $metric->hits);
    }

    public function testMetricsCommandWarnsWhenNoDataExists(): void
    {
        $this->artisan('metrics:access --days=1')
            ->expectsOutput('Total repositories saved: 0')
            ->expectsOutput('No access metrics found for the selected period.')
            ->assertExitCode(0);
    }

    public function testMetricsCommandDisplaysTotalsAndTopRoutes(): void
    {
        RepositoryAnalysis::create([
            'repository_url' => 'https://github.com/owner/repo',
            'repository_name' => 'repo',
            'owner' => 'owner',
            'slug' => 'owner-repo',
            'metrics' => [],
        ]);

        $primaryIp = '203.0.113.20';
        $secondaryIp = '198.51.100.30';

        $this->withServerVariables(['REMOTE_ADDR' => $primaryIp])
            ->get(route('repository-analyses.index'))
            ->assertOk();
        $this->withServerVariables(['REMOTE_ADDR' => $primaryIp])
            ->get(route('repository-analyses.index'))
            ->assertOk();
        $this->withServerVariables(['REMOTE_ADDR' => $secondaryIp])
            ->get(route('repository-analyses.genome'))
            ->assertOk();

        Artisan::call('metrics:access', ['--days' => 1]);

        $output = Artisan::output();

        $this->assertStringContainsString('Total repositories saved: 1', $output);
        $this->assertStringContainsString('Access totals for the last 1 day(s):', $output);
        $this->assertStringContainsString('Top routes in the period:', $output);
        $this->assertStringContainsString('Top IPs in the period:', $output);
        $this->assertStringContainsString('repository-analyses.index', $output);
        $this->assertStringContainsString('repository-analyses.genome', $output);
        $this->assertStringContainsString($primaryIp, $output);
        $this->assertStringContainsString($secondaryIp, $output);
    }
}
