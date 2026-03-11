<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code Genome</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

@vite([
    'resources/css/analysis.css',
    'resources/css/loading.css',
    'resources/css/particles.css',
    'resources/js/loading.js',
    'resources/js/particles.js'
])

</head>
<body class="bg-slate-950 text-white min-h-screen">
    @include('components.loading')
    <div id="particles-container" class="particles-container"></div>

    <div class="max-w-4xl mx-auto px-6 py-20">
        <div class="text-center mb-12">
            <h1 class="text-5xl font-bold mb-4">Code Genome</h1>
            <p class="text-slate-300 text-lg">Paste the repository URL and see the visual DNA of the project</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-2xl">
            <form id="analyze-form" action="{{ route('repository-analyses.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label class="block mb-2 text-sm text-slate-300">Repository URL</label>
                    <input
                        type="url"
                        name="repository_url"
                        value="{{ old('repository_url') }}"
                        placeholder="https://github.com/laravel/laravel"
                        class="w-full rounded-xl bg-slate-950 border border-slate-700 px-4 py-4 text-white outline-none"
                    >
                    @error('repository_url')
                        <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <button class="w-full bg-indigo-600 hover:bg-indigo-500 transition rounded-xl py-4 font-semibold">
                    Analyze Repository
                </button>
            </form>

            <div class="action-links">
                <a
                    href="{{ route('repository-analyses.genome') }}"
                    class="explore-btn"
                >
                    <i class="fa-solid fa-dna"></i> Explore
                </a>
                <a
                    href="{{ route('repository-analyses.ranking') }}"
                    class="explore-btn"
                >
                    <i class="fa-solid fa-trophy"></i> Ranking
                </a>
                <a
                    href="https://github.com/emilsonsn/code-genome/"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="subtle-btn star-cta"
                >
                    <i class="fa-solid fa-star"></i>
                    <span>Leave a Star on GitHub</span>
                </a>
                <a
                    href="http://emilsonsouza.com.br/"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="subtle-btn"
                >
                    <i class="fa-solid fa-globe"></i> My Website
                </a>
                
            </div>
        </div>        

        <p class="text-center text-slate-500 text-sm mt-6">
            Built by <span class="text-slate-300 font-semibold">emilsonsn</span>
        </p>
    </div>
</body>
</html>