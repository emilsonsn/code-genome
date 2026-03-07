<?php

namespace App\Infrastructure\Score;

class RepositoryScoreCalculator
{
    public function calculate(array $metrics): array
    {
        $documentation = 0;

        if ($metrics['has_readme']) {
            $documentation += 70;
        }

        if ($metrics['has_docs']) {
            $documentation += 30;
        }

        $tests = min(100, (int) round(
            ($metrics['test_files_count'] / max($metrics['total_files'], 1)) * 1000
        ));

        $maintenance = 0;

        if ($metrics['has_readme']) {
            $maintenance += 30;
        }

        if ($metrics['test_files_count'] > 0) {
            $maintenance += 40;
        }

        if ($metrics['total_files'] > 100) {
            $maintenance += 30;
        }

        return [
            'documentation' => $documentation,
            'tests' => $tests,
            'maintenance' => min(100, $maintenance),
        ];
    }
}
