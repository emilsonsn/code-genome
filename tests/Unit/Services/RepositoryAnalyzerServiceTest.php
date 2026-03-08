<?php

namespace Tests\Unit\Services;

use App\Clients\GitHub\GitHubRepositoryClient;
use App\Infrastructure\Git\GitRepositoryCloner;
use App\Infrastructure\Metrics\RepositoryMetricsCollector;
use App\Infrastructure\Python\PythonRepositoryAnalyzer;
use App\Infrastructure\Score\RepositoryScoreCalculator;
use App\Models\RepositoryAnalysis;
use App\Repositories\RepositoryAnalysisRepository;
use App\Services\RepositoryAnalyzerService;
use Illuminate\Support\Facades\File;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class RepositoryAnalyzerServiceTest extends TestCase
{
    private MockInterface $repository;

    private MockInterface $cloner;

    private MockInterface $metricsCollector;

    private MockInterface $scoreCalculator;

    private MockInterface $github;

    private MockInterface $pythonAnalyzer;

    private RepositoryAnalyzerService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(RepositoryAnalysisRepository::class);
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

    public function test_returns_existing_analysis_when_found(): void
    {
        $existingAnalysis = new RepositoryAnalysis([
            'repository_url' => 'https://github.com/owner/repo',
            'slug' => 'owner-repo',
        ]);

        $this->repository
            ->shouldReceive('findByUrl')
            ->with('https://github.com/owner/repo')
            ->andReturn($existingAnalysis);

        $this->repository
            ->shouldReceive('isStale')
            ->with($existingAnalysis)
            ->andReturn(false);

        $result = $this->service
            ->setRepositoryUrl('https://github.com/owner/repo')
            ->analyze()
            ->object();

        $this->assertSame($existingAnalysis, $result);
    }

    public function test_creates_new_analysis_when_not_found(): void
    {
        $url = 'https://github.com/owner/repo';
        $path = '/tmp/repo-clone';

        $this->repository
            ->shouldReceive('findByUrl')
            ->with($url)
            ->andReturn(null);

        $this->repository
            ->shouldReceive('extractRepoInfo')
            ->with($url)
            ->andReturn(['owner' => 'owner', 'repository_name' => 'repo']);

        $this->cloner
            ->shouldReceive('clone')
            ->with($url)
            ->andReturn($path);

        $this->metricsCollector
            ->shouldReceive('collect')
            ->with($path)
            ->andReturn(['total_files' => 100]);

        $this->github
            ->shouldReceive('getRepository')
            ->andReturn(['stargazers_count' => 50, 'forks_count' => 10]);

        $this->github
            ->shouldReceive('getLanguages')
            ->andReturn(['PHP' => 5000]);

        $this->github
            ->shouldReceive('getContributors')
            ->andReturn([['login' => 'user1']]);

        $this->scoreCalculator
            ->shouldReceive('calculate')
            ->andReturn(['overall' => 85]);

        $this->pythonAnalyzer
            ->shouldReceive('analyze')
            ->with($path)
            ->andReturn(['total_loc' => 1000]);

        $newAnalysis = new RepositoryAnalysis(['slug' => 'owner-repo']);
        $this->repository
            ->shouldReceive('create')
            ->andReturn($newAnalysis);

        File::shouldReceive('deleteDirectory')
            ->with($path)
            ->once();

        $result = $this->service
            ->setRepositoryUrl($url)
            ->analyze()
            ->object();

        $this->assertSame($newAnalysis, $result);
    }

    public function test_clones_repository_when_not_cached(): void
    {
        $url = 'https://github.com/owner/repo';

        $this->repository->shouldReceive('findByUrl')->andReturn(null);
        $this->repository->shouldReceive('extractRepoInfo')->andReturn([
            'owner' => 'owner',
            'repository_name' => 'repo',
        ]);
        $this->repository->shouldReceive('create')->andReturn(new RepositoryAnalysis);

        $this->cloner
            ->shouldReceive('clone')
            ->with($url)
            ->once()
            ->andReturn('/tmp/clone');

        $this->metricsCollector->shouldReceive('collect')->andReturn([]);
        $this->github->shouldReceive('getRepository')->andReturn([]);
        $this->github->shouldReceive('getLanguages')->andReturn([]);
        $this->github->shouldReceive('getContributors')->andReturn([]);
        $this->scoreCalculator->shouldReceive('calculate')->andReturn([]);
        $this->pythonAnalyzer->shouldReceive('analyze')->andReturn([]);

        File::shouldReceive('deleteDirectory');

        $result = $this->service->setRepositoryUrl($url)->analyze();

        $this->assertInstanceOf(RepositoryAnalyzerService::class, $result);
    }

    public function test_fetches_github_data(): void
    {
        $url = 'https://github.com/owner/repo';

        $this->repository->shouldReceive('findByUrl')->andReturn(null);
        $this->repository->shouldReceive('extractRepoInfo')->andReturn([
            'owner' => 'owner',
            'repository_name' => 'repo',
        ]);
        $this->repository->shouldReceive('create')->andReturn(new RepositoryAnalysis);

        $this->cloner->shouldReceive('clone')->andReturn('/tmp/repo');
        $this->metricsCollector->shouldReceive('collect')->andReturn([]);
        $this->scoreCalculator->shouldReceive('calculate')->andReturn([]);
        $this->pythonAnalyzer->shouldReceive('analyze')->andReturn([]);

        $this->github
            ->shouldReceive('getRepository')
            ->with('owner', 'repo')
            ->once()
            ->andReturn([]);

        $this->github
            ->shouldReceive('getLanguages')
            ->with('owner', 'repo')
            ->once()
            ->andReturn([]);

        $this->github
            ->shouldReceive('getContributors')
            ->with('owner', 'repo')
            ->once()
            ->andReturn([]);

        File::shouldReceive('deleteDirectory');

        $result = $this->service->setRepositoryUrl($url)->analyze();

        $this->assertInstanceOf(RepositoryAnalyzerService::class, $result);
    }

    public function test_calculates_scores(): void
    {
        $url = 'https://github.com/owner/repo';

        $this->repository->shouldReceive('findByUrl')->andReturn(null);
        $this->repository->shouldReceive('extractRepoInfo')->andReturn([
            'owner' => 'owner',
            'repository_name' => 'repo',
        ]);
        $this->repository->shouldReceive('create')->andReturn(new RepositoryAnalysis);

        $this->cloner->shouldReceive('clone')->andReturn('/tmp/repo');
        $this->metricsCollector->shouldReceive('collect')->andReturn(['total_files' => 100]);
        $this->github->shouldReceive('getRepository')->andReturn([]);
        $this->github->shouldReceive('getLanguages')->andReturn([]);
        $this->github->shouldReceive('getContributors')->andReturn([]);
        $this->pythonAnalyzer->shouldReceive('analyze')->andReturn([]);

        $this->scoreCalculator
            ->shouldReceive('calculate')
            ->once()
            ->andReturn(['overall' => 90]);

        File::shouldReceive('deleteDirectory');

        $result = $this->service->setRepositoryUrl($url)->analyze();

        $this->assertInstanceOf(RepositoryAnalyzerService::class, $result);
    }

    public function test_runs_python_analyzer(): void
    {
        $url = 'https://github.com/owner/repo';
        $path = '/tmp/repo-clone';

        $this->repository->shouldReceive('findByUrl')->andReturn(null);
        $this->repository->shouldReceive('extractRepoInfo')->andReturn([
            'owner' => 'owner',
            'repository_name' => 'repo',
        ]);
        $this->repository->shouldReceive('create')->andReturn(new RepositoryAnalysis);

        $this->cloner->shouldReceive('clone')->andReturn($path);
        $this->metricsCollector->shouldReceive('collect')->andReturn([]);
        $this->github->shouldReceive('getRepository')->andReturn([]);
        $this->github->shouldReceive('getLanguages')->andReturn([]);
        $this->github->shouldReceive('getContributors')->andReturn([]);
        $this->scoreCalculator->shouldReceive('calculate')->andReturn([]);

        $this->pythonAnalyzer
            ->shouldReceive('analyze')
            ->with($path)
            ->once()
            ->andReturn(['total_loc' => 500]);

        File::shouldReceive('deleteDirectory');

        $result = $this->service->setRepositoryUrl($url)->analyze();

        $this->assertInstanceOf(RepositoryAnalyzerService::class, $result);
    }

    public function test_deletes_cloned_directory_after_analysis(): void
    {
        $url = 'https://github.com/owner/repo';
        $path = '/tmp/repo-clone';

        $this->repository->shouldReceive('findByUrl')->andReturn(null);
        $this->repository->shouldReceive('extractRepoInfo')->andReturn([
            'owner' => 'owner',
            'repository_name' => 'repo',
        ]);
        $this->repository->shouldReceive('create')->andReturn(new RepositoryAnalysis);

        $this->cloner->shouldReceive('clone')->andReturn($path);
        $this->metricsCollector->shouldReceive('collect')->andReturn([]);
        $this->github->shouldReceive('getRepository')->andReturn([]);
        $this->github->shouldReceive('getLanguages')->andReturn([]);
        $this->github->shouldReceive('getContributors')->andReturn([]);
        $this->scoreCalculator->shouldReceive('calculate')->andReturn([]);
        $this->pythonAnalyzer->shouldReceive('analyze')->andReturn([]);

        File::shouldReceive('deleteDirectory')
            ->with($path)
            ->once();

        $result = $this->service->setRepositoryUrl($url)->analyze();

        $this->assertInstanceOf(RepositoryAnalyzerService::class, $result);
    }

    public function test_does_not_clone_when_analysis_exists(): void
    {
        $existingAnalysis = new RepositoryAnalysis(['slug' => 'owner-repo']);

        $this->repository
            ->shouldReceive('findByUrl')
            ->andReturn($existingAnalysis);

        $this->repository
            ->shouldReceive('isStale')
            ->with($existingAnalysis)
            ->andReturn(false);

        $this->cloner
            ->shouldNotReceive('clone');

        $result = $this->service
            ->setRepositoryUrl('https://github.com/owner/repo')
            ->analyze()
            ->object();

        $this->assertSame($existingAnalysis, $result);
    }

    public function test_merges_all_metrics_into_final_result(): void
    {
        $url = 'https://github.com/owner/repo';

        $this->repository->shouldReceive('findByUrl')->andReturn(null);
        $this->repository->shouldReceive('extractRepoInfo')->andReturn([
            'owner' => 'owner',
            'repository_name' => 'repo',
        ]);
        $this->cloner->shouldReceive('clone')->andReturn('/tmp/repo');
        $this->metricsCollector->shouldReceive('collect')->andReturn(['total_files' => 50]);
        $this->github->shouldReceive('getRepository')->andReturn(['stargazers_count' => 100]);
        $this->github->shouldReceive('getLanguages')->andReturn(['PHP' => 3000]);
        $this->github->shouldReceive('getContributors')->andReturn([]);
        $this->scoreCalculator->shouldReceive('calculate')->andReturn(['overall' => 75]);
        $this->pythonAnalyzer->shouldReceive('analyze')->andReturn(['total_loc' => 2000]);

        $metricsVerified = false;
        $this->repository
            ->shouldReceive('create')
            ->withArgs(function ($url, $name, $owner, $metrics) use (&$metricsVerified) {
                $metricsVerified = isset($metrics['total_files'])
                    && isset($metrics['github'])
                    && isset($metrics['scores'])
                    && isset($metrics['total_loc'])
                    && $metrics['github']['stars'] === 100;

                return true;
            })
            ->andReturn(new RepositoryAnalysis);

        File::shouldReceive('deleteDirectory');

        $this->service->setRepositoryUrl($url)->analyze();

        $this->assertTrue($metricsVerified, 'Metrics should contain all expected keys');
    }

    public function test_allows_method_chaining(): void
    {
        $existingAnalysis = new RepositoryAnalysis(['slug' => 'test']);

        $this->repository
            ->shouldReceive('findByUrl')
            ->andReturn($existingAnalysis);

        $this->repository
            ->shouldReceive('isStale')
            ->with($existingAnalysis)
            ->andReturn(false);

        $result = $this->service
            ->setRepositoryUrl('https://github.com/owner/repo')
            ->analyze();

        $this->assertInstanceOf(RepositoryAnalyzerService::class, $result);
    }

    public function test_returns_repository_analysis_object(): void
    {
        $existingAnalysis = new RepositoryAnalysis(['slug' => 'test']);

        $this->repository
            ->shouldReceive('findByUrl')
            ->andReturn($existingAnalysis);

        $this->repository
            ->shouldReceive('isStale')
            ->with($existingAnalysis)
            ->andReturn(false);

        $result = $this->service
            ->setRepositoryUrl('https://github.com/owner/repo')
            ->analyze()
            ->object();

        $this->assertInstanceOf(RepositoryAnalysis::class, $result);
    }

    /**
     * Helper to setup common mocks for new analysis tests
     */
    private function setupMocksForNewAnalysis(string $url, string $path = '/tmp/repo'): void
    {
        $this->repository->shouldReceive('findByUrl')->andReturn(null);
        $this->repository->shouldReceive('extractRepoInfo')->andReturn([
            'owner' => 'owner',
            'repository_name' => 'repo',
        ]);
        $this->repository->shouldReceive('create')->andReturn(new RepositoryAnalysis);

        $this->cloner->shouldReceive('clone')->andReturn($path);
        $this->metricsCollector->shouldReceive('collect')->andReturn([]);
        $this->github->shouldReceive('getRepository')->andReturn([]);
        $this->github->shouldReceive('getLanguages')->andReturn([]);
        $this->github->shouldReceive('getContributors')->andReturn([]);
        $this->scoreCalculator->shouldReceive('calculate')->andReturn([]);
        $this->pythonAnalyzer->shouldReceive('analyze')->andReturn([]);

        File::shouldReceive('deleteDirectory')->byDefault();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
