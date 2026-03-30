@extends('install.layout')

@section('title', 'Gereksinimler')

@section('content')
    <h2 class="text-lg font-semibold text-slate-900">Sunucu kontrolleri</h2>
    <p class="mt-1 text-sm text-slate-600">
        Aşağıdaki maddeler tamamsa bir sonraki adıma geçebilirsiniz.
    </p>

    <ul class="mt-6 space-y-3 text-sm">
        <li class="flex items-start gap-2">
            <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $requirements['php_ok'] ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                {{ $requirements['php_ok'] ? '✓' : '!' }}
            </span>
            <span>
                PHP {{ $requirements['php_version'] }}
                @if (! $requirements['php_ok'])
                    <span class="text-red-600">— 8.3+ gerekir</span>
                @endif
            </span>
        </li>
        <li class="flex items-start gap-2">
            <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $requirements['extensions_missing'] === [] ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                {{ $requirements['extensions_missing'] === [] ? '✓' : '!' }}
            </span>
            <span>
                PHP eklentileri (pdo_mysql, mbstring, openssl, …)
                @if ($requirements['extensions_missing'] !== [])
                    <span class="block text-red-600">Eksik: {{ implode(', ', $requirements['extensions_missing']) }}</span>
                @endif
            </span>
        </li>
        <li class="flex items-start gap-2">
            <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $requirements['env_writable'] ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                {{ $requirements['env_writable'] ? '✓' : '!' }}
            </span>
            <span>.env dosyası mevcut ve yazılabilir</span>
        </li>
        <li class="flex items-start gap-2">
            <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $requirements['storage_writable'] && $requirements['cache_writable'] ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                {{ $requirements['storage_writable'] && $requirements['cache_writable'] ? '✓' : '!' }}
            </span>
            <span><code class="rounded bg-slate-100 px-1">storage/</code> ve <code class="rounded bg-slate-100 px-1">bootstrap/cache</code> yazılabilir</span>
        </li>
    </ul>

    @if ($requirements['details'] !== [])
        <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
            <p class="font-medium">Yapmanız gerekenler</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($requirements['details'] as $line)
                    <li>{{ $line }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-end">
        @if ($requirements['php_ok'] && $requirements['extensions_missing'] === [] && $requirements['paths_ok'])
            <a
                href="{{ route('install.database') }}"
                class="inline-flex items-center justify-center rounded-xl bg-brand px-5 py-3 text-sm font-semibold text-white shadow-md transition hover:bg-brand-dark"
            >
                Veritabanı ayarlarına geç
            </a>
        @else
            <span class="inline-flex cursor-not-allowed items-center justify-center rounded-xl bg-slate-200 px-5 py-3 text-sm font-semibold text-slate-500">
                Önce sunucuyu düzeltin
            </span>
        @endif
    </div>

    <p class="mt-8 border-t border-slate-100 pt-6 text-xs leading-relaxed text-slate-500">
        <strong class="text-slate-700">Canlı sunucu:</strong> Sihirbaz yalnızca <code class="rounded bg-slate-100 px-1">APP_ENV=production</code>
        iken tüm siteyi kuruluma yönlendirir. Yerelde <code class="rounded bg-slate-100 px-1">local</code> kullanıyorsanız bu zorunluluk yoktur.
    </p>
    <p class="mt-4 text-xs leading-relaxed text-slate-500">
        İpucu: <code class="rounded bg-slate-100 px-1">cp .env.example .env</code>,
        ardından <code class="rounded bg-slate-100 px-1">composer install --no-dev --optimize-autoloader</code> ve
        <code class="rounded bg-slate-100 px-1">npm ci &amp;&amp; npm run build</code>.
        Kurulumda geçici <code class="rounded bg-slate-100 px-1">SESSION_DRIVER=file</code>; tamamlanınca veritabanı oturumu ayarlanır.
    </p>
@endsection
