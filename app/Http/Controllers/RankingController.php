<?php

namespace App\Http\Controllers;

use App\Services\RankingService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

class RankingController extends Controller
{
    public function index(RankingService $rankingService): View
    {
        $topRepositories = $rankingService->getTopRepositories();
        $developerRankings = $rankingService->getDeveloperRankings();

        $lastCacheUpdate = Cache::get('ranking:cache_timestamp', now());

        return view('repository-analyses.ranking', [
            'topRepositories' => $topRepositories,
            'developerRankings' => $developerRankings,
            'lastCacheUpdate' => $lastCacheUpdate,
        ]);
    }
}
