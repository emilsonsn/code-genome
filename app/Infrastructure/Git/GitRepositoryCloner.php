<?php

namespace App\Infrastructure\Git;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use RuntimeException;

class GitRepositoryCloner
{
    public function clone(string $url): string
    {
        $path = storage_path('app/repos/'.Str::uuid());

        File::ensureDirectoryExists(dirname($path));

        $result = Process::run([
            'git',
            'clone',
            '--depth=1',
            '--filter=blob:none',
            $url,
            $path,
        ]);

        if ($result->failed()) {
            throw new RuntimeException('Failed to clone repository');
        }

        return $path;
    }
}
