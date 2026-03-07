<?php

namespace App\Services;

use App\Infrastructure\Git\GitRepositoryCloner;
use App\Infrastructure\Metrics\RepositoryMetricsCollector;
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
    ) {}

    public function setRepositoryUrl(string $url): self
    {
        $this->repositoryUrl = $url;

        return $this;
    }

    public function analyze(): self
    {
        $existing = $this->repository->findByUrl($this->repositoryUrl);

        if ($existing) {
            $this->analysis = $existing;

            return $this;
        }

        $repoInfo = $this->repository->extractRepoInfo($this->repositoryUrl);

        $path = $this->cloner->clone($this->repositoryUrl);

        $metrics = $this->metricsCollector->collect($path);

        $scores = $this->scoreCalculator->calculate($metrics);

        $metrics['scores'] = $scores;

        $this->analysis = $this->repository->create(
            $this->repositoryUrl,
            $repoInfo['repository_name'],
            $repoInfo['owner'],
            $metrics
        );

        File::deleteDirectory($path);

        return $this;
    }

    public function object(): RepositoryAnalysis
    {
        return $this->analysis;
    }
}
