<?php

namespace App\Infrastructure\Stack;

use App\Enums\RepositoryStack;
use Illuminate\Support\Facades\File;

class StackDetector
{
    public function detect(string $path, array $dependencyFiles): array
    {
        $result = [];

        foreach (RepositoryStack::cases() as $stack) {

            $enabled = $this->{$stack->detectorMethod()}($path, $dependencyFiles);

            $result[] = [
                'key' => $stack->value,
                'label' => $stack->label(),
                'icon' => $stack->icon(),
                'enabled' => $enabled,
            ];
        }

        return $result;
    }

    private function detectLaravel(string $path): bool
    {
        return File::exists($path.'/artisan')
            && File::exists($path.'/routes/web.php');
    }

    private function detectSymfony(string $path): bool
    {
        return File::exists($path.'/bin/console');
    }

    private function detectReact(string $path): bool
    {
        return $this->packageJsonHas($path, 'react');
    }

    private function detectVue(string $path): bool
    {
        return $this->packageJsonHas($path, 'vue');
    }

    private function detectAngular(string $path): bool
    {
        return File::exists($path.'/angular.json');
    }

    private function detectNode(string $path, array $deps): bool
    {
        return $deps['package.json'] ?? false;
    }

    private function detectPython(string $path, array $deps): bool
    {
        return ($deps['requirements.txt'] ?? false)
            || ($deps['pyproject.toml'] ?? false);
    }

    private function detectDjango(string $path): bool
    {
        return $this->fileContains($path.'/requirements.txt', 'django');
    }

    private function detectFlask(string $path): bool
    {
        return $this->fileContains($path.'/requirements.txt', 'flask');
    }

    private function detectFastApi(string $path): bool
    {
        return $this->fileContains($path.'/requirements.txt', 'fastapi');
    }

    private function detectSpring(string $path): bool
    {
        return File::exists($path.'/pom.xml');
    }

    private function detectExpress(string $path): bool
    {
        return $this->packageJsonHas($path, 'express');
    }

    private function detectNext(string $path): bool
    {
        return $this->packageJsonHas($path, 'next');
    }

    private function detectNuxt(string $path): bool
    {
        return $this->packageJsonHas($path, 'nuxt');
    }

    private function detectNest(string $path): bool
    {
        return $this->packageJsonHas($path, '@nestjs/core');
    }

    private function detectDotNet(string $path): bool
    {
        return $this->hasExtension($path, ['csproj', 'sln']);
    }

    private function detectAspNet(string $path): bool
    {
        return $this->hasExtension($path, ['csproj'])
            && $this->fileContainsAny($path, 'Microsoft.AspNetCore');
    }

    private function detectHtml(string $path): bool
    {
        return $this->hasExtension($path, ['html', 'htm']);
    }

    private function detectCss(string $path): bool
    {
        return $this->hasExtension($path, ['css', 'scss', 'sass']);
    }

    private function packageJsonHas(string $path, string $needle): bool
    {
        return $this->fileContains($path.'/package.json', $needle);
    }

    private function fileContains(string $file, string $needle): bool
    {
        if (! File::exists($file)) {
            return false;
        }

        return str_contains(strtolower(File::get($file)), strtolower($needle));
    }

    private function fileContainsAny(string $path, string $needle): bool
    {
        foreach (File::allFiles($path) as $file) {
            if ($this->fileContains($file->getPathname(), $needle)) {
                return true;
            }
        }

        return false;
    }

    private function hasExtension(string $path, array $extensions): bool
    {
        foreach (File::allFiles($path) as $file) {
            if (in_array(strtolower($file->getExtension()), $extensions)) {
                return true;
            }
        }

        return false;
    }
}
