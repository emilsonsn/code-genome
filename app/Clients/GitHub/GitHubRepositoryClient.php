<?php

namespace App\Clients\GitHub;

class GitHubRepositoryClient extends GitHubClient
{
    public function getRepository(string $owner, string $repo): array
    {
        return $this->request("/repos/{$owner}/{$repo}");
    }

    public function getLanguages(string $owner, string $repo): array
    {
        return $this->request("/repos/{$owner}/{$repo}/languages");
    }

    public function getContributors(string $owner, string $repo): array
    {
        return $this->request("/repos/{$owner}/{$repo}/contributors");
    }

    public function getCommitActivity(string $owner, string $repo): array
    {
        return $this->request("/repos/{$owner}/{$repo}/stats/commit_activity");
    }

    public function getCodeFrequency(string $owner, string $repo): array
    {
        return $this->request("/repos/{$owner}/{$repo}/stats/code_frequency");
    }
}
