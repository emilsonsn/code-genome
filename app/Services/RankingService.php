<?php

namespace App\Services;

use App\Models\RepositoryAnalysis;
use Illuminate\Support\Collection;

class RankingService
{
    public function getTopRepositories(int $limit = 50): Collection
    {
        return RepositoryAnalysis::query()
            ->latest('updated_at')
            ->get()
            ->map(function (RepositoryAnalysis $analysis) {
                $scores = $analysis->metrics['scores'] ?? [];
                $github = $analysis->metrics['github'] ?? [];

                return [
                    'id' => $analysis->id,
                    'repository_name' => $analysis->repository_name,
                    'owner' => $analysis->owner,
                    'overall' => $scores['overall'] ?? 0,
                    'grade' => $scores['grade_label'] ?? 'Unknown',
                    'grade_color' => $scores['grade_color'] ?? 'indigo',
                    'documentation' => $scores['documentation'] ?? 0,
                    'tests' => $scores['tests'] ?? 0,
                    'structure' => $scores['structure'] ?? 0,
                    'maintainability' => $scores['maintainability'] ?? 0,
                    'stars' => $github['stars'] ?? 0,
                    'contributors_count' => $github['contributors_count'] ?? 0,
                    'updated_at' => $analysis->updated_at,
                    'url' => route('repository-analyses.show', $analysis),
                ];
            })
            ->sortBy(function ($repo) {
                return [
                    -intval($repo['overall']),
                    -$repo['updated_at']->getTimestamp()
                ];
            })
            ->take($limit)
            ->values();
    }

    public function getDeveloperRankings(): Collection
    {
        return RepositoryAnalysis::query()
            ->get()
            ->groupBy('owner')
            ->map(function (Collection $repositories, string $owner) {
                $scores = $repositories->map(function (RepositoryAnalysis $analysis) {
                    return $analysis->metrics['scores']['overall'] ?? 0;
                });

                return [
                    'owner' => $owner,
                    'average_score' => round($scores->avg(), 2),
                    'repository_count' => $repositories->count(),
                    'repositories' => $repositories
                        ->map(function (RepositoryAnalysis $analysis) {
                            $scores = $analysis->metrics['scores'] ?? [];

                            return [
                                'name' => $analysis->repository_name,
                                'score' => $scores['overall'] ?? 0,
                                'url' => route('repository-analyses.show', $analysis),
                            ];
                        })
                        ->values()
                        ->toArray(),
                ];
            })
            ->sortByDesc('average_score')
            ->values();
    }
}
