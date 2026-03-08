<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code Genome</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/loading.css', 'resources/css/particles.css'])
</head>
<body class="bg-slate-950 text-white min-h-screen overflow-x-hidden">
    <div id="particles-container" class="particles-container"></div>

    <div id="main-content" class="relative z-10 max-w-4xl mx-auto px-6 py-20">
        <div class="text-center mb-12">
            <div class="flex justify-center mb-6">
                <div class="title-helix">
                    <div class="title-pair">
                        <div class="title-node title-node-left"></div>
                        <div class="title-bridge"></div>
                        <div class="title-node title-node-right"></div>
                    </div>
                    <div class="title-pair">
                        <div class="title-node title-node-left"></div>
                        <div class="title-bridge"></div>
                        <div class="title-node title-node-right"></div>
                    </div>
                    <div class="title-pair">
                        <div class="title-node title-node-left"></div>
                        <div class="title-bridge"></div>
                        <div class="title-node title-node-right"></div>
                    </div>
                </div>
            </div>
            <h1 class="text-5xl font-bold mb-4 bg-gradient-to-r from-indigo-400 via-purple-400 to-emerald-400 bg-clip-text text-transparent">
                Code Genome
            </h1>
            <p class="text-slate-300 text-lg">Paste the repository URL and see the visual DNA of the project</p>
        </div>

        <div class="glow-container bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-2xl">
            <form id="analyze-form" action="{{ route('repository-analyses.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label class="block mb-2 text-sm text-slate-300">Repository URL</label>
                    <input
                        type="url"
                        name="repository_url"
                        value="{{ old('repository_url') }}"
                        placeholder="https://github.com/laravel/laravel"
                        class="w-full rounded-xl bg-slate-950 border border-slate-700 px-4 py-4 text-white outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all"
                    >
                    @error('repository_url')
                        <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-emerald-600 hover:from-indigo-500 hover:to-emerald-500 transition-all rounded-xl py-4 font-semibold shadow-lg shadow-indigo-500/25">
                    <span class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        Analyze Repository
                    </span>
                </button>
            </form>
        </div>

        <div class="mt-8 text-center text-slate-500 text-sm">
            <p>Supports GitHub, GitLab, and Bitbucket repositories</p>
        </div>
    </div>

    <div id="loading-screen" class="hidden fixed inset-0 bg-slate-950 z-50 flex flex-col items-center justify-center">
        <div class="text-center">
            <div class="dna-loader mb-8">
                <div class="dna-strand">
                    @for ($i = 0; $i < 8; $i++)
                    <div class="dna-pair">
                        <div class="dna-node dna-node-left"></div>
                        <div class="dna-bridge"></div>
                        <div class="dna-node dna-node-right"></div>
                    </div>
                    @endfor
                </div>
            </div>

            <h2 class="text-3xl font-bold mb-3">Sequencing Code DNA</h2>
            <p id="loading-status" class="text-slate-400 loading-text">Cloning repository...</p>
        </div>
    </div>

    @vite(['resources/js/loading.js', 'resources/js/particles.js'])
</body>
</html>