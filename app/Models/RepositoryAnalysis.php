<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepositoryAnalysis extends Model
{
    protected $fillable = [
        'repository_url',
        'repository_name',
        'owner',
        'slug',
        'metrics',
    ];

    protected $casts = [
        'metrics' => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
