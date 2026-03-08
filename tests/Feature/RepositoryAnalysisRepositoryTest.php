<?php

namespace Tests\Feature;

use App\Models\RepositoryAnalysis;
use App\Repositories\RepositoryAnalysisRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepositoryAnalysisRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private RepositoryAnalysisRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new RepositoryAnalysisRepository;
    }

    public function test_find_by_url_returns_analysis_when_exists(): void
    {
        $analysis = RepositoryAnalysis::create([
            'repository_url' => 'https://github.com/owner/repo',
            'repository_name' => 'repo',
            'owner' => 'owner',
            'slug' => 'owner-repo',
            'metrics' => ['test' => 'data'],
        ]);

        $result = $this->repository->findByUrl('https://github.com/owner/repo');

        $this->assertNotNull($result);
        $this->assertEquals($analysis->id, $result->id);
    }

    public function test_find_by_url_returns_null_when_not_exists(): void
    {
        $result = $this->repository->findByUrl('https://github.com/nonexistent/repo');

        $this->assertNull($result);
    }

    public function test_is_stale_returns_true_when_analysis_older_than24_hours(): void
    {
        $analysis = RepositoryAnalysis::create([
            'repository_url' => 'https://github.com/owner/repo',
            'repository_name' => 'repo',
            'owner' => 'owner',
            'slug' => 'owner-repo',
            'metrics' => ['test' => 'data'],
        ]);

        $analysis->updated_at = Carbon::now()->subHours(25);
        $analysis->save();

        $this->assertTrue($this->repository->isStale($analysis));
    }

    public function test_is_stale_returns_false_when_analysis_less_than24_hours(): void
    {
        $analysis = RepositoryAnalysis::create([
            'repository_url' => 'https://github.com/owner/repo',
            'repository_name' => 'repo',
            'owner' => 'owner',
            'slug' => 'owner-repo',
            'metrics' => ['test' => 'data'],
        ]);

        $analysis->updated_at = Carbon::now()->subHours(23);
        $analysis->save();

        $this->assertFalse($this->repository->isStale($analysis));
    }

    public function test_is_stale_returns_true_when_analysis_exactly24_hours(): void
    {
        $analysis = RepositoryAnalysis::create([
            'repository_url' => 'https://github.com/owner/repo',
            'repository_name' => 'repo',
            'owner' => 'owner',
            'slug' => 'owner-repo',
            'metrics' => ['test' => 'data'],
        ]);

        $analysis->updated_at = Carbon::now()->subHours(24);
        $analysis->save();

        $this->assertTrue($this->repository->isStale($analysis));
    }

    public function test_update_updates_metrics_and_timestamp(): void
    {
        $analysis = RepositoryAnalysis::create([
            'repository_url' => 'https://github.com/owner/repo',
            'repository_name' => 'repo',
            'owner' => 'owner',
            'slug' => 'owner-repo',
            'metrics' => ['old' => 'data'],
        ]);

        $oldUpdatedAt = $analysis->updated_at;

        // Wait a second to ensure timestamp changes
        Carbon::setTestNow(Carbon::now()->addSecond());

        $newMetrics = ['new' => 'metrics', 'score' => 100];
        $result = $this->repository->update($analysis, $newMetrics);

        $this->assertEquals($newMetrics, $result->metrics);
        $this->assertTrue($result->updated_at->greaterThan($oldUpdatedAt));

        Carbon::setTestNow();
    }

    public function test_create_creates_new_analysis(): void
    {
        $result = $this->repository->create(
            'https://github.com/owner/newrepo',
            'newrepo',
            'owner',
            ['metrics' => 'data']
        );

        $this->assertNotNull($result);
        $this->assertEquals('https://github.com/owner/newrepo', $result->repository_url);
        $this->assertEquals('newrepo', $result->repository_name);
        $this->assertEquals('owner', $result->owner);
        $this->assertEquals('owner-newrepo', $result->slug);
        $this->assertEquals(['metrics' => 'data'], $result->metrics);
    }

    public function test_extract_repo_info_extracts_owner_and_repo(): void
    {
        $result = $this->repository->extractRepoInfo('https://github.com/laravel/framework');

        $this->assertEquals('laravel', $result['owner']);
        $this->assertEquals('framework', $result['repository_name']);
    }

    public function test_extract_repo_info_handles_trailing_slash(): void
    {
        $result = $this->repository->extractRepoInfo('https://github.com/laravel/framework/');

        $this->assertEquals('laravel', $result['owner']);
        $this->assertEquals('framework', $result['repository_name']);
    }
}
