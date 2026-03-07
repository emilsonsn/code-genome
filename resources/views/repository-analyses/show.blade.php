<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $analysis->repository_name }} - Code Genome</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-950 text-white min-h-screen">

<div class="max-w-7xl mx-auto px-6 py-10">
    <div class="flex items-center justify-between mb-10">
        <div>
            <h1 class="text-4xl font-bold flex items-center gap-3">
                <i class="fa-brands fa-github"></i>
                {{ $analysis->owner }}/{{ $analysis->repository_name }}
            </h1>
            <p class="text-slate-400 mt-2">{{ $analysis->repository_url }}</p>
        </div>

        <a href="{{ route('repository-analyses.index') }}"
           class="bg-slate-800 hover:bg-slate-700 px-5 py-3 rounded-xl">
            New Analysis
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-10">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 flex items-center gap-4">
            <i class="fa-solid fa-file-code text-indigo-400 text-3xl"></i>
            <div>
                <p class="text-slate-400 text-sm">Files</p>
                <p class="text-3xl font-bold">{{ $analysis->metrics['total_files'] }}</p>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 flex items-center gap-4">
            <i class="fa-solid fa-folder-tree text-yellow-400 text-3xl"></i>
            <div>
                <p class="text-slate-400 text-sm">Folders</p>
                <p class="text-3xl font-bold">{{ $analysis->metrics['total_directories'] }}</p>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 flex items-center gap-4">
            <i class="fa-solid fa-database text-cyan-400 text-3xl"></i>
            <div>
                <p class="text-slate-400 text-sm">Size</p>
                <p class="text-3xl font-bold">{{ $analysis->metrics['total_size_human'] }}</p>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 flex items-center gap-4">
            <i class="fa-solid fa-vial text-green-400 text-3xl"></i>
            <div>
                <p class="text-slate-400 text-sm">Tests</p>
                <p class="text-3xl font-bold">{{ $analysis->metrics['test_files_count'] }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 flex items-center gap-4">
            <i class="fa-solid fa-layer-group text-purple-400 text-2xl"></i>
            <div>
                <p class="text-slate-400 text-sm">Max Depth</p>
                <p class="text-3xl font-bold mt-1">{{ $analysis->metrics['max_directory_depth'] }}</p>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 flex items-center gap-4">
            <i class="fa-solid fa-folder-open text-orange-400 text-2xl"></i>
            <div>
                <p class="text-slate-400 text-sm">Avg Files per Folder</p>
                <p class="text-3xl font-bold mt-1">{{ $analysis->metrics['avg_files_per_directory'] }}</p>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 flex items-center gap-4">
            <i class="fa-solid fa-flask text-green-400 text-2xl"></i>
            <div>
                <p class="text-slate-400 text-sm">Test Coverage</p>
                <p class="text-3xl font-bold mt-1">{{ $analysis->metrics['test_ratio'] }}%</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <h2 class="text-2xl font-semibold mb-6 flex items-center gap-2">
                <i class="fa-solid fa-dna text-indigo-400"></i>
                Project DNA
            </h2>

            @foreach ($analysis->metrics['scores'] as $label => $value)
                <div class="mb-5">
                    <div class="flex justify-between mb-2">
                        <span class="capitalize">{{ str_replace('_',' ',$label) }}</span>
                        <span>{{ $value }}/100</span>
                    </div>

                    <div class="w-full bg-slate-800 rounded-full h-4">
                        <div class="bg-indigo-500 h-4 rounded-full"
                             style="width: {{ $value }}%">
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <h2 class="text-2xl font-semibold mb-6 flex items-center gap-2">
                <i class="fa-solid fa-microchip text-emerald-400"></i>
                Stack Signals
            </h2>

            <div class="grid grid-cols-2 gap-3">
                @foreach ($analysis->metrics['stack_signals'] as $stack)
                    <div
                        class="flex items-center gap-3 rounded-xl px-4 py-3 border
                        {{ $stack['enabled']
                            ? 'bg-emerald-500/10 border-emerald-500 text-emerald-300'
                            : 'bg-slate-950 border-slate-800 text-slate-500' }}">

                        <i class="{{ $stack['icon'] }} text-lg w-5 text-center"></i>

                        <span>{{ $stack['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <h2 class="text-2xl font-semibold mb-6 flex items-center gap-2">
                <i class="fa-solid fa-folder-open text-yellow-400"></i>
                Largest Directories
            </h2>

            @foreach ($analysis->metrics['largest_directories'] as $dir => $count)
                <div class="flex justify-between border-b border-slate-800 py-2">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-folder text-yellow-500"></i>
                        {{ $dir }}
                    </span>

                    <span>{{ $count }} files</span>
                </div>
            @endforeach
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <h2 class="text-2xl font-semibold mb-6 flex items-center gap-2">
                <i class="fa-solid fa-file-lines text-cyan-400"></i>
                Largest Files
            </h2>

            @foreach ($analysis->metrics['largest_files'] as $file => $lines)
                <div class="flex justify-between border-b border-slate-800 py-2">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-file-code text-cyan-400"></i>
                        {{ $file }}
                    </span>

                    <span>{{ $lines }} lines</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 mb-8">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <h2 class="text-2xl font-semibold mb-6 flex items-center gap-2">
                <i class="fa-solid fa-code text-cyan-400"></i>
                Languages
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($analysis->metrics['language_distribution'] as $item)
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="capitalize">{{ $item['language'] }}</span>
                            <span>{{ $item['percent'] }}%</span>
                        </div>

                        <div class="w-full bg-slate-800 rounded-full h-3">
                            <div class="bg-cyan-500 h-3 rounded-full"
                                 style="width: {{ $item['percent'] }}%">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 mb-8">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <h2 class="text-2xl font-semibold mb-6 flex items-center gap-2">
                <i class="fa-solid fa-cubes text-purple-400"></i>
                Dependencies & Documentation
            </h2>

            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-book"></i> README
                    </span>

                    @if ($analysis->metrics['has_readme'])
                        <i class="fa-solid fa-circle-check text-emerald-400"></i>
                    @else
                        <i class="fa-solid fa-circle-xmark text-red-400"></i>
                    @endif
                </div>

                <div class="flex justify-between items-center">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-file-lines"></i> Docs
                    </span>

                    @if ($analysis->metrics['has_docs'])
                        <i class="fa-solid fa-circle-check text-emerald-400"></i>
                    @else
                        <i class="fa-solid fa-circle-xmark text-red-400"></i>
                    @endif
                </div>

                @foreach ($analysis->metrics['dependency_files'] as $file => $exists)
                    <div class="flex justify-between items-center">
                        <span class="font-mono text-sm flex items-center gap-2">
                            <i class="fa-solid fa-cube"></i>
                            {{ $file }}
                        </span>

                        @if ($exists)
                            <i class="fa-solid fa-circle-check text-emerald-400"></i>
                        @else
                            <i class="fa-solid fa-circle-xmark text-red-400"></i>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">

        <h2 class="text-2xl font-semibold mb-6 flex items-center gap-2">
            <i class="fa-solid fa-folder-tree text-yellow-400"></i>
            Project Structure
        </h2>

        <div class="space-y-2">
            <x-folder-tree :tree="$analysis->metrics['directory_tree']" :level="0" />
        </div>

    </div>
</div>
</body>
</html>