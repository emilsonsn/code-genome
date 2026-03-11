<?php

namespace Tests\Feature;

use App\Models\RepositoryAnalysis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexReturnsRankingView(): void
    {
        RepositoryAnalysis::factory()->create([
            'repository_name' => 'Laravel',
            'owner' => 'laravel',
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
                    'stars' => 70000,
                    'contributors_count' => 500,
                ],
            ],
        ]);

        $response = $this->get(route('repository-analyses.ranking'));

        $response->assertStatus(200);
        $response->assertViewIs('repository-analyses.ranking');
        $response->assertViewHas(['topRepositories', 'developerRankings']);
    }

    public function testIndexDisplaysTopRepositoriesOrdered(): void
    {
        RepositoryAnalysis::factory()->create([
            'repository_name' => 'High Score Repo',
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
            'repository_name' => 'Low Score Repo',
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

        $response = $this->get(route('repository-analyses.ranking'));

        $repositories = $response['topRepositories'];
        $this->assertGreaterThan(
            $repositories[1]['overall'],
            $repositories[0]['overall']
        );
    }

    public function testIndexDisplaysDeveloperRankings(): void
    {
        RepositoryAnalysis::factory()->create([
            'repository_name' => 'Repo 1',
            'owner' => 'developer1',
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
            'repository_name' => 'Repo 2',
            'owner' => 'developer1',
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

        $response = $this->get(route('repository-analyses.ranking'));

        $developerRankings = $response['developerRankings'];
        $this->assertCount(1, $developerRankings);
        $this->assertEquals('developer1', $developerRankings[0]['owner']);
        $this->assertEquals(2, $developerRankings[0]['repository_count']);
        $this->assertEquals(87.5, $developerRankings[0]['average_score']);
    }

    public function testIndexDisplaysEmptyStateWhenNoRepositories(): void
    {
        $response = $this->get(route('repository-analyses.ranking'));

        $response->assertStatus(200);
        $response->assertViewHas(['topRepositories', 'developerRankings']);
        $this->assertCount(0, $response['topRepositories']);
        $this->assertCount(0, $response['developerRankings']);
    }
}
