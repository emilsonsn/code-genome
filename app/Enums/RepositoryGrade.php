<?php

namespace App\Enums;

enum RepositoryGrade: string
{
    case EXCELLENT = 'excellent';
    case VERY_GOOD = 'very_good';
    case GOOD = 'good';
    case NEEDS_IMPROVEMENT = 'needs_improvement';
    case POOR = 'poor';

    public static function fromScore(int $score): self
    {
        return match (true) {
            $score >= 90 => self::EXCELLENT,
            $score >= 75 => self::VERY_GOOD,
            $score >= 60 => self::GOOD,
            $score >= 40 => self::NEEDS_IMPROVEMENT,
            default => self::POOR,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::EXCELLENT => 'Excellent',
            self::VERY_GOOD => 'Very Good',
            self::GOOD => 'Good',
            self::NEEDS_IMPROVEMENT => 'Needs Improvement',
            self::POOR => 'Poor',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::EXCELLENT => 'green',
            self::VERY_GOOD => 'emerald',
            self::GOOD => 'yellow',
            self::NEEDS_IMPROVEMENT => 'orange',
            self::POOR => 'red',
        };
    }
}
