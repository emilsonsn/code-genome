<?php

namespace App\Repositories;

use App\Models\RepositoryAnalysis;
use Carbon\Carbon;
use Illuminate\Support\Str;

class RepositoryAnalysisRepository
{
    private const CACHE_DURATION_HOURS = 24;

    public function findByUrl(string $url): ?RepositoryAnalysis
    {
        return RepositoryAnalysis::where('repository_url', $url)->first();
    }

    public function isStale(RepositoryAnalysis $analysis): bool
    {
        return $analysis->updated_at->diffInHours(Carbon::now()) >= self::CACHE_DURATION_HOURS;
    }

    public function create(
        string $url,
        ?string $name,
        ?string $owner,
        array $metrics
    ): RepositoryAnalysis {
        $slug = Str::slug($owner.'-'.$name);

        return RepositoryAnalysis::create([
            'repository_url' => $url,
            'repository_name' => $name,
            'owner' => $owner,
            'slug' => $slug,
            'metrics' => $metrics,
        ]);
    }

    public function update(RepositoryAnalysis $analysis, array $metrics): RepositoryAnalysis
    {
        $analysis->update([
            'metrics' => $metrics,
        ]);

        return $analysis->fresh();
    }

    public function extractRepoInfo(string $url): array
    {
        $normalized = rtrim($url, '/');

        $path = parse_url($normalized, PHP_URL_PATH) ?? '';

        $segments = array_values(array_filter(explode('/', $path)));

        return [
            'owner' => $segments[0] ?? null,
            'repository_name' => $segments[1] ?? null,
        ];
    }
}
