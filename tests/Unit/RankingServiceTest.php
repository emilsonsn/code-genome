<?php

namespace Tests\Unit;

use App\Models\RepositoryAnalysis;
use App\Services\RankingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingServiceTest extends TestCase
{
    use RefreshDatabase;

    private RankingService $rankingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rankingService = new RankingService;
    }

    public function testGetTopRepositoriesReturnsOrderedByScoreDescending(): void
    {
        RepositoryAnalysis::factory()->create([
            'repository_name' => 'High Score',
            'owner' => 'owner1',
            'metrics' => [
                'scores' => [
                    'overall' => 95,
                    'grade_label' => 'Excellent',
                    'grade_color' => 'green',
                    'documentation' => 90,
                    'tests' => 95,
                    'structure' => 95,
                    'maintainability' => 95,
                ],
                'github' => [
                    'stars' => 1000,
                    'contributors_count' => 50,
                ],
            ],
        ]);

        RepositoryAnalysis::factory()->create([
            'repository_name' => 'Low Score',
            'owner' => 'owner2',
            'metrics' => [
                'scores' => [
                    'overall' => 45,
                    'grade_label' => 'Fair',
                    'grade_color' => 'yellow',
                    'documentation' => 40,
                    'tests' => 45,
                    'structure' => 45,
                    'maintainability' => 45,
                ],
                'github' => [
                    'stars' => 100,
                    'contributors_count' => 5,
                ],
            ],
        ]);

        $repositories = $this->rankingService->getTopRepositories();

        $this->assertCount(2, $repositories);
        $this->assertEquals(95, $repositories[0]['overall']);
        $this->assertEquals(45, $repositories[1]['overall']);
    }

    public function testGetTopRepositoriesLimitsResultsTo50ByDefault(): void
    {
        for ($i = 0; $i < 60; $i++) {
            RepositoryAnalysis::factory()->create([
                'repository_name' => "Repo $i",
                'owner' => "owner$i",
                'metrics' => [
                    'scores' => [
                        'overall' => rand(0, 100),
                        'grade_label' => 'Good',
                        'grade_color' => 'emerald',
                    ],
                    'github' => [
                        'stars' => rand(0, 10000),
                        'contributors_count' => rand(0, 100),
                    ],
                ],
            ]);
        }

        $repositories = $this->rankingService->getTopRepositories();

        $this->assertCount(50, $repositories);
    }

    public function testGetTopRepositoriesLimitsResultsToCustomLimit(): void
    {
        for ($i = 0; $i < 30; $i++) {
            RepositoryAnalysis::factory()->create([
                'repository_name' => "Repo $i",
                'owner' => "owner$i",
                'metrics' => [
                    'scores' => [
                        'overall' => rand(0, 100),
                        'grade_label' => 'Good',
                        'grade_color' => 'emerald',
                    ],
                    'github' => [
                        'stars' => rand(0, 10000),
                        'contributors_count' => rand(0, 100),
                    ],
                ],
            ]);
        }

        $repositories = $this->rankingService->getTopRepositories(10);

        $this->assertCount(10, $repositories);
    }

    public function testGetTopRepositoriesUsesUpdatedAtAsBreaker(): void
    {
        $twoHoursAgo = now()->subHours(2);
        $oneHourAgo = now()->subHour();

        RepositoryAnalysis::factory()->create([
            'repository_name' => 'Older with same score',
            'owner' => 'owner1',
            'created_at' => $twoHoursAgo,
            'updated_at' => $twoHoursAgo,
            'metrics' => [
                'scores' => [
                    'overall' => 85,
                    'grade_label' => 'Good',
                    'grade_color' => 'emerald',
                ],
                'github' => [
                    'stars' => 500,
                    'contributors_count' => 20,
                ],
            ],
        ]);

        RepositoryAnalysis::factory()->create([
            'repository_name' => 'Newer with same score',
            'owner' => 'owner2',
            'created_at' => $oneHourAgo,
            'updated_at' => $oneHourAgo,
            'metrics' => [
                'scores' => [
                    'overall' => 85,
                    'grade_label' => 'Good',
                    'grade_color' => 'emerald',
                ],
                'github' => [
                    'stars' => 500,
                    'contributors_count' => 20,
                ],
            ],
        ]);

        $repositories = $this->rankingService->getTopRepositories();

        $this->assertEquals('Newer with same score', $repositories[0]['repository_name']);
        $this->assertEquals('Older with same score', $repositories[1]['repository_name']);
    }

    public function testGetDeveloperRankingsGroupsByOwner(): void
    {
        RepositoryAnalysis::factory()->create([
            'repository_name' => 'Repo 1',
            'owner' => 'laravel',
            'metrics' => [
                'scores' => [
                    'overall' => 95,
                    'grade_label' => 'Excellent',
                    'grade_color' => 'green',
                ],
                'github' => [
                    'stars' => 70000,
                    'contributors_count' => 500,
                ],
            ],
        ]);

        RepositoryAnalysis::factory()->create([
            'repository_name' => 'Repo 2',
            'owner' => 'laravel',
            'metrics' => [
                'scores' => [
                    'overall' => 90,
                    'grade_label' => 'Excellent',
                    'grade_color' => 'green',
                ],
                'github' => [
                    'stars' => 50000,
                    'contributors_count' => 400,
                ],
            ],
        ]);

        $rankings = $this->rankingService->getDeveloperRankings();

        $this->assertCount(1, $rankings);
        $this->assertEquals('laravel', $rankings[0]['owner']);
        $this->assertEquals(2, $rankings[0]['repository_count']);
    }

    public function testGetDeveloperRankingsCalculatesAverageScore(): void
    {
        RepositoryAnalysis::factory()->create([
            'repository_name' => 'Repo 1',
            'owner' => 'developer',
            'metrics' => [
                'scores' => [
                    'overall' => 80,
                    'grade_label' => 'Good',
                    'grade_color' => 'emerald',
                ],
                'github' => [
                    'stars' => 500,
                    'contributors_count' => 20,
                ],
            ],
        ]);

        RepositoryAnalysis::factory()->create([
            'repository_name' => 'Repo 2',
            'owner' => 'developer',
            'metrics' => [
                'scores' => [
                    'overall' => 90,
                    'grade_label' => 'Excellent',
                    'grade_color' => 'green',
                ],
                'github' => [
                    'stars' => 1000,
                    'contributors_count' => 50,
                ],
            ],
        ]);

        $rankings = $this->rankingService->getDeveloperRankings();

        $this->assertEquals(85, $rankings[0]['average_score']);
    }

    public function testGetDeveloperRankingsOrdersByAverageScoreDescending(): void
    {
        RepositoryAnalysis::factory()->create([
            'repository_name' => 'High Avg',
            'owner' => 'developer1',
            'metrics' => [
                'scores' => [
                    'overall' => 95,
                    'grade_label' => 'Excellent',
                    'grade_color' => 'green',
                ],
                'github' => [
                    'stars' => 1000,
                    'contributors_count' => 50,
                ],
            ],
        ]);

        RepositoryAnalysis::factory()->create([
            'repository_name' => 'Low Avg',
            'owner' => 'developer2',
            'metrics' => [
                'scores' => [
                    'overall' => 50,
                    'grade_label' => 'Fair',
                    'grade_color' => 'orange',
                ],
                'github' => [
                    'stars' => 100,
                    'contributors_count' => 5,
                ],
            ],
        ]);

        $rankings = $this->rankingService->getDeveloperRankings();

        $this->assertGreaterThan(
            $rankings[1]['average_score'],
            $rankings[0]['average_score']
        );
    }

    public function testGetDeveloperRankingsIncludesRepositoriesList(): void
    {
        RepositoryAnalysis::factory()->create([
            'repository_name' => 'Repo 1',
            'owner' => 'developer',
            'metrics' => [
                'scores' => [
                    'overall' => 85,
                    'grade_label' => 'Good',
                    'grade_color' => 'emerald',
                ],
                'github' => [
                    'stars' => 500,
                    'contributors_count' => 20,
                ],
            ],
        ]);

        $rankings = $this->rankingService->getDeveloperRankings();

        $this->assertCount(1, $rankings[0]['repositories']);
        $this->assertEquals('Repo 1', $rankings[0]['repositories'][0]['name']);
        $this->assertEquals(85, $rankings[0]['repositories'][0]['score']);
    }
}
