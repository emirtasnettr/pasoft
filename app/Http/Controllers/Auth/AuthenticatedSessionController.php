<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /** @var array<string, string> role slug => seeded test email */
    private const DEMO_ACCOUNTS = [
        'system_admin' => 'sistem@policeasist.test',
        'admin' => 'admin@policeasist.test',
        'sales' => 'satis@policeasist.test',
        'operations' => 'operasyon@policeasist.test',
    ];

    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
            'demoLogin' => $this->demoLoginPayload(),
        ]);
    }

    /**
     * One-click login for seeded test users (local environment only).
     */
    public function demoLogin(string $role): RedirectResponse
    {
        if (! $this->demoLoginAllowed()) {
            abort(404);
        }

        if (! isset(self::DEMO_ACCOUNTS[$role])) {
            abort(404);
        }

        $user = User::query()->where('email', self::DEMO_ACCOUNTS[$role])->first();

        if ($user === null) {
            return redirect()
                ->route('login')
                ->with('status', 'Test hesabı bulunamadı. `php artisan db:seed` çalıştırın.');
        }

        Auth::login($user, remember: true);

        request()->session()->regenerate();

        return redirect()->intended($this->defaultLoginPath($user));
    }

    /**
     * @return array{enabled: bool, roles: list<array{slug: string, label: string, email: string}>}
     */
    private function demoLoginPayload(): array
    {
        if (! $this->demoLoginAllowed()) {
            return ['enabled' => false, 'roles' => []];
        }

        $labels = [
            'system_admin' => 'Sistem yöneticisi',
            'admin' => 'Yönetici',
            'sales' => 'Satış',
            'operations' => 'Operasyon',
        ];

        $roles = [];
        foreach (self::DEMO_ACCOUNTS as $slug => $email) {
            $roles[] = [
                'slug' => $slug,
                'label' => $labels[$slug] ?? $slug,
                'email' => $email,
            ];
        }

        return ['enabled' => true, 'roles' => $roles];
    }

    private function demoLoginAllowed(): bool
    {
        return app()->environment('local');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended($this->defaultLoginPath(Auth::user()));
    }

    private function defaultLoginPath(?User $user): string
    {
        if ($user?->hasRole('system_admin')) {
            return route('admin.settings.edit', [], false);
        }

        return route('dashboard', [], false);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
