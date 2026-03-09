<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code Genome</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite([
        'resources/css/genome.css',
        'resources/js/genome.js', 
        'resources/css/repository-card.css'
    ])
</head>
<body>
    <div id="labels"></div>
    <script>
        window.repositories = @json($analyses);
    </script>
</body>
</html>

@include('components.repository-card')