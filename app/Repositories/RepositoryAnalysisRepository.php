<?php

namespace App\Repositories;

use App\Models\RepositoryAnalysis;
use Illuminate\Support\Str;

class RepositoryAnalysisRepository
{
    public function findByUrl(string $url): ?RepositoryAnalysis
    {
        return RepositoryAnalysis::where('repository_url', $url)->first();
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
