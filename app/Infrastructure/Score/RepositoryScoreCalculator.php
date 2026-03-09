<?php

namespace App\Infrastructure\Score;

use App\Enums\RepositoryGrade;

class RepositoryScoreCalculator
{
    public function calculate(array $metrics): array
    {
        $documentation = $this->documentationScore($metrics);
        $tests = $this->testsScore($metrics);
        $structure = $this->structureScore($metrics);
        $size = $this->sizeScore($metrics);

        $maintainability = (int) round(
            $documentation * 0.4 +
            $tests * 0.2 +
            $structure * 0.4
        );

        $overall = (int) round(
            $documentation * 0.25 +
            $tests * 0.2 +
            $structure * 0.25 +
            $size * 0.1 +
            $maintainability * 0.2
        );

        $grade = RepositoryGrade::fromScore($overall);

        return [
            'documentation' => $documentation,
            'tests' => $tests,
            'structure' => $structure,
            'size' => $size,
            'maintainability' => $maintainability,
            'overall' => $overall,
            'grade' => $grade->value,
            'grade_label' => $grade->label(),
            'grade_color' => $grade->color(),
        ];
    }

    private function documentationScore(array $metrics): int
    {
        $score = 0;

        if ($metrics['has_readme']) {
            $score += 70;
        }

        if ($metrics['has_docs']) {
            $score += 20;
        }

        if (($metrics['max_directory_depth'] ?? 0) > 2) {
            $score += 10;
        }

        return min(100, $score);
    }

    private function testsScore(array $metrics): int
    {
        $tests = $metrics['test_files_count'] ?? 0;
        $total = $metrics['total_files'] ?? 0;

        if ($total === 0) {
            return 10;
        }

        $ratio = ($tests / $total) * 100;

        return min(
            100,
            (int) round($ratio * 8 + 10)
        );
    }

    private function structureScore(array $metrics): int
    {
        $depth = $metrics['max_directory_depth'] ?? 0;
        $avgFiles = $metrics['avg_files_per_directory'] ?? 0;

        $score = 0;

        if ($depth >= 3) {
            $score += 40;
        }

        if ($depth <= 10) {
            $score += 20;
        }

        if ($avgFiles > 0 && $avgFiles <= 80) {
            $score += 40;
        }

        return min(100, $score);
    }

    private function sizeScore(array $metrics): int
    {
        $files = $metrics['total_files'] ?? 0;

        if ($files < 20) {
            return 20;
        }

        if ($files < 100) {
            return 60;
        }

        if ($files < 2000) {
            return 100;
        }

        return 70;
    }
}
