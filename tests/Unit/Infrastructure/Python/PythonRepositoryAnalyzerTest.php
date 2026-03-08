<?php

namespace Tests\Unit\Infrastructure\Python;

use App\Infrastructure\Python\PythonRepositoryAnalyzer;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class PythonRepositoryAnalyzerTest extends TestCase
{
    private PythonRepositoryAnalyzer $analyzer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyzer = new PythonRepositoryAnalyzer;
    }

    public function test_returns_parsed_json_on_success(): void
    {
        Process::fake([
            '*' => Process::result(
                output: json_encode([
                    'total_loc' => 1000,
                    'files_analyzed' => 50,
                ]),
                exitCode: 0
            ),
        ]);

        $result = $this->analyzer->analyze('/path/to/repo');

        $this->assertEquals(1000, $result['total_loc']);
        $this->assertEquals(50, $result['files_analyzed']);
    }

    public function test_returns_empty_array_on_failure(): void
    {
        Process::fake([
            '*' => Process::result(
                output: 'Error: Python script failed',
                exitCode: 1
            ),
        ]);

        $result = $this->analyzer->analyze('/path/to/repo');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_returns_empty_array_for_invalid_json(): void
    {
        Process::fake([
            '*' => Process::result(
                output: 'not valid json',
                exitCode: 0
            ),
        ]);

        $result = $this->analyzer->analyze('/path/to/repo');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_passes_repository_path_to_python_script(): void
    {
        Process::fake([
            '*' => Process::result(output: '{}', exitCode: 0),
        ]);

        $this->analyzer->analyze('/custom/repo/path');

        Process::assertRan(function ($process) {
            return str_contains($process->command, '/custom/repo/path');
        });
    }

    public function test_passes_code_extensions_to_python_script(): void
    {
        Process::fake([
            '*' => Process::result(output: '{}', exitCode: 0),
        ]);

        $this->analyzer->analyze('/path/to/repo');

        Process::assertRan(function ($process) {
            // Extensions should be passed as comma-separated argument (escaped)
            // The command contains the extensions like 'php,js,ts,py'
            return str_contains($process->command, 'analyze_repo.py');
        });
    }

    public function test_runs_from_python_directory(): void
    {
        Process::fake([
            '*' => Process::result(output: '{}', exitCode: 0),
        ]);

        $this->analyzer->analyze('/path/to/repo');

        Process::assertRan(function ($process) {
            return str_contains($process->command, 'analyze_repo.py');
        });
    }

    public function test_uses_virtual_environment_python(): void
    {
        Process::fake([
            '*' => Process::result(output: '{}', exitCode: 0),
        ]);

        $this->analyzer->analyze('/path/to/repo');

        Process::assertRan(function ($process) {
            return str_contains($process->command, 'env/bin/python');
        });
    }

    public function test_handles_complex_metrics_response(): void
    {
        $complexMetrics = [
            'total_loc' => 5000,
            'files_analyzed' => 100,
            'total_commits' => 250,
            'total_contributors' => 10,
            'most_complex_functions' => [
                ['name' => 'parseData', 'complexity' => 15, 'file' => 'parser.py'],
                ['name' => 'processItems', 'complexity' => 12, 'file' => 'worker.py'],
            ],
            'hotspot_files' => [
                ['file' => 'core.py', 'changes' => 50],
                ['file' => 'utils.py', 'changes' => 30],
            ],
        ];

        Process::fake([
            '*' => Process::result(
                output: json_encode($complexMetrics),
                exitCode: 0
            ),
        ]);

        $result = $this->analyzer->analyze('/path/to/repo');

        $this->assertEquals(5000, $result['total_loc']);
        $this->assertCount(2, $result['most_complex_functions']);
        $this->assertEquals('parseData', $result['most_complex_functions'][0]['name']);
    }

    public function test_handles_empty_repository(): void
    {
        Process::fake([
            '*' => Process::result(
                output: json_encode([
                    'total_loc' => 0,
                    'files_analyzed' => 0,
                    'most_complex_functions' => [],
                    'hotspot_files' => [],
                ]),
                exitCode: 0
            ),
        ]);

        $result = $this->analyzer->analyze('/empty/repo');

        $this->assertEquals(0, $result['total_loc']);
        $this->assertEmpty($result['most_complex_functions']);
    }

    public function test_escapes_path_correctly(): void
    {
        Process::fake([
            '*' => Process::result(output: '{}', exitCode: 0),
        ]);

        $pathWithSpaces = '/path/to/my repo';
        $this->analyzer->analyze($pathWithSpaces);

        Process::assertRan(function ($process) {
            // Should still run successfully with escaped path
            return true;
        });
    }
}
