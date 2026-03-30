@extends('install.layout')

@section('title', 'Yönetici')

@section('content')
    <h2 class="text-lg font-semibold text-slate-900">Uygulama ve ilk yönetici</h2>
    <p class="mt-1 text-sm text-slate-600">
        Migration çalıştırılır, roller oluşturulur ve <strong>sistem yöneticisi</strong> hesabı eklenir (admin paneline giriş).
    </p>

    @if ($errors->has('finish'))
        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $errors->first('finish') }}
        </div>
    @endif

    <form method="post" action="{{ route('install.admin.store') }}" class="mt-6 space-y-4">
        @csrf
        <div>
            <label for="app_name" class="block text-sm font-medium text-slate-700">Uygulama adı (APP_NAME)</label>
            <input type="text" name="app_name" id="app_name" value="{{ $defaults['app_name'] }}" required
                class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
        </div>
        <div>
            <label for="app_url" class="block text-sm font-medium text-slate-700">Site adresi (APP_URL)</label>
            <input type="url" name="app_url" id="app_url" value="{{ $defaults['app_url'] }}" required placeholder="https://ornek.com"
                class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
            <p class="mt-1 text-xs text-slate-500">HTTPS kullanıyorsanız https:// ile yazın; sondaki / olmasın.</p>
        </div>

        <div class="border-t border-slate-100 pt-4">
            <p class="text-sm font-medium text-slate-800">Sistem yöneticisi hesabı</p>
        </div>
        <div>
            <label for="admin_name" class="block text-sm font-medium text-slate-700">Ad soyad</label>
            <input type="text" name="admin_name" id="admin_name" value="{{ old('admin_name') }}" required autocomplete="name"
                class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
            @error('admin_name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="admin_email" class="block text-sm font-medium text-slate-700">E-posta (giriş için)</label>
            <input type="email" name="admin_email" id="admin_email" value="{{ old('admin_email') }}" required autocomplete="email"
                class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
            @error('admin_email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="admin_password" class="block text-sm font-medium text-slate-700">Şifre (en az 8 karakter)</label>
            <input type="password" name="admin_password" id="admin_password" required autocomplete="new-password"
                class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
            @error('admin_password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="admin_password_confirmation" class="block text-sm font-medium text-slate-700">Şifre tekrar</label>
            <input type="password" name="admin_password_confirmation" id="admin_password_confirmation" required autocomplete="new-password"
                class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
        </div>

        <div class="flex flex-col gap-3 pt-4 sm:flex-row sm:justify-between">
            <a href="{{ route('install.database') }}" class="inline-flex justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Geri
            </a>
            <button type="submit" class="inline-flex justify-center rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-brand-dark">
                Kurulumu tamamla
            </button>
        </div>
    </form>
@endsection
