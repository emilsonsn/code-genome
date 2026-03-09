<?php

namespace Tests\Unit\Repositories;

use App\Models\RepositoryAnalysis;
use App\Repositories\RepositoryAnalysisRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function testFindsAnalysisByUrl(): void
    {
        $analysis = RepositoryAnalysis::create([
            'repository_url' => 'https://github.com/laravel/laravel',
            'repository_name' => 'laravel',
            'owner' => 'laravel',
            'slug' => 'laravel-laravel',
            'metrics' => ['test' => true],
        ]);

        $found = $this->repository->findByUrl('https://github.com/laravel/laravel');

        $this->assertNotNull($found);
        $this->assertEquals($analysis->id, $found->id);
    }

    public function testReturnsNullWhenUrlNotFound(): void
    {
        $found = $this->repository->findByUrl('https://github.com/non/existent');

        $this->assertNull($found);
    }

    public function testCreatesNewAnalysis(): void
    {
        $analysis = $this->repository->create(
            'https://github.com/owner/repo',
            'repo',
            'owner',
            ['total_files' => 100]
        );

        $this->assertInstanceOf(RepositoryAnalysis::class, $analysis);
        $this->assertEquals('https://github.com/owner/repo', $analysis->repository_url);
        $this->assertEquals('repo', $analysis->repository_name);
        $this->assertEquals('owner', $analysis->owner);
        $this->assertEquals('owner-repo', $analysis->slug);
        $this->assertEquals(['total_files' => 100], $analysis->metrics);
    }

    public function testGeneratesSlugFromOwnerAndName(): void
    {
        $analysis = $this->repository->create(
            'https://github.com/My-Owner/My-Repo',
            'My-Repo',
            'My-Owner',
            []
        );

        $this->assertEquals('my-owner-my-repo', $analysis->slug);
    }

    public function testHandlesSpecialCharactersInSlug(): void
    {
        $analysis = $this->repository->create(
            'https://github.com/owner/repo-name_v2.0',
            'repo-name_v2.0',
            'owner',
            []
        );

        $this->assertNotEmpty($analysis->slug);
        $this->assertStringNotContainsString(' ', $analysis->slug);
    }

    #[DataProvider('urlProvider')]
    public function testExtractsRepoInfoFromUrl(string $url, string $expectedOwner, string $expectedName): void
    {
        $info = $this->repository->extractRepoInfo($url);

        $this->assertEquals($expectedOwner, $info['owner']);
        $this->assertEquals($expectedName, $info['repository_name']);
    }

    public static function urlProvider(): array
    {
        return [
            'github standard' => [
                'https://github.com/laravel/laravel',
                'laravel',
                'laravel',
            ],
            'github with trailing slash' => [
                'https://github.com/owner/repo/',
                'owner',
                'repo',
            ],
            'gitlab' => [
                'https://gitlab.com/gitlab-org/gitlab',
                'gitlab-org',
                'gitlab',
            ],
            'bitbucket' => [
                'https://bitbucket.org/atlassian/bitbucket',
                'atlassian',
                'bitbucket',
            ],
            'github with .git extension' => [
                'https://github.com/owner/repo.git',
                'owner',
                'repo.git',
            ],
        ];
    }

    public function testHandlesInvalidUrlGracefully(): void
    {
        // For invalid URLs, parse_url treats the input as a path
        // So 'not-a-url' becomes the first path segment (owner)
        $info = $this->repository->extractRepoInfo('not-a-url');

        $this->assertEquals('not-a-url', $info['owner']);
        $this->assertNull($info['repository_name']);
    }

    public function testHandlesUrlWithoutPath(): void
    {
        $info = $this->repository->extractRepoInfo('https://github.com');

        $this->assertNull($info['owner']);
        $this->assertNull($info['repository_name']);
    }

    public function testHandlesUrlWithOnlyOwner(): void
    {
        $info = $this->repository->extractRepoInfo('https://github.com/owner');

        $this->assertEquals('owner', $info['owner']);
        $this->assertNull($info['repository_name']);
    }

    public function testPersistsAnalysisToDatabase(): void
    {
        $this->repository->create(
            'https://github.com/test/test',
            'test',
            'test',
            ['key' => 'value']
        );

        $this->assertDatabaseHas('repository_analyses', [
            'repository_url' => 'https://github.com/test/test',
            'repository_name' => 'test',
            'owner' => 'test',
        ]);
    }

    public function testStoresComplexMetricsAsJson(): void
    {
        $metrics = [
            'total_files' => 100,
            'languages' => ['php' => 50, 'js' => 30],
            'nested' => [
                'level1' => [
                    'level2' => 'value',
                ],
            ],
        ];

        $analysis = $this->repository->create(
            'https://github.com/test/metrics',
            'metrics',
            'test',
            $metrics
        );

        $retrieved = RepositoryAnalysis::find($analysis->id);

        $this->assertEquals($metrics, $retrieved->metrics);
    }
}
