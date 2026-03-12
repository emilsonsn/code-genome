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
        $normalizedUrl = $this->normalizeUrl($url);

        return RepositoryAnalysis::query()
            ->where('repository_url', $normalizedUrl)
            ->orWhereRaw('LOWER(repository_url) = ?', [strtolower($normalizedUrl)])
            ->first();
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
            'repository_url' => $this->normalizeUrl($url),
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
        $normalized = $this->normalizeUrl($url);

        $path = parse_url($normalized, PHP_URL_PATH) ?? '';

        $segments = array_values(array_filter(explode('/', $path)));

        $repositoryName = $segments[1] ?? null;

        if ($repositoryName) {
            $repositoryName = preg_replace('/\.git$/i', '', $repositoryName);
        }

        return [
            'owner' => $segments[0] ?? null,
            'repository_name' => $repositoryName,
        ];
    }

    public function normalizeUrl(string $url): string
    {
        $trimmed = trim($url);

        if ($trimmed === '') {
            return $trimmed;
        }

        $withoutGitSuffix = preg_replace('/\.git$/i', '', rtrim($trimmed, '/'));

        if (! is_string($withoutGitSuffix)) {
            return rtrim($trimmed, '/');
        }

        $parts = parse_url($withoutGitSuffix);

        if ($parts === false || ! isset($parts['host'])) {
            return rtrim($withoutGitSuffix, '/');
        }

        $scheme = strtolower($parts['scheme'] ?? 'https');
        $host = strtolower($parts['host']);
        $path = trim($parts['path'] ?? '', '/');

        if ($path === '') {
            return $scheme.'://'.$host;
        }

        $segments = array_values(array_filter(explode('/', $path)));

        if (count($segments) >= 2) {
            $owner = strtolower($segments[0]);
            $repository = strtolower($segments[1]);

            return $scheme.'://'.$host.'/'.$owner.'/'.$repository;
        }

        return $scheme.'://'.$host.'/'.strtolower($segments[0]);
    }
}
