<?php

namespace Tests\Feature;

use App\Clients\GitHub\GitHubRepositoryClient;
use App\Infrastructure\Git\GitRepositoryCloner;
use App\Infrastructure\Metrics\RepositoryMetricsCollector;
use App\Infrastructure\Python\PythonRepositoryAnalyzer;
use App\Infrastructure\Score\RepositoryScoreCalculator;
use App\Models\RepositoryAnalysis;
use App\Repositories\RepositoryAnalysisRepository;
use App\Services\RepositoryAnalyzerService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class RepositoryAnalyzerServiceTest extends TestCase
{
    use RefreshDatabase;

    private RepositoryAnalysisRepository $repository;
    private RepositoryAnalyzerService $service;
    private $cloner;
    private $metricsCollector;
    private $scoreCalculator;
    private $github;
    private $pythonAnalyzer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new RepositoryAnalysisRepository();

        $this->cloner = Mockery::mock(GitRepositoryCloner::class);
        $this->metricsCollector = Mockery::mock(RepositoryMetricsCollector::class);
        $this->scoreCalculator = Mockery::mock(RepositoryScoreCalculator::class);
        $this->github = Mockery::mock(GitHubRepositoryClient::class);
        $this->pythonAnalyzer = Mockery::mock(PythonRepositoryAnalyzer::class);

        $this->service = new RepositoryAnalyzerService(
            $this->repository,
            $this->cloner,
            $this->metricsCollector,
            $this->scoreCalculator,
            $this->github,
            $this->pythonAnalyzer
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testAnalyzeReturnsCachedResultWhenFresh(): void
    {
        $analysis = RepositoryAnalysis::create([
            'repository_url' => 'https://github.com/owner/repo',
            'repository_name' => 'repo',
            'owner' => 'owner',
            'slug' => 'owner-repo',
            'metrics' => ['cached' => 'data'],
        ]);

        // Mocks should NOT be called when returning cached result
        $this->cloner->shouldNotReceive('clone');
        $this->metricsCollector->shouldNotReceive('collect');
        $this->pythonAnalyzer->shouldNotReceive('analyze');

        $result = $this->service
            ->setRepositoryUrl('https://github.com/owner/repo')
            ->analyze()
            ->object();

        $this->assertEquals($analysis->id, $result->id);
        $this->assertEquals(['cached' => 'data'], $result->metrics);
    }

    public function testAnalyzeReanalyzesWhenStale(): void
    {
        $analysis = RepositoryAnalysis::create([
            'repository_url' => 'https://github.com/owner/repo',
            'repository_name' => 'repo',
            'owner' => 'owner',
            'slug' => 'owner-repo',
            'metrics' => ['old' => 'data'],
        ]);

        // Set updated_at to 25 hours ago (stale)
        $analysis->updated_at = Carbon::now()->subHours(25);
        $analysis->save();

        $this->setupMocksForAnalysis();

        $result = $this->service
            ->setRepositoryUrl('https://github.com/owner/repo')
            ->analyze()
            ->object();

        // Should be the same record (updated, not new)
        $this->assertEquals($analysis->id, $result->id);
        // Should have new metrics
        $this->assertArrayHasKey('new_metric', $result->metrics);
        $this->assertEquals('value', $result->metrics['new_metric']);
    }

    public function testAnalyzeCreatesNewRecordWhenNotExists(): void
    {
        $this->setupMocksForAnalysis();

        $result = $this->service
            ->setRepositoryUrl('https://github.com/newowner/newrepo')
            ->analyze()
            ->object();

        $this->assertNotNull($result);
        $this->assertEquals('https://github.com/newowner/newrepo', $result->repository_url);
        $this->assertEquals('newrepo', $result->repository_name);
        $this->assertEquals('newowner', $result->owner);
    }

    public function testAnalyzeDoesNotReanalyzeWhenLessThan24Hours(): void
    {
        $analysis = RepositoryAnalysis::create([
            'repository_url' => 'https://github.com/owner/repo',
            'repository_name' => 'repo',
            'owner' => 'owner',
            'slug' => 'owner-repo',
            'metrics' => ['fresh' => 'data'],
        ]);

        // Set updated_at to 23 hours ago (still fresh)
        $analysis->updated_at = Carbon::now()->subHours(23);
        $analysis->save();

        // Mocks should NOT be called
        $this->cloner->shouldNotReceive('clone');
        $this->metricsCollector->shouldNotReceive('collect');

        $result = $this->service
            ->setRepositoryUrl('https://github.com/owner/repo')
            ->analyze()
            ->object();

        $this->assertEquals(['fresh' => 'data'], $result->metrics);
    }

    public function testAnalyzeUpdatesTimestampWhenReanalyzing(): void
    {
        $analysis = RepositoryAnalysis::create([
            'repository_url' => 'https://github.com/owner/repo',
            'repository_name' => 'repo',
            'owner' => 'owner',
            'slug' => 'owner-repo',
            'metrics' => ['old' => 'data'],
        ]);

        $oldTime = Carbon::now()->subHours(25);
        $analysis->updated_at = $oldTime;
        $analysis->save();

        $this->setupMocksForAnalysis();

        $result = $this->service
            ->setRepositoryUrl('https://github.com/owner/repo')
            ->analyze()
            ->object();

        $this->assertTrue($result->updated_at->greaterThan($oldTime));
    }

    private function setupMocksForAnalysis(): void
    {
        $this->cloner
            ->shouldReceive('clone')
            ->once()
            ->andReturn('/tmp/repo');

        $this->metricsCollector
            ->shouldReceive('collect')
            ->once()
            ->with('/tmp/repo')
            ->andReturn(['new_metric' => 'value']);

        $this->github
            ->shouldReceive('getRepository')
            ->once()
            ->andReturn([
                'stargazers_count' => 100,
                'forks_count' => 50,
                'open_issues_count' => 10,
                'watchers_count' => 100,
                'default_branch' => 'main',
            ]);

        $this->github
            ->shouldReceive('getLanguages')
            ->once()
            ->andReturn(['PHP' => 1000, 'JavaScript' => 500]);

        $this->github
            ->shouldReceive('getContributors')
            ->once()
            ->andReturn([['login' => 'user1'], ['login' => 'user2']]);

        $this->scoreCalculator
            ->shouldReceive('calculate')
            ->once()
            ->andReturn(['overall' => 85]);

        $this->pythonAnalyzer
            ->shouldReceive('analyze')
            ->once()
            ->with('/tmp/repo')
            ->andReturn(['complexity' => 5]);
    }
}
