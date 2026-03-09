<?php

namespace App\Http\Controllers;

use App\Models\RepositoryAnalysis;
use App\Services\RepositoryAnalyzerService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Throwable;

class RepositoryAnalysisController extends Controller
{
    public function index(): View
    {
        return view('repository-analyses.index');
    }

    public function genome(): View
    {
        $analyses = RepositoryAnalysis::query()
            ->latest()
            ->get()
            ->map(function (RepositoryAnalysis $analysis) {
                $scores = $analysis->metrics['scores'] ?? [];
                $github = $analysis->metrics['github'] ?? [];

                return [
                    'id' => $analysis->id,
                    'repository_name' => $analysis->repository_name,
                    'owner' => $analysis->owner,
                    'overall' => $scores['overall'] ?? 0,
                    'grade' => $scores['grade_label'] ?? 'Unknown',
                    'grade_color' => $scores['grade_color'] ?? 'indigo',
                    'documentation' => $scores['documentation'] ?? 0,
                    'tests' => $scores['tests'] ?? 0,
                    'structure' => $scores['structure'] ?? 0,
                    'size' => $scores['size'] ?? 0,
                    'maintainability' => $scores['maintainability'] ?? 0,
                    'stars' => $github['stars'] ?? 0,
                    'contributors_count' => $github['contributors_count'] ?? 0,
                    'url' => route('repository-analyses.show', $analysis),
                ];
            });

        return view('repository-analyses.genome', [
            'analyses' => $analyses,
        ]);
    }

    public function store(Request $request, RepositoryAnalyzerService $service)
    {
        $data = $request->validate([
            'repository_url' => ['required', 'url'],
        ]);

        try {
            $analysis = $service
                ->setRepositoryUrl($data['repository_url'])
                ->analyze()
                ->object();
        } catch (Throwable) {
            return back()
                ->withInput()
                ->withErrors([
                    'repository_url' => 'Nao foi possivel analisar este repositorio agora. Verifique a URL e tente novamente.',
                ]);
        }

        return redirect()->route('repository-analyses.show', $analysis);
    }

    public function show(RepositoryAnalysis $repositoryAnalysis): View
    {
        $scores = $repositoryAnalysis->metrics['scores'] ?? [];

        $scoreMetrics = [
            'documentation',
            'tests',
            'structure',
            'size',
            'maintainability',
        ];

        return view('repository-analyses.show', [
            'analysis' => $repositoryAnalysis,
            'scores' => $scores,
            'scoreMetrics' => $scoreMetrics,
        ]);
    }
}
