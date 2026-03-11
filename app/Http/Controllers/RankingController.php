<?php

namespace App\Http\Controllers;

use App\Services\RankingService;
use Illuminate\Contracts\View\View;

class RankingController extends Controller
{
    public function index(RankingService $rankingService): View
    {
        $topRepositories = $rankingService->getTopRepositories();
        $developerRankings = $rankingService->getDeveloperRankings();

        return view('repository-analyses.ranking', [
            'topRepositories' => $topRepositories,
            'developerRankings' => $developerRankings,
        ]);
    }
}
