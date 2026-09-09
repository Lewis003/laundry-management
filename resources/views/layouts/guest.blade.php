<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Staff Login - Aura Laundry Systems</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F8FAFC] text-slate-800 antialiased min-h-screen flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8">

    <div class="mb-8">
        <a href="/">
            <x-application-logo />
        </a>
    </div>

    <div class="w-full sm:max-w-md bg-white p-8 rounded-3xl border border-slate-200/80 shadow-2xs">
        {{ $slot }}
    </div>

    <div class="mt-8 text-center text-xs text-slate-400">
        &copy; {{ date('Y') }} Aura Laundry Systems Ltd. Authorized Staff Only.
    </div>

</body>
</html>
