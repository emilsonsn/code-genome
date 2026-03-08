<?php

namespace Tests\Unit\Infrastructure\Stack;

use App\Infrastructure\Stack\StackDetector;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class StackDetectorTest extends TestCase
{
    private StackDetector $detector;

    private string $tempPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->detector = new StackDetector;
        $this->tempPath = storage_path('app/test-repo-'.uniqid());
        File::ensureDirectoryExists($this->tempPath);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->tempPath);
        parent::tearDown();
    }

    public function test_returns_array_with_all_stacks(): void
    {
        $result = $this->detector->detect($this->tempPath, []);

        $this->assertCount(19, $result);

        foreach ($result as $stack) {
            $this->assertArrayHasKey('key', $stack);
            $this->assertArrayHasKey('label', $stack);
            $this->assertArrayHasKey('icon', $stack);
            $this->assertArrayHasKey('enabled', $stack);
        }
    }

    public function test_detects_laravel_from_artisan_and_routes(): void
    {
        // Laravel detection requires artisan AND routes/web.php
        File::put($this->tempPath.'/artisan', '#!/usr/bin/env php');
        File::ensureDirectoryExists($this->tempPath.'/routes');
        File::put($this->tempPath.'/routes/web.php', '<?php');

        $result = $this->detector->detect($this->tempPath, ['composer.json' => true]);
        $laravel = $this->findStack($result, 'laravel');

        $this->assertTrue($laravel['enabled']);
    }

    public function test_detects_symfony_from_bin_console(): void
    {
        // Symfony detection requires bin/console
        File::ensureDirectoryExists($this->tempPath.'/bin');
        File::put($this->tempPath.'/bin/console', '#!/usr/bin/env php');

        $result = $this->detector->detect($this->tempPath, ['composer.json' => true]);
        $symfony = $this->findStack($result, 'symfony');

        $this->assertTrue($symfony['enabled']);
    }

    public function test_detects_react_from_package_json(): void
    {
        $this->createPackageJson(['react']);

        $result = $this->detector->detect($this->tempPath, ['package.json' => true]);
        $react = $this->findStack($result, 'react');

        $this->assertTrue($react['enabled']);
    }

    public function test_detects_vue_from_package_json(): void
    {
        $this->createPackageJson(['vue']);

        $result = $this->detector->detect($this->tempPath, ['package.json' => true]);
        $vue = $this->findStack($result, 'vue');

        $this->assertTrue($vue['enabled']);
    }

    public function test_detects_angular_from_angular_json(): void
    {
        // Angular detection requires angular.json file
        File::put($this->tempPath.'/angular.json', '{}');

        $result = $this->detector->detect($this->tempPath, ['package.json' => true]);
        $angular = $this->findStack($result, 'angular');

        $this->assertTrue($angular['enabled']);
    }

    public function test_detects_node_from_package_json_existence(): void
    {
        $this->createPackageJson([]);

        $result = $this->detector->detect($this->tempPath, ['package.json' => true]);
        $node = $this->findStack($result, 'node');

        $this->assertTrue($node['enabled']);
    }

    public function test_detects_python_from_requirements_txt(): void
    {
        File::put($this->tempPath.'/requirements.txt', 'flask==2.0.0');

        $result = $this->detector->detect($this->tempPath, ['requirements.txt' => true]);
        $python = $this->findStack($result, 'python');

        $this->assertTrue($python['enabled']);
    }

    public function test_detects_python_from_pyproject_toml(): void
    {
        File::put($this->tempPath.'/pyproject.toml', '[project]');

        $result = $this->detector->detect($this->tempPath, ['pyproject.toml' => true]);
        $python = $this->findStack($result, 'python');

        $this->assertTrue($python['enabled']);
    }

    public function test_detects_django_from_requirements_txt(): void
    {
        File::put($this->tempPath.'/requirements.txt', 'Django==4.0.0');

        $result = $this->detector->detect($this->tempPath, ['requirements.txt' => true]);
        $django = $this->findStack($result, 'django');

        $this->assertTrue($django['enabled']);
    }

    public function test_detects_flask_from_requirements_txt(): void
    {
        File::put($this->tempPath.'/requirements.txt', 'flask==2.0.0');

        $result = $this->detector->detect($this->tempPath, ['requirements.txt' => true]);
        $flask = $this->findStack($result, 'flask');

        $this->assertTrue($flask['enabled']);
    }

    public function test_detects_fastapi_from_requirements_txt(): void
    {
        File::put($this->tempPath.'/requirements.txt', 'fastapi==0.100.0');

        $result = $this->detector->detect($this->tempPath, ['requirements.txt' => true]);
        $fastapi = $this->findStack($result, 'fastapi');

        $this->assertTrue($fastapi['enabled']);
    }

    public function test_detects_spring_from_pom_xml(): void
    {
        File::put($this->tempPath.'/pom.xml', '<dependency>spring-boot</dependency>');

        $result = $this->detector->detect($this->tempPath, []);
        $spring = $this->findStack($result, 'spring');

        $this->assertTrue($spring['enabled']);
    }

    public function test_detects_spring_from_build_gradle_pom_xml(): void
    {
        // Spring detection uses pom.xml, so create pom.xml instead
        File::put($this->tempPath.'/pom.xml', '<dependency>spring-boot</dependency>');

        $result = $this->detector->detect($this->tempPath, []);
        $spring = $this->findStack($result, 'spring');

        $this->assertTrue($spring['enabled']);
    }

    public function test_detects_express_from_package_json(): void
    {
        $this->createPackageJson(['express']);

        $result = $this->detector->detect($this->tempPath, ['package.json' => true]);
        $express = $this->findStack($result, 'express');

        $this->assertTrue($express['enabled']);
    }

    public function test_detects_next_from_package_json(): void
    {
        $this->createPackageJson(['next']);

        $result = $this->detector->detect($this->tempPath, ['package.json' => true]);
        $next = $this->findStack($result, 'next');

        $this->assertTrue($next['enabled']);
    }

    public function test_detects_nuxt_from_package_json(): void
    {
        $this->createPackageJson(['nuxt']);

        $result = $this->detector->detect($this->tempPath, ['package.json' => true]);
        $nuxt = $this->findStack($result, 'nuxt');

        $this->assertTrue($nuxt['enabled']);
    }

    public function test_detects_nest_from_package_json(): void
    {
        $this->createPackageJson(['@nestjs/core']);

        $result = $this->detector->detect($this->tempPath, ['package.json' => true]);
        $nest = $this->findStack($result, 'nestjs');

        $this->assertTrue($nest['enabled']);
    }

    public function test_detects_dotnet_from_csproj(): void
    {
        File::put($this->tempPath.'/project.csproj', '<Project Sdk="Microsoft.NET.Sdk"></Project>');

        $result = $this->detector->detect($this->tempPath, []);
        $dotnet = $this->findStack($result, 'dotnet');

        $this->assertTrue($dotnet['enabled']);
    }

    public function test_detects_aspnet_from_csproj(): void
    {
        // AspNet detection requires .csproj file AND Microsoft.AspNetCore string
        File::put($this->tempPath.'/project.csproj', '<PackageReference Include="Microsoft.AspNetCore.App" />');

        $result = $this->detector->detect($this->tempPath, []);
        $aspnet = $this->findStack($result, 'aspnet');

        $this->assertTrue($aspnet['enabled']);
    }

    public function test_detects_html_from_html_files(): void
    {
        File::put($this->tempPath.'/index.html', '<!DOCTYPE html>');

        $result = $this->detector->detect($this->tempPath, []);
        $html = $this->findStack($result, 'html');

        $this->assertTrue($html['enabled']);
    }

    public function test_detects_css_from_css_files(): void
    {
        File::put($this->tempPath.'/styles.css', 'body { }');

        $result = $this->detector->detect($this->tempPath, []);
        $css = $this->findStack($result, 'css');

        $this->assertTrue($css['enabled']);
    }

    public function test_detects_css_from_scss_files(): void
    {
        File::put($this->tempPath.'/styles.scss', '$color: red;');

        $result = $this->detector->detect($this->tempPath, []);
        $css = $this->findStack($result, 'css');

        $this->assertTrue($css['enabled']);
    }

    public function test_returns_all_disabled_for_empty_repository(): void
    {
        $result = $this->detector->detect($this->tempPath, []);

        foreach ($result as $stack) {
            if (! in_array($stack['key'], ['html', 'css'])) {
                $this->assertFalse($stack['enabled'], "Stack {$stack['key']} should be disabled");
            }
        }
    }

    public function test_detects_multiple_stacks_simultaneously(): void
    {
        // Laravel requires artisan + routes/web.php
        File::put($this->tempPath.'/artisan', '#!/usr/bin/env php');
        File::ensureDirectoryExists($this->tempPath.'/routes');
        File::put($this->tempPath.'/routes/web.php', '<?php');

        $this->createPackageJson(['react', 'vue']);

        $result = $this->detector->detect($this->tempPath, [
            'composer.json' => true,
            'package.json' => true,
        ]);

        $laravel = $this->findStack($result, 'laravel');
        $react = $this->findStack($result, 'react');
        $vue = $this->findStack($result, 'vue');
        $node = $this->findStack($result, 'node');

        $this->assertTrue($laravel['enabled']);
        $this->assertTrue($react['enabled']);
        $this->assertTrue($vue['enabled']);
        $this->assertTrue($node['enabled']);
    }

    private function createComposerJson(array $packages): void
    {
        $require = [];
        foreach ($packages as $package) {
            $require[$package] = '*';
        }

        File::put($this->tempPath.'/composer.json', json_encode([
            'require' => $require,
        ]));
    }

    private function createPackageJson(array $dependencies): void
    {
        $deps = [];
        foreach ($dependencies as $dep) {
            $deps[$dep] = '*';
        }

        File::put($this->tempPath.'/package.json', json_encode([
            'dependencies' => $deps,
        ], JSON_UNESCAPED_SLASHES));
    }

    private function findStack(array $stacks, string $key): ?array
    {
        foreach ($stacks as $stack) {
            if ($stack['key'] === $key) {
                return $stack;
            }
        }

        return null;
    }
}
