<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Commain - Qualitative Data Analysis</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="h-full text-slate-900">
    <div class="min-h-full flex flex-col">
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-40 border-b border-slate-200">
            <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold">C</span>
                    </div>
                    <a href="{{ route('home') }}" class="text-xl font-bold tracking-tight text-slate-900">Commain</a>
                </div>
                <div class="hidden sm:flex gap-6">
                    <a href="{{ route('home') }}" class="text-sm font-medium text-slate-600 hover:text-indigo-600 transition-colors">Home</a>
                    <a href="{{ route('projects.index') }}" class="text-sm font-medium text-slate-600 hover:text-indigo-600 transition-colors">Projects</a>
                </div>
            </nav>
        </header>

        <main class="flex-grow">
            @yield('content')
        </main>

        <footer class="bg-white border-t border-slate-200 py-8">
            <div class="max-w-7xl mx-auto px-4 text-center">
                <p class="text-sm text-slate-500">&copy; {{ date('Y') }} Commain Systems. All rights reserved.</p>
            </div>
        </footer>
    </div>
    @stack('scripts')
</body>
</html>
