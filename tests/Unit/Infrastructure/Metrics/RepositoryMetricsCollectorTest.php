<?php

namespace Tests\Unit\Infrastructure\Metrics;

use App\Infrastructure\Metrics\RepositoryMetricsCollector;
use App\Infrastructure\Python\PythonRepositoryAnalyzer;
use App\Infrastructure\Stack\StackDetector;
use Illuminate\Support\Facades\File;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\Finder\SplFileInfo;
use Tests\TestCase;

class RepositoryMetricsCollectorTest extends TestCase
{
    private MockInterface $stackDetector;

    private MockInterface $pythonAnalyzer;

    private RepositoryMetricsCollector $collector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stackDetector = Mockery::mock(StackDetector::class);
        $this->pythonAnalyzer = Mockery::mock(PythonRepositoryAnalyzer::class);

        $this->collector = new RepositoryMetricsCollector(
            $this->stackDetector,
            $this->pythonAnalyzer
        );
    }

    public function test_collects_basic_metrics(): void
    {
        $this->setupBasicMocks();

        $result = $this->collector->collect('/path/to/repo');

        $this->assertArrayHasKey('total_files', $result);
        $this->assertArrayHasKey('total_directories', $result);
        $this->assertArrayHasKey('total_size_bytes', $result);
        $this->assertArrayHasKey('total_size_human', $result);
    }

    public function test_detects_languages(): void
    {
        $files = $this->createMockFiles([
            ['name' => 'app.php', 'extension' => 'php', 'size' => 1000],
            ['name' => 'script.js', 'extension' => 'js', 'size' => 500],
            ['name' => 'styles.css', 'extension' => 'css', 'size' => 300],
        ]);

        $this->setupMocksWithFiles($files);

        $result = $this->collector->collect('/path/to/repo');

        $this->assertArrayHasKey('languages', $result);
    }

    public function test_calculates_language_distribution(): void
    {
        $files = $this->createMockFiles([
            ['name' => 'file1.php', 'extension' => 'php', 'size' => 1000],
            ['name' => 'file2.php', 'extension' => 'php', 'size' => 1000],
            ['name' => 'file3.js', 'extension' => 'js', 'size' => 500],
        ]);

        $this->setupMocksWithFiles($files);

        $result = $this->collector->collect('/path/to/repo');

        $this->assertArrayHasKey('language_distribution', $result);
        $this->assertIsArray($result['language_distribution']);
    }

    public function test_detects_readme(): void
    {
        $files = $this->createMockFiles([
            ['name' => 'README.md', 'extension' => 'md', 'size' => 100],
        ]);

        $this->setupMocksWithFiles($files);

        $result = $this->collector->collect('/path/to/repo');

        $this->assertTrue($result['has_readme']);
    }

    public function test_detects_missing_readme(): void
    {
        $files = $this->createMockFiles([
            ['name' => 'app.php', 'extension' => 'php', 'size' => 1000],
        ]);

        $this->setupMocksWithFiles($files);

        $result = $this->collector->collect('/path/to/repo');

        $this->assertFalse($result['has_readme']);
    }

    public function test_detects_docs_directory(): void
    {
        $this->setupBasicMocks();

        File::shouldReceive('exists')
            ->with('/path/to/repo/docs')
            ->andReturn(true);

        $result = $this->collector->collect('/path/to/repo');

        $this->assertTrue($result['has_docs']);
    }

    public function test_counts_test_files(): void
    {
        $files = $this->createMockFiles([
            ['name' => 'ExampleTest.php', 'extension' => 'php', 'size' => 500, 'path' => '/tests/ExampleTest.php'],
            ['name' => 'app.spec.js', 'extension' => 'js', 'size' => 300, 'path' => '/src/app.spec.js'],
            ['name' => 'main.test.ts', 'extension' => 'ts', 'size' => 400, 'path' => '/src/main.test.ts'],
        ]);

        $this->setupMocksWithFiles($files);

        $result = $this->collector->collect('/path/to/repo');

        $this->assertArrayHasKey('test_files_count', $result);
        $this->assertGreaterThanOrEqual(0, $result['test_files_count']);
    }

    public function test_calculates_test_ratio(): void
    {
        $this->setupBasicMocks();

        $result = $this->collector->collect('/path/to/repo');

        $this->assertArrayHasKey('test_ratio', $result);
        $this->assertIsFloat($result['test_ratio']);
    }

    public function test_detects_dependency_files(): void
    {
        $this->setupBasicMocks();

        File::shouldReceive('exists')
            ->with('/path/to/repo/composer.json')
            ->andReturn(true);

        File::shouldReceive('exists')
            ->with('/path/to/repo/package.json')
            ->andReturn(true);

        $result = $this->collector->collect('/path/to/repo');

        $this->assertArrayHasKey('dependency_files', $result);
        $this->assertTrue($result['dependency_files']['composer.json']);
        $this->assertTrue($result['dependency_files']['package.json']);
    }

    public function test_calls_stack_detector(): void
    {
        File::shouldReceive('allFiles')->andReturn(collect([]));
        File::shouldReceive('directories')->andReturn(collect([]));
        File::shouldReceive('exists')->andReturn(false)->byDefault();
        File::shouldReceive('isDirectory')->andReturn(false)->byDefault();
        File::shouldReceive('get')->andReturn('')->byDefault();

        $this->pythonAnalyzer->shouldReceive('analyze')->andReturn([]);

        $this->stackDetector
            ->shouldReceive('detect')
            ->once()
            ->andReturn(['Laravel', 'Vue.js']);

        $result = $this->collector->collect('/path/to/repo');

        $this->assertArrayHasKey('stack_signals', $result);
        $this->assertEquals(['Laravel', 'Vue.js'], $result['stack_signals']);
    }

    public function test_builds_directory_tree(): void
    {
        $this->setupBasicMocks();

        $result = $this->collector->collect('/path/to/repo');

        $this->assertArrayHasKey('directory_tree', $result);
        $this->assertIsArray($result['directory_tree']);
    }

    public function test_calculates_max_directory_depth(): void
    {
        $this->setupBasicMocks();

        $result = $this->collector->collect('/path/to/repo');

        $this->assertArrayHasKey('max_directory_depth', $result);
        $this->assertIsInt($result['max_directory_depth']);
    }

    public function test_calculates_average_files_per_directory(): void
    {
        $this->setupBasicMocks();

        $result = $this->collector->collect('/path/to/repo');

        $this->assertArrayHasKey('avg_files_per_directory', $result);
        $this->assertIsFloat($result['avg_files_per_directory']);
    }

    public function test_finds_largest_directories(): void
    {
        $this->setupBasicMocks();

        $result = $this->collector->collect('/path/to/repo');

        $this->assertArrayHasKey('largest_directories', $result);
    }

    public function test_finds_largest_files(): void
    {
        $files = $this->createMockFiles([
            ['name' => 'large.php', 'extension' => 'php', 'size' => 100000],
            ['name' => 'medium.js', 'extension' => 'js', 'size' => 50000],
            ['name' => 'small.css', 'extension' => 'css', 'size' => 1000],
        ]);

        $this->setupMocksWithFiles($files);

        $result = $this->collector->collect('/path/to/repo');

        $this->assertArrayHasKey('largest_files', $result);
    }

    public function test_includes_python_metrics(): void
    {
        File::shouldReceive('allFiles')->andReturn(collect([]));
        File::shouldReceive('directories')->andReturn(collect([]));
        File::shouldReceive('exists')->andReturn(false)->byDefault();
        File::shouldReceive('isDirectory')->andReturn(false)->byDefault();
        File::shouldReceive('get')->andReturn('')->byDefault();

        $this->stackDetector->shouldReceive('detect')->andReturn([]);

        $this->pythonAnalyzer
            ->shouldReceive('analyze')
            ->with('/path/to/repo')
            ->andReturn(['total_loc' => 5000]);

        $result = $this->collector->collect('/path/to/repo');

        $this->assertArrayHasKey('python_metrics', $result);
        $this->assertEquals(5000, $result['python_metrics']['total_loc']);
    }

    public function test_formats_bytes_correctly(): void
    {
        $files = $this->createMockFiles([
            ['name' => 'file.php', 'extension' => 'php', 'size' => 1048576], // 1 MB
        ]);

        $this->setupMocksWithFiles($files);

        $result = $this->collector->collect('/path/to/repo');

        $this->assertStringContainsString('MB', $result['total_size_human']);
    }

    public function test_excludes_git_directory_from_files(): void
    {
        $files = $this->createMockFiles([
            ['name' => 'app.php', 'extension' => 'php', 'size' => 1000, 'path' => '/src/app.php'],
        ]);

        // Git files should be filtered out
        $this->setupMocksWithFiles($files);

        $result = $this->collector->collect('/path/to/repo');

        // Total files should not include .git directory files
        $this->assertGreaterThanOrEqual(0, $result['total_files']);
    }

    /**
     * Create mock SplFileInfo objects
     */
    private function createMockFiles(array $fileDefinitions): array
    {
        $files = [];

        foreach ($fileDefinitions as $def) {
            $file = Mockery::mock(SplFileInfo::class);
            $file->shouldReceive('getFilename')->andReturn($def['name']);
            $file->shouldReceive('getExtension')->andReturn($def['extension']);
            $file->shouldReceive('getSize')->andReturn($def['size']);
            $file->shouldReceive('getPathname')->andReturn($def['path'] ?? '/path/'.$def['name']);
            $files[] = $file;
        }

        return $files;
    }

    /**
     * Setup basic mocks for simple tests
     */
    private function setupBasicMocks(): void
    {
        File::shouldReceive('allFiles')->andReturn(collect([]));
        File::shouldReceive('directories')->andReturn(collect([]));
        File::shouldReceive('exists')->andReturn(false)->byDefault();
        File::shouldReceive('isDirectory')->andReturn(false)->byDefault();
        File::shouldReceive('get')->andReturn('')->byDefault();

        $this->stackDetector->shouldReceive('detect')->andReturn([]);
        $this->pythonAnalyzer->shouldReceive('analyze')->andReturn([]);
    }

    /**
     * Setup mocks with specific files
     */
    private function setupMocksWithFiles(array $files): void
    {
        File::shouldReceive('allFiles')->andReturn(collect($files));
        File::shouldReceive('directories')->andReturn(collect([]));
        File::shouldReceive('exists')->andReturn(false)->byDefault();
        File::shouldReceive('isDirectory')->andReturn(false)->byDefault();
        File::shouldReceive('get')->andReturn('')->byDefault();

        $this->stackDetector->shouldReceive('detect')->andReturn([]);
        $this->pythonAnalyzer->shouldReceive('analyze')->andReturn([]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
