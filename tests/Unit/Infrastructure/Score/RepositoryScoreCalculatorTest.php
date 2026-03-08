<?php

namespace Tests\Unit\Infrastructure\Score;

use App\Infrastructure\Score\RepositoryScoreCalculator;
use Tests\TestCase;

class RepositoryScoreCalculatorTest extends TestCase
{
    private RepositoryScoreCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new RepositoryScoreCalculator;
    }

    public function test_returns_array_with_all_score_keys(): void
    {
        $metrics = [
            'has_readme' => false,
            'has_docs' => false,
            'test_files_count' => 0,
            'total_files' => 10,
        ];

        $result = $this->calculator->calculate($metrics);

        $this->assertArrayHasKey('documentation', $result);
        $this->assertArrayHasKey('tests', $result);
        $this->assertArrayHasKey('maintenance', $result);
    }

    public function test_calculates_zero_documentation_score_without_readme_and_docs(): void
    {
        $metrics = [
            'has_readme' => false,
            'has_docs' => false,
            'test_files_count' => 0,
            'total_files' => 10,
        ];

        $result = $this->calculator->calculate($metrics);

        $this->assertEquals(0, $result['documentation']);
    }

    public function test_calculates70_documentation_score_with_only_readme(): void
    {
        $metrics = [
            'has_readme' => true,
            'has_docs' => false,
            'test_files_count' => 0,
            'total_files' => 10,
        ];

        $result = $this->calculator->calculate($metrics);

        $this->assertEquals(70, $result['documentation']);
    }

    public function test_calculates30_documentation_score_with_only_docs(): void
    {
        $metrics = [
            'has_readme' => false,
            'has_docs' => true,
            'test_files_count' => 0,
            'total_files' => 10,
        ];

        $result = $this->calculator->calculate($metrics);

        $this->assertEquals(30, $result['documentation']);
    }

    public function test_calculates100_documentation_score_with_readme_and_docs(): void
    {
        $metrics = [
            'has_readme' => true,
            'has_docs' => true,
            'test_files_count' => 0,
            'total_files' => 10,
        ];

        $result = $this->calculator->calculate($metrics);

        $this->assertEquals(100, $result['documentation']);
    }

    public function test_calculates_zero_tests_score_without_test_files(): void
    {
        $metrics = [
            'has_readme' => false,
            'has_docs' => false,
            'test_files_count' => 0,
            'total_files' => 100,
        ];

        $result = $this->calculator->calculate($metrics);

        $this->assertEquals(0, $result['tests']);
    }

    public function test_calculates_tests_score_based_on_ratio(): void
    {
        $metrics = [
            'has_readme' => false,
            'has_docs' => false,
            'test_files_count' => 5,
            'total_files' => 100,
        ];

        $result = $this->calculator->calculate($metrics);

        // 5 / 100 * 1000 = 50
        $this->assertEquals(50, $result['tests']);
    }

    public function test_caps_tests_score_at100(): void
    {
        $metrics = [
            'has_readme' => false,
            'has_docs' => false,
            'test_files_count' => 200,
            'total_files' => 100,
        ];

        $result = $this->calculator->calculate($metrics);

        $this->assertEquals(100, $result['tests']);
    }

    public function test_calculates_zero_maintenance_score_with_no_indicators(): void
    {
        $metrics = [
            'has_readme' => false,
            'has_docs' => false,
            'test_files_count' => 0,
            'total_files' => 50,
        ];

        $result = $this->calculator->calculate($metrics);

        $this->assertEquals(0, $result['maintenance']);
    }

    public function test_adds30_to_maintenance_for_readme(): void
    {
        $metrics = [
            'has_readme' => true,
            'has_docs' => false,
            'test_files_count' => 0,
            'total_files' => 50,
        ];

        $result = $this->calculator->calculate($metrics);

        $this->assertEquals(30, $result['maintenance']);
    }

    public function test_adds40_to_maintenance_for_tests(): void
    {
        $metrics = [
            'has_readme' => false,
            'has_docs' => false,
            'test_files_count' => 5,
            'total_files' => 50,
        ];

        $result = $this->calculator->calculate($metrics);

        $this->assertEquals(40, $result['maintenance']);
    }

    public function test_adds30_to_maintenance_for_large_repositories(): void
    {
        $metrics = [
            'has_readme' => false,
            'has_docs' => false,
            'test_files_count' => 0,
            'total_files' => 150,
        ];

        $result = $this->calculator->calculate($metrics);

        $this->assertEquals(30, $result['maintenance']);
    }

    public function test_calculates100_maintenance_score_with_all_indicators(): void
    {
        $metrics = [
            'has_readme' => true,
            'has_docs' => true,
            'test_files_count' => 20,
            'total_files' => 150,
        ];

        $result = $this->calculator->calculate($metrics);

        // 30 (readme) + 40 (tests) + 30 (large repo) = 100
        $this->assertEquals(100, $result['maintenance']);
    }

    public function test_handles_zero_total_files(): void
    {
        $metrics = [
            'has_readme' => false,
            'has_docs' => false,
            'test_files_count' => 0,
            'total_files' => 0,
        ];

        $result = $this->calculator->calculate($metrics);

        $this->assertEquals(0, $result['tests']);
    }

    public function test_returns_integer_scores(): void
    {
        $metrics = [
            'has_readme' => true,
            'has_docs' => true,
            'test_files_count' => 33,
            'total_files' => 100,
        ];

        $result = $this->calculator->calculate($metrics);

        $this->assertIsInt($result['documentation']);
        $this->assertIsInt($result['tests']);
        $this->assertIsInt($result['maintenance']);
    }
}
