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

    public function testReturnsArrayWithAllScoreKeys(): void
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
        $this->assertArrayHasKey('maintainability', $result);
    }

    public function testCalculatesZeroDocumentationScoreWithoutReadmeAndDocs(): void
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

    public function testCalculates70DocumentationScoreWithOnlyReadme(): void
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

    public function testCalculates20DocumentationScoreWithOnlyDocs(): void
    {
        $metrics = [
            'has_readme' => false,
            'has_docs' => true,
            'test_files_count' => 0,
            'total_files' => 10,
        ];

        $result = $this->calculator->calculate($metrics);

        $this->assertEquals(20, $result['documentation']);
    }

    public function testCalculates90DocumentationScoreWithReadmeAndDocs(): void
    {
        $metrics = [
            'has_readme' => true,
            'has_docs' => true,
            'test_files_count' => 0,
            'total_files' => 10,
        ];

        $result = $this->calculator->calculate($metrics);

        $this->assertEquals(90, $result['documentation']);
    }

    public function testCalculates10TestsScoreWithoutTestFiles(): void
    {
        $metrics = [
            'has_readme' => false,
            'has_docs' => false,
            'test_files_count' => 0,
            'total_files' => 100,
        ];

        $result = $this->calculator->calculate($metrics);

        $this->assertEquals(10, $result['tests']);
    }

    public function testCalculatesTestsScoreBasedOnRatio(): void
    {
        $metrics = [
            'has_readme' => false,
            'has_docs' => false,
            'test_files_count' => 5,
            'total_files' => 100,
        ];

        $result = $this->calculator->calculate($metrics);

        $this->assertEquals(50, $result['tests']);
    }

    public function testCapsTestsScoreAt100(): void
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

    public function testCalculatesMaintainabilityScore(): void
    {
        $metrics = [
            'has_readme' => true,
            'has_docs' => true,
            'test_files_count' => 10,
            'total_files' => 100,
            'max_directory_depth' => 3,
            'avg_files_per_directory' => 10,
        ];

        $result = $this->calculator->calculate($metrics);

        $this->assertIsInt($result['maintainability']);
        $this->assertGreaterThanOrEqual(0, $result['maintainability']);
        $this->assertLessThanOrEqual(100, $result['maintainability']);
    }

    public function testHandlesZeroTotalFiles(): void
    {
        $metrics = [
            'has_readme' => false,
            'has_docs' => false,
            'test_files_count' => 0,
            'total_files' => 0,
        ];

        $result = $this->calculator->calculate($metrics);

        $this->assertEquals(10, $result['tests']);
    }

    public function testReturnsIntegerScores(): void
    {
        $metrics = [
            'has_readme' => true,
            'has_docs' => true,
            'test_files_count' => 33,
            'total_files' => 100,
            'max_directory_depth' => 4,
            'avg_files_per_directory' => 12,
        ];

        $result = $this->calculator->calculate($metrics);

        $this->assertIsInt($result['documentation']);
        $this->assertIsInt($result['tests']);
        $this->assertIsInt($result['maintainability']);
    }
}
