<?php

namespace Tests\Unit\Clients\GitHub;

use App\Clients\GitHub\GitHubRepositoryClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GitHubRepositoryClientTest extends TestCase
{
    private GitHubRepositoryClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new GitHubRepositoryClient;
    }

    public function test_fetches_repository_information(): void
    {
        Http::fake([
            '*/repos/laravel/laravel' => Http::response([
                'id' => 1234,
                'name' => 'laravel',
                'full_name' => 'laravel/laravel',
                'description' => 'A PHP framework for web artisans',
                'stargazers_count' => 70000,
                'forks_count' => 22000,
                'open_issues_count' => 50,
            ], 200),
        ]);

        $result = $this->client->getRepository('laravel', 'laravel');

        $this->assertEquals('laravel', $result['name']);
        $this->assertEquals('laravel/laravel', $result['full_name']);
        $this->assertEquals(70000, $result['stargazers_count']);
    }

    public function test_fetches_repository_languages(): void
    {
        Http::fake([
            '*/repos/owner/repo/languages' => Http::response([
                'PHP' => 500000,
                'JavaScript' => 200000,
                'CSS' => 50000,
            ], 200),
        ]);

        $result = $this->client->getLanguages('owner', 'repo');

        $this->assertArrayHasKey('PHP', $result);
        $this->assertArrayHasKey('JavaScript', $result);
        $this->assertEquals(500000, $result['PHP']);
    }

    public function test_fetches_contributors(): void
    {
        Http::fake([
            '*/repos/owner/repo/contributors' => Http::response([
                ['login' => 'user1', 'contributions' => 100],
                ['login' => 'user2', 'contributions' => 50],
            ], 200),
        ]);

        $result = $this->client->getContributors('owner', 'repo');

        $this->assertCount(2, $result);
        $this->assertEquals('user1', $result[0]['login']);
        $this->assertEquals(100, $result[0]['contributions']);
    }

    public function test_fetches_commit_activity(): void
    {
        Http::fake([
            '*/repos/owner/repo/stats/commit_activity' => Http::response([
                ['week' => 1609459200, 'total' => 10, 'days' => [1, 2, 3, 1, 1, 1, 1]],
                ['week' => 1610064000, 'total' => 20, 'days' => [3, 3, 4, 2, 2, 3, 3]],
            ], 200),
        ]);

        $result = $this->client->getCommitActivity('owner', 'repo');

        $this->assertCount(2, $result);
        $this->assertEquals(10, $result[0]['total']);
    }

    public function test_fetches_code_frequency(): void
    {
        Http::fake([
            '*/repos/owner/repo/stats/code_frequency' => Http::response([
                [1609459200, 500, -100],
                [1610064000, 300, -50],
            ], 200),
        ]);

        $result = $this->client->getCodeFrequency('owner', 'repo');

        $this->assertCount(2, $result);
        $this->assertEquals(500, $result[0][1]);
        $this->assertEquals(-100, $result[0][2]);
    }

    public function test_handles_empty_response(): void
    {
        Http::fake([
            '*/repos/owner/repo/contributors' => Http::response([], 200),
        ]);

        $result = $this->client->getContributors('owner', 'repo');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_includes_authorization_header_when_token_is_set(): void
    {
        config(['github.token' => 'test-token']);

        Http::fake([
            '*' => Http::response(['name' => 'test'], 200),
        ]);

        $client = new GitHubRepositoryClient;
        $client->getRepository('owner', 'repo');

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer test-token');
        });
    }

    public function test_works_without_token(): void
    {
        config(['github.token' => null]);

        Http::fake([
            '*' => Http::response(['name' => 'test'], 200),
        ]);

        $client = new GitHubRepositoryClient;
        $result = $client->getRepository('owner', 'repo');

        $this->assertEquals('test', $result['name']);
    }

    public function test_uses_correct_base_url(): void
    {
        config(['github.base_url' => 'https://api.github.com']);

        Http::fake([
            'https://api.github.com/*' => Http::response(['name' => 'test'], 200),
        ]);

        $client = new GitHubRepositoryClient;
        $client->getRepository('owner', 'repo');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.github.com');
        });
    }

    public function test_constructs_correct_endpoints(): void
    {
        Http::fake([
            '*' => Http::response([], 200),
        ]);

        $this->client->getRepository('my-org', 'my-repo');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/repos/my-org/my-repo');
        });
    }

    public function test_handles_special_characters_in_owner_and_repo(): void
    {
        Http::fake([
            '*' => Http::response(['name' => 'test-repo'], 200),
        ]);

        $result = $this->client->getRepository('owner-name', 'repo-name.js');

        $this->assertEquals('test-repo', $result['name']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/repos/owner-name/repo-name.js');
        });
    }
}
