<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepositoryAnalysis extends Model
{
    protected $fillable = [
        'repository_url',
        'repository_name',
        'owner',
        'metrics',
    ];

    protected $casts = [
        'metrics' => 'array',
    ];
}
