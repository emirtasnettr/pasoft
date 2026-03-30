@extends('install.layout')

@section('title', 'Veritabanı')

@section('content')
    <h2 class="text-lg font-semibold text-slate-900">MySQL bağlantısı</h2>
    <p class="mt-1 text-sm text-slate-600">
        Veritabanını önce sunucuda oluşturun (utf8mb4). Bilgiler <code class="rounded bg-slate-100 px-1">.env</code> dosyasına yazılır.
    </p>

    @if ($errors->has('db_connection'))
        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $errors->first('db_connection') }}
        </div>
    @endif

    <form method="post" action="{{ route('install.database.store') }}" class="mt-6 space-y-4">
        @csrf
        <div>
            <label for="db_host" class="block text-sm font-medium text-slate-700">Sunucu (host)</label>
            <input type="text" name="db_host" id="db_host" value="{{ $defaults['host'] }}" required
                class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
        </div>
        <div>
            <label for="db_port" class="block text-sm font-medium text-slate-700">Port</label>
            <input type="text" name="db_port" id="db_port" value="{{ $defaults['port'] }}"
                class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
        </div>
        <div>
            <label for="db_database" class="block text-sm font-medium text-slate-700">Veritabanı adı</label>
            <input type="text" name="db_database" id="db_database" value="{{ $defaults['database'] }}" required
                class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
        </div>
        <div>
            <label for="db_username" class="block text-sm font-medium text-slate-700">Kullanıcı adı</label>
            <input type="text" name="db_username" id="db_username" value="{{ $defaults['username'] }}" required autocomplete="username"
                class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
        </div>
        <div>
            <label for="db_password" class="block text-sm font-medium text-slate-700">Şifre</label>
            <input type="password" name="db_password" id="db_password" value="{{ old('db_password') }}" autocomplete="current-password"
                class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
        </div>

        <div class="flex flex-col gap-3 pt-4 sm:flex-row sm:justify-between">
            <a href="{{ route('install.welcome') }}" class="inline-flex justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Geri
            </a>
            <button type="submit" class="inline-flex justify-center rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-brand-dark">
                Bağlantıyı test et ve kaydet
            </button>
        </div>
    </form>
@endsection
