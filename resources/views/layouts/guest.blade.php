<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Masuk' }} — {{ config('app.name', 'Simulasi ERP') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
    <div class="flex min-h-screen flex-col justify-center px-4 py-10 sm:px-6 lg:px-8">
        {{ $slot }}
    </div>
</body>
</html>
