<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code Genome - Ranking</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite([
        'resources/css/ranking.css',
        'resources/js/ranking.js',
    ])
</head>
<body class="bg-slate-950 text-white">
    <a href="{{ route('repository-analyses.index') }}" class="floating-back-button-ranking" title="Voltar">
        <i class="fa-solid fa-arrow-left"></i>
    </a>

    <div class="dna-background"></div>

    <div class="ranking-container">
        <div class="ranking-header">
            <h1 class="text-5xl font-bold mb-2">🧬 Ranking de Genomas</h1>
            <p class="text-slate-300 text-lg">Descubra os repositórios mais saudáveis</p>
        </div>

        <div class="tabs-container">
            <div class="tabs">
                <button class="tab-button active" data-tab="repositories">
                    <i class="fa-solid fa-star"></i> Repositórios Mais Bem Avaliados
                </button>
                <button class="tab-button" data-tab="developers">
                    <i class="fa-solid fa-users"></i> Desenvolvedores Topo
                </button>
            </div>
        </div>

        <div id="repositories-tab" class="tab-content active">
            <div class="rankings-grid">
                @forelse($topRepositories as $index => $repo)
                    <div class="ranking-card repository-card" style="animation-delay: {{ ($index % 3) * 0.1 }}s">
                        <div class="position-badge">
                            @if($index < 3)
                                @if($index === 0)
                                    <i class="fa-solid fa-crown"></i> #1
                                @elseif($index === 1)
                                    <i class="fa-solid fa-medal"></i> #2
                                @else
                                    <i class="fa-solid fa-trophy"></i> #3
                                @endif
                            @else
                                #{{ $index + 1 }}
                            @endif
                        </div>

                        <div class="dna-helix"></div>

                        <div class="card-content">
                            <div class="repo-header">
                                <h3 class="repo-name">{{ $repo['repository_name'] }}</h3>
                                <span class="repo-owner">by {{ $repo['owner'] }}</span>
                            </div>

                            <div class="score-display">
                                <div class="score-circle grade-{{ $repo['grade_color'] }}">
                                    <span class="score-value">{{ number_format($repo['overall'], 1) }}</span>
                                </div>
                                <div class="grade-label">{{ $repo['grade'] }}</div>
                            </div>

                            <div class="metrics-bars">
                                <div class="metric-bar">
                                    <div class="metric-name">Docs</div>
                                    <div class="metric-fill" style="width: {{ $repo['documentation'] }}%"></div>
                                </div>
                                <div class="metric-bar">
                                    <div class="metric-name">Tests</div>
                                    <div class="metric-fill" style="width: {{ $repo['tests'] }}%"></div>
                                </div>
                                <div class="metric-bar">
                                    <div class="metric-name">Structure</div>
                                    <div class="metric-fill" style="width: {{ $repo['structure'] }}%"></div>
                                </div>
                                <div class="metric-bar">
                                    <div class="metric-name">Maintenability</div>
                                    <div class="metric-fill" style="width: {{ $repo['maintainability'] }}%"></div>
                                </div>
                            </div>

                            <div class="repo-stats">
                                <div class="stat">
                                    <i class="fa-solid fa-star"></i>
                                    <span>{{ $repo['stars'] }}</span>
                                </div>
                                <div class="stat">
                                    <i class="fa-solid fa-users"></i>
                                    <span>{{ $repo['contributors_count'] }}</span>
                                </div>
                            </div>

                            <a href="{{ $repo['url'] }}" class="view-button">
                                Ver Análise <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="fa-solid fa-dna"></i>
                        <p>Nenhum repositório analisado ainda</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div id="developers-tab" class="tab-content">
            <div class="developers-grid">
                @forelse($developerRankings as $index => $developer)
                    <div class="developer-card" style="animation-delay: {{ ($index % 2) * 0.1 }}s">
                        <div class="dev-position-badge">
                            @if($index < 3)
                                @if($index === 0)
                                    <i class="fa-solid fa-crown"></i>
                                @elseif($index === 1)
                                    <i class="fa-solid fa-medal"></i>
                                @else
                                    <i class="fa-solid fa-trophy"></i>
                                @endif
                            @else
                                {{ $index + 1 }}
                            @endif
                        </div>

                        <div class="dna-strands"></div>

                        <div class="dev-content">
                            <h3 class="dev-name">{{ $developer['owner'] }}</h3>
                            
                            <div class="dev-score">
                                <div class="score-label">Score Médio</div>
                                <div class="score-big">{{ $developer['average_score'] }}</div>
                            </div>

                            <div class="repo-count">
                                <i class="fa-solid fa-folder"></i>
                                {{ $developer['repository_count'] }} repositório(s)
                            </div>

                            <div class="dev-repos-list">
                                @foreach($developer['repositories'] as $repo)
                                    <a href="{{ $repo['url'] }}" class="dev-repo-item">
                                        <span class="dev-repo-name">{{ $repo['name'] }}</span>
                                        <span class="dev-repo-score">{{ number_format($repo['score'], 1) }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="fa-solid fa-dna"></i>
                        <p>Nenhum desenvolvedor registrado ainda</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</body>
</html>
