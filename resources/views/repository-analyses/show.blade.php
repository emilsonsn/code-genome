<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $analysis->repository_name }} - Code Genome</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-white min-h-screen">
    <div class="max-w-7xl mx-auto px-6 py-10">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="text-4xl font-bold">{{ $analysis->owner }}/{{ $analysis->repository_name }}</h1>
                <p class="text-slate-400 mt-2">{{ $analysis->repository_url }}</p>
            </div>
            <a href="{{ route('repository-analyses.index') }}" class="bg-slate-800 px-5 py-3 rounded-xl">Nova análise</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <p class="text-slate-400 text-sm">Arquivos</p>
                <p class="text-3xl font-bold mt-2">{{ $analysis->metrics['total_files'] }}</p>
            </div>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <p class="text-slate-400 text-sm">Pastas</p>
                <p class="text-3xl font-bold mt-2">{{ $analysis->metrics['total_directories'] }}</p>
            </div>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <p class="text-slate-400 text-sm">Tamanho</p>
                <p class="text-3xl font-bold mt-2">{{ $analysis->metrics['total_size_human'] }}</p>
            </div>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <p class="text-slate-400 text-sm">Testes</p>
                <p class="text-3xl font-bold mt-2">{{ $analysis->metrics['test_files_count'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <h2 class="text-2xl font-semibold mb-6">DNA do projeto</h2>

                @foreach($analysis->metrics['scores'] as $label => $value)
                    <div class="mb-5">
                        <div class="flex justify-between mb-2">
                            <span class="capitalize">{{ str_replace('_', ' ', $label) }}</span>
                            <span>{{ $value }}/100</span>
                        </div>
                        <div class="w-full bg-slate-800 rounded-full h-4">
                            <div class="bg-indigo-500 h-4 rounded-full" style="width: {{ $value }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <h2 class="text-2xl font-semibold mb-6">Sinais de stack</h2>
                <div class="grid grid-cols-2 gap-3">
                    @foreach($analysis->metrics['stack_signals'] as $stack => $enabled)
                        <div class="rounded-xl px-4 py-3 border {{ $enabled ? 'bg-emerald-500/10 border-emerald-500 text-emerald-300' : 'bg-slate-950 border-slate-800 text-slate-500' }}">
                            {{ strtoupper($stack) }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <h2 class="text-2xl font-semibold mb-6">Linguagens</h2>
                <div class="space-y-4">
                    @foreach($analysis->metrics['languages'] as $language => $count)
                        <div>
                            <div class="flex justify-between mb-2">
                                <span>{{ $language }}</span>
                                <span>{{ $count }}</span>
                            </div>
                            <div class="w-full bg-slate-800 rounded-full h-3">
                                <div class="bg-cyan-500 h-3 rounded-full" style="width: {{ min(100, ($count / max($analysis->metrics['total_files'], 1)) * 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <h2 class="text-2xl font-semibold mb-6">Dependências e documentação</h2>
                <div class="space-y-3">
                    <div class="flex justify-between"><span>README</span><span>{{ $analysis->metrics['has_readme'] ? 'Sim' : 'Não' }}</span></div>
                    <div class="flex justify-between"><span>Docs</span><span>{{ $analysis->metrics['has_docs'] ? 'Sim' : 'Não' }}</span></div>
                    @foreach($analysis->metrics['dependency_files'] as $file => $exists)
                        <div class="flex justify-between"><span>{{ $file }}</span><span>{{ $exists ? 'Sim' : 'Não' }}</span></div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <h2 class="text-2xl font-semibold mb-6">Estrutura principal</h2>
            <div class="flex flex-wrap gap-3">
                @foreach($analysis->metrics['top_level_directories'] as $directory)
                    <span class="px-4 py-2 rounded-full bg-slate-800 border border-slate-700">{{ $directory }}</span>
                @endforeach
            </div>
        </div>
    </div>
</body>
</html>