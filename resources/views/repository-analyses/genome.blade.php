<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code Genome</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite([
        'resources/css/genome.css',
        'resources/css/tutorial.css',
        'resources/js/genome.js',
        'resources/js/tutorial.js',
        'resources/css/repository-card.css'
    ])
</head>
<body>
    <div id="labels"></div>

    <a href="javascript:history.back()" class="floating-back-button" title="Back">
        <i class="fa-solid fa-arrow-left"></i>
    </a>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
    <script>
        window.repositories = @json($analyses);
    </script>
</body>
</html>

@include('components.tutorial')
@include('components.repository-card')