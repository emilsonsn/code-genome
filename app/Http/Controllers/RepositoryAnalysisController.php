<?php

namespace App\Http\Controllers;

use App\Models\RepositoryAnalysis;
use App\Services\RepositoryAnalyzerService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RepositoryAnalysisController extends Controller
{
    public function index(): View
    {
        return view('repository-analyses.index');
    }

    public function store(Request $request, RepositoryAnalyzerService $service)
    {
        $data = $request->validate([
            'repository_url' => ['required', 'url'],
        ]);

        $analysis = $service
            ->setRepositoryUrl($data['repository_url'])
            ->analyze()
            ->object();

        return redirect()->route('repository-analyses.show', $analysis);
    }

    public function show(RepositoryAnalysis $repositoryAnalysis): View
    {
        return view('repository-analyses.show', [
            'analysis' => $repositoryAnalysis,
        ]);
    }
}
