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
        return view('repository-analyses.show', [
            'analysis' => $repositoryAnalysis,
        ]);
    }
}
