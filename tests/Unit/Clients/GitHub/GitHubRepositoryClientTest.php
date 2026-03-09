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

    public function testFetchesRepositoryInformation(): void
    {
        Http::fake([
            '*/repos/laravel/laravel' => Http::response([
                'id' => 1234,
                'name' => 'laravel',
                'fullName' => 'laravel/laravel',
                'description' => 'A PHP framework for web artisans',
                'stargazersCount' => 70000,
                'forksCount' => 22000,
                'openIssuesCount' => 50,
            ], 200),
        ]);

        $result = $this->client->getRepository('laravel', 'laravel');

        $this->assertEquals('laravel', $result['name']);
        $this->assertEquals('laravel/laravel', $result['fullName']);
        $this->assertEquals(70000, $result['stargazersCount']);
    }

    public function testFetchesRepositoryLanguages(): void
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

    public function testFetchesContributors(): void
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

    public function testFetchesCommitActivity(): void
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

    public function testFetchesCodeFrequency(): void
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

    public function testHandlesEmptyResponse(): void
    {
        Http::fake([
            '*/repos/owner/repo/contributors' => Http::response([], 200),
        ]);

        $result = $this->client->getContributors('owner', 'repo');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testIncludesAuthorizationHeaderWhenTokenIsSet(): void
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

    public function testWorksWithoutToken(): void
    {
        config(['github.token' => null]);

        Http::fake([
            '*' => Http::response(['name' => 'test'], 200),
        ]);

        $client = new GitHubRepositoryClient;
        $result = $client->getRepository('owner', 'repo');

        $this->assertEquals('test', $result['name']);
    }

    public function testUsesCorrectBaseUrl(): void
    {
        config(['github.baseUrl' => 'https://api.github.com']);

        Http::fake([
            'https://api.github.com/*' => Http::response(['name' => 'test'], 200),
        ]);

        $client = new GitHubRepositoryClient;
        $client->getRepository('owner', 'repo');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.github.com');
        });
    }

    public function testConstructsCorrectEndpoints(): void
    {
        Http::fake([
            '*' => Http::response([], 200),
        ]);

        $this->client->getRepository('my-org', 'my-repo');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/repos/my-org/my-repo');
        });
    }

    public function testHandlesSpecialCharactersInOwnerAndRepo(): void
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
