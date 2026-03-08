<?php

namespace App\Infrastructure\Python;

use Illuminate\Support\Facades\Process;

class PythonRepositoryAnalyzer
{
    public function analyze(string $path): array
    {
        $process = Process::path(base_path('python'))
            ->run("./env/bin/python analyze_repo.py {$path}");

        if (! $process->successful()) {
            return [];
        }

        return json_decode($process->output(), true) ?? [];
    }
}