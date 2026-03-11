<?php

namespace Database\Factories;

use App\Models\RepositoryAnalysis;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RepositoryAnalysisFactory extends Factory
{
    protected $model = RepositoryAnalysis::class;

    public function definition(): array
    {
        $repoName = $this->faker->word();
        $owner = $this->faker->word();

        return [
            'repository_url' => "https://github.com/{$owner}/{$repoName}",
            'repository_name' => $repoName,
            'owner' => $owner,
            'slug' => Str::slug("{$owner}-{$repoName}"),
            'metrics' => [
                'scores' => [
                    'overall' => $this->faker->numberBetween(0, 100),
                    'grade_label' => $this->faker->randomElement(['Excellent', 'Good', 'Fair', 'Poor']),
                    'grade_color' => $this->faker->randomElement(['green', 'emerald', 'yellow', 'orange', 'red']),
                    'documentation' => $this->faker->numberBetween(0, 100),
                    'tests' => $this->faker->numberBetween(0, 100),
                    'structure' => $this->faker->numberBetween(0, 100),
                    'maintainability' => $this->faker->numberBetween(0, 100),
                ],
                'github' => [
                    'stars' => $this->faker->numberBetween(0, 100000),
                    'contributors_count' => $this->faker->numberBetween(0, 1000),
                ],
            ],
        ];
    }
}
