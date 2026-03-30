<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Kurulum') — PoliçeAsist</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { DEFAULT: '#064AAD', dark: '#053d8f' },
                    },
                },
            },
        };
    </script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <div class="mx-auto max-w-2xl px-4 py-10 sm:py-16">
        <header class="mb-10 text-center">
            <p class="text-sm font-semibold uppercase tracking-widest text-brand">PoliçeAsist</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">Kurulum sihirbazı</h1>
            <p class="mt-2 text-sm text-slate-600">VPS üzerinde ilk kurulum adımları</p>
        </header>

        <main class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            @if (session('error'))
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>

        <p class="mt-8 text-center text-xs text-slate-500">
            Kurulum tamamlandıktan sonra bu adres (/install) erişime kapanır.
        </p>
    </div>
</body>
</html>
