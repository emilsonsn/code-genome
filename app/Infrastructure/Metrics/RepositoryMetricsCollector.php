<?php

namespace App\Infrastructure\Metrics;

use App\Enums\RepositoryStack;
use App\Infrastructure\Python\PythonRepositoryAnalyzer;
use App\Infrastructure\Stack\StackDetector;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RepositoryMetricsCollector
{
    private StackDetector $stackDetector;

    private PythonRepositoryAnalyzer $pythonAnalyzer;

    public function __construct(StackDetector $stackDetector, PythonRepositoryAnalyzer $pythonAnalyzer)
    {
        $this->stackDetector = $stackDetector;
        $this->pythonAnalyzer = $pythonAnalyzer;
    }

    public function collect(string $path): array
    {
        $files = $this->collectFiles($path);
        $directories = $this->collectDirectories($path);
        $totalSize = $this->calculateTotalSize($files);
        $extensions = $this->detectLanguages($files);
        $hasReadme = $this->detectReadme($files);
        $hasDocs = $this->detectDocs($path);
        $testFiles = $this->detectTests($files);
        $dependencyFiles = $this->detectDependencyFiles($path);
        $stackSignals = $this->detectStack($path, $dependencyFiles);

        return [
            'total_files' => $files->count(),
            'total_directories' => $directories->count(),
            'total_size_bytes' => $totalSize,
            'total_size_human' => $this->formatBytes($totalSize),
            'languages' => $extensions,
            'language_distribution' => $this->calculateLanguageDistribution($extensions),
            'has_readme' => $hasReadme,
            'has_docs' => $hasDocs,

            'test_files_count' => $testFiles,
            'test_ratio' => $this->calculateTestRatio(
                $testFiles,
                $files->count()
            ),
            'dependency_files' => $dependencyFiles,
            'stack_signals' => $stackSignals,
            'directory_tree' => $this->buildDirectoryTree($path),
            'max_directory_depth' => $this->calculateMaxDepth($path),
            'avg_files_per_directory' => $this->calculateAvgFilesPerDirectory(
                $files->count(),
                $directories->count()
            ),
            'largest_directories' => $this->findLargestDirectories($path),
            'largest_files' => $this->findLargestFiles($files),
            'python_metrics' => $this->pythonAnalyzer->analyze($path),
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
        $extensions = collect(RepositoryStack::cases())
            ->flatMap(fn ($stack) => $stack->extensions())
            ->unique()
            ->values()
            ->toArray();

        return $files
            ->map(fn ($file) => strtolower($file->getExtension()))
            ->filter(fn ($ext) => in_array($ext, $extensions))
            ->countBy()
            ->sortDesc()
            ->toArray();
    }

    private function detectReadme($files): bool
    {
        return $files->contains(fn ($file) => Str::startsWith(strtolower($file->getFilename()), 'readme'));
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
        return $this->stackDetector->detect($path, $dependencyFiles);
    }

    private function buildDirectoryTree(string $path): array
    {
        $ignored = [
            '.git',
            'vendor',
            'node_modules',
            'storage',
            'bootstrap/cache',
        ];

        return $this->scanDirectory($path, $ignored);
    }

    private function scanDirectory(string $path, array $ignored, int $depth = 0): array
    {
        if ($depth > 3) {
            return [];
        }

        $tree = [];

        foreach (File::directories($path) as $directory) {

            $name = basename($directory);

            if (in_array($name, $ignored)) {
                continue;
            }

            $tree[$name] = $this->scanDirectory($directory, $ignored, $depth + 1);
        }

        return $tree;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    private function calculateLanguageDistribution(array $languages): array
    {
        $distribution = [];

        $totalLanguageFiles = array_sum($languages);

        foreach ($languages as $language => $count) {
            $distribution[] = [
                'language' => $language,
                'percent' => round(($count / max($totalLanguageFiles, 1)) * 100, 2),
            ];
        }

        usort($distribution, fn ($a, $b) => $b['percent'] <=> $a['percent']);

        return $distribution;
    }

    private function calculateTestRatio(int $testFiles, int $totalFiles): float
    {
        return round(($testFiles / max($totalFiles, 1)) * 100, 2);
    }

    private function calculateAvgFilesPerDirectory(int $files, int $directories): float
    {
        return round($files / max($directories, 1), 2);
    }

    private function calculateMaxDepth(string $path, int $depth = 0): int
    {
        if (! File::isDirectory($path)) {
            return $depth;
        }

        $ignored = [
            '.git',
            'node_modules',
            'vendor',
            'storage',
            'bootstrap/cache',
            'public/build',
            'fonts',
        ];

        $maxDepth = $depth;

        try {

            foreach (File::directories($path) as $directory) {

                $name = basename($directory);

                if (in_array($name, $ignored, true)) {
                    continue;
                }

                if (! File::isDirectory($directory)) {
                    continue;
                }

                $maxDepth = max(
                    $maxDepth,
                    $this->calculateMaxDepth($directory, $depth + 1)
                );
            }

        } catch (\Throwable) {
            return $maxDepth;
        }

        return $maxDepth;
    }

    private function findLargestDirectories(string $path): array
    {
        $directories = [];

        foreach (File::directories($path) as $directory) {
            $name = basename($directory);
            $count = count(File::allFiles($directory));
            $directories[$name] = $count;
        }

        arsort($directories);

        return array_slice($directories, 0, 5, true);
    }

    private function findLargestFiles($files): array
    {
        $extensions = collect(RepositoryStack::cases())
            ->flatMap(fn ($stack) => $stack->extensions())
            ->unique()
            ->toArray();

        $largest = [];

        foreach ($files as $file) {

            $ext = strtolower($file->getExtension());

            if (! in_array($ext, $extensions, true)) {
                continue;
            }

            try {
                $lines = count(file($file->getPathname()));
            } catch (\Throwable) {
                $lines = 0;
            }

            $largest[$file->getFilename()] = $lines;
        }

        arsort($largest);

        return array_slice($largest, 0, 5, true);
    }
}
