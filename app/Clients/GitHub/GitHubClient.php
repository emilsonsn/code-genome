<?php

namespace App\Clients\GitHub;

use Illuminate\Support\Facades\Http;

class GitHubClient
{
    protected string $baseUrl;

    protected ?string $token;

    public function __construct()
    {
        $this->baseUrl = config('github.base_url');
        $this->token = config('github.token');
    }

    protected function request(string $endpoint): array
    {
        $request = Http::baseUrl($this->baseUrl)
            ->acceptJson();

        if ($this->token) {
            $request->withToken($this->token);
        }

        return $request
            ->get($endpoint)
            ->json();
    }
}
