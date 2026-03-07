<?php

namespace App\Infrastructure\Metrics;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RepositoryMetricsCollector
{
    public function collect(string $path): array
    {
        $files = $this->collectFiles($path);
        $directories = $this->collectDirectories($path);

        $dependencyFiles = $this->detectDependencyFiles($path);
        $stackSignals = $this->detectStack($path, $dependencyFiles);

        return [
            'total_files' => $files->count(),
            'total_directories' => $directories->count(),
            'total_size_bytes' => $this->calculateTotalSize($files),
            'total_size_human' => $this->formatBytes($this->calculateTotalSize($files)),
            'languages' => $this->detectLanguages($files),
            'has_readme' => $this->detectReadme($files),
            'has_docs' => $this->detectDocs($path),
            'test_files_count' => $this->detectTests($files),
            'dependency_files' => $dependencyFiles,
            'stack_signals' => $stackSignals,
            'top_level_directories' => $this->detectTopLevelDirectories($path),
        ];
    }

    private function collectFiles(string $path)
    {
        return collect(File::allFiles($path))
            ->reject(fn ($file) => str_contains($file->getPathname(), DIRECTORY_SEPARATOR.'.git'.DIRECTORY_SEPARATOR)
            );
    }

    private function collectDirectories(string $path)
    {
        return collect(File::directories($path))
            ->reject(fn ($dir) => str_contains($dir, DIRECTORY_SEPARATOR.'.git'.DIRECTORY_SEPARATOR)
            );
    }

    private function calculateTotalSize($files): int
    {
        return $files->sum(function ($file) {
            try {
                return $file->getSize();
            } catch (\Throwable) {
                return 0;
            }
        });
    }

    private function detectLanguages($files): array
    {
        return $files
            ->map(fn ($file) => strtolower($file->getExtension()))
            ->filter()
            ->countBy()
            ->sortDesc()
            ->toArray();
    }

    private function detectReadme($files): bool
    {
        return $files->contains(fn ($file) => Str::startsWith(strtolower($file->getFilename()), 'readme')
        );
    }

    private function detectDocs(string $path): bool
    {
        return File::exists($path.'/docs');
    }

    private function detectTests($files): int
    {
        return $files->filter(function ($file) {

            $name = strtolower($file->getFilename());
            $pathname = str_replace('\\', '/', strtolower($file->getPathname()));

            return str_contains($pathname, '/tests/')
                || str_contains($pathname, '/test/')
                || str_ends_with($name, 'test.php')
                || str_ends_with($name, '.spec.js')
                || str_ends_with($name, '.spec.ts')
                || str_ends_with($name, '.test.js')
                || str_ends_with($name, '.test.ts')
                || str_ends_with($name, '_test.py');

        })->count();
    }

    private function detectDependencyFiles(string $path): array
    {
        return [
            'composer.json' => File::exists($path.'/composer.json'),
            'package.json' => File::exists($path.'/package.json'),
            'requirements.txt' => File::exists($path.'/requirements.txt'),
            'pyproject.toml' => File::exists($path.'/pyproject.toml'),
            'Gemfile' => File::exists($path.'/Gemfile'),
            'go.mod' => File::exists($path.'/go.mod'),
        ];
    }

    private function detectStack(string $path, array $dependencyFiles): array
    {
        return [
            'laravel' => File::exists($path.'/artisan') && File::exists($path.'/routes/web.php'),
            'symfony' => File::exists($path.'/bin/console'),
            'react' => $this->fileContains($path.'/package.json', ['react']),
            'vue' => $this->fileContains($path.'/package.json', ['vue']),
            'angular' => File::exists($path.'/angular.json'),
            'python' => $dependencyFiles['requirements.txt'] || $dependencyFiles['pyproject.toml'],
            'node' => $dependencyFiles['package.json'],
        ];
    }

    private function detectTopLevelDirectories(string $path): array
    {
        return collect(File::directories($path))
            ->map(fn ($dir) => basename($dir))
            ->filter(fn ($dir) => ! in_array($dir, ['.git', 'vendor', 'node_modules']))
            ->values()
            ->all();
    }

    private function fileContains(string $filePath, array $needles): bool
    {
        if (! File::exists($filePath)) {
            return false;
        }

        $content = strtolower(File::get($filePath));

        foreach ($needles as $needle) {
            if (str_contains($content, strtolower($needle))) {
                return true;
            }
        }

        return false;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
