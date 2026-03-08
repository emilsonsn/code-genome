<?php

namespace App\Infrastructure\Python;

use App\Enums\RepositoryStack;
use Illuminate\Support\Facades\Process;

class PythonRepositoryAnalyzer
{
    public function analyze(string $path): array
    {
        $extensions = $this->getCodeExtensions();
        $extensionsArg = escapeshellarg(implode(',', $extensions));

        $process = Process::path(base_path('python'))
            ->run("./env/bin/python analyze_repo.py {$path} {$extensionsArg}");

        if (! $process->successful()) {
            return [];
        }

        return json_decode($process->output(), true) ?? [];
    }

    private function getCodeExtensions(): array
    {
        return collect(RepositoryStack::cases())
            ->flatMap(fn ($stack) => $stack->extensions())
            ->unique()
            ->values()
            ->toArray();
    }
}
