<?php

namespace App\Services;

use App\Clients\GitHub\GitHubRepositoryClient;
use App\Infrastructure\Git\GitRepositoryCloner;
use App\Infrastructure\Metrics\RepositoryMetricsCollector;
use App\Infrastructure\Python\PythonRepositoryAnalyzer;
use App\Infrastructure\Score\RepositoryScoreCalculator;
use App\Models\RepositoryAnalysis;
use App\Repositories\RepositoryAnalysisRepository;
use Illuminate\Support\Facades\File;

class RepositoryAnalyzerService
{
    private string $repositoryUrl;

    private ?RepositoryAnalysis $analysis = null;

    public function __construct(
        private RepositoryAnalysisRepository $repository,
        private GitRepositoryCloner $cloner,
        private RepositoryMetricsCollector $metricsCollector,
        private RepositoryScoreCalculator $scoreCalculator,
        private GitHubRepositoryClient $github,
        private PythonRepositoryAnalyzer $pythonAnalyzer
    ) {}

    public function setRepositoryUrl(string $url): self
    {
        $this->repositoryUrl = $url;

        return $this;
    }

    public function analyze(): self
    {
        $existing = $this->repository->findByUrl($this->repositoryUrl);

        if ($existing && ! $this->repository->isStale($existing)) {
            $this->analysis = $existing;

            return $this;
        }

        $repoInfo = $this->repository->extractRepoInfo($this->repositoryUrl);

        $path = null;

        try {
            $path = $this->cloner->clone($this->repositoryUrl);

            $metrics = $this->metricsCollector->collect($path);

            $githubData = $this->fetchGitHubData(
                $repoInfo['owner'],
                $repoInfo['repository_name']
            );

            $scores = $this->scoreCalculator->calculate($metrics);

            $pythonMetrics = $this->pythonAnalyzer->analyze($path);

            $metrics = array_merge($metrics, $pythonMetrics);
            $metrics['github'] = $githubData;
            $metrics['scores'] = $scores;

            if ($existing) {
                $this->analysis = $this->repository->update($existing, $metrics);
            } else {
                $this->analysis = $this->repository->create(
                    $this->repositoryUrl,
                    $repoInfo['repository_name'],
                    $repoInfo['owner'],
                    $metrics
                );
            }
        } finally {
            if ($path) {
                File::deleteDirectory($path);
            }
        }

        return $this;
    }

    public function object(): RepositoryAnalysis
    {
        return $this->analysis;
    }

    private function fetchGitHubData(string $owner, string $repo): array
    {
        $repository = $this->github->getRepository($owner, $repo);

        $languages = $this->github->getLanguages($owner, $repo);

        $contributors = $this->github->getContributors($owner, $repo);

        return [
            'stars' => $repository['stargazers_count'] ?? 0,
            'forks' => $repository['forks_count'] ?? 0,
            'open_issues' => $repository['open_issues_count'] ?? 0,
            'watchers' => $repository['watchers_count'] ?? 0,
            'default_branch' => $repository['default_branch'] ?? null,
            'languages' => $languages,
            'contributors_count' => count($contributors),
        ];
    }
}
