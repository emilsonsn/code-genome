<?php

namespace Tests\Unit\Infrastructure\Git;

use App\Infrastructure\Git\GitRepositoryCloner;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use Tests\TestCase;

class GitRepositoryClonerTest extends TestCase
{
    private GitRepositoryCloner $cloner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cloner = new GitRepositoryCloner;
    }

    public function test_clones_repository_successfully(): void
    {
        File::shouldReceive('ensureDirectoryExists')
            ->once()
            ->andReturnTrue();

        Process::fake([
            '*' => Process::result(
                output: '',
                exitCode: 0
            ),
        ]);

        $path = $this->cloner->clone('https://github.com/owner/repo');

        $this->assertStringStartsWith(storage_path('app/repos/'), $path);
    }

    public function test_creates_directory_before_cloning(): void
    {
        File::shouldReceive('ensureDirectoryExists')
            ->once()
            ->andReturnTrue();

        Process::fake([
            '*' => Process::result(output: '', exitCode: 0),
        ]);

        $this->cloner->clone('https://github.com/owner/repo');

        // No assertion needed - shouldReceive validates the call
    }

    public function test_throws_exception_on_clone_failure(): void
    {
        File::shouldReceive('ensureDirectoryExists')
            ->once()
            ->andReturnTrue();

        Process::fake([
            '*' => Process::result(
                output: 'fatal: repository not found',
                exitCode: 128
            ),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to clone repository');

        $this->cloner->clone('https://github.com/invalid/repo');
    }

    public function test_generates_unique_path_for_each_clone(): void
    {
        File::shouldReceive('ensureDirectoryExists')
            ->twice()
            ->andReturnTrue();

        Process::fake([
            '*' => Process::result(output: '', exitCode: 0),
        ]);

        $path1 = $this->cloner->clone('https://github.com/owner/repo1');
        $path2 = $this->cloner->clone('https://github.com/owner/repo2');

        $this->assertNotEquals($path1, $path2);
    }

    public function test_uses_blobless_clone(): void
    {
        File::shouldReceive('ensureDirectoryExists')
            ->once()
            ->andReturnTrue();

        Process::fake([
            '*' => Process::result(output: '', exitCode: 0),
        ]);

        $this->cloner->clone('https://github.com/owner/repo');

        Process::assertRan(function ($process) {
            $command = implode(' ', $process->command);

            return str_contains($command, '--filter=blob:none');
        });
    }

    public function test_does_not_use_depth_flag(): void
    {
        File::shouldReceive('ensureDirectoryExists')
            ->once()
            ->andReturnTrue();

        Process::fake([
            '*' => Process::result(output: '', exitCode: 0),
        ]);

        $this->cloner->clone('https://github.com/owner/repo');

        Process::assertRan(function ($process) {
            $command = implode(' ', $process->command);

            return ! str_contains($command, '--depth');
        });
    }

    public function test_passes_url_to_git_command(): void
    {
        File::shouldReceive('ensureDirectoryExists')
            ->once()
            ->andReturnTrue();

        Process::fake([
            '*' => Process::result(output: '', exitCode: 0),
        ]);

        $url = 'https://github.com/laravel/laravel';
        $this->cloner->clone($url);

        Process::assertRan(function ($process) use ($url) {
            return in_array($url, $process->command);
        });
    }

    public function test_returns_path_stored_in_repos_directory(): void
    {
        File::shouldReceive('ensureDirectoryExists')
            ->once()
            ->andReturnTrue();

        Process::fake([
            '*' => Process::result(output: '', exitCode: 0),
        ]);

        $path = $this->cloner->clone('https://github.com/owner/repo');

        $this->assertStringContainsString('repos', $path);
    }
}
