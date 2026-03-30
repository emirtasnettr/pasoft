<?php

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\EnvWriter;
use App\Support\Installer;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use PDO;
use PDOException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class InstallWizardController extends Controller
{
    public function welcome(): Response
    {
        $requirements = $this->requirements();

        if ($requirements['php_ok'] && $requirements['env_writable'] && $requirements['extensions_missing'] === []) {
            try {
                if (blank(config('app.key'))) {
                    Artisan::call('key:generate', ['--force' => true]);
                }
            } catch (Throwable) {
                // Anahtar oluşturulamazsa bir sonraki adımda hata mesajı gösterilir
            }
        }

        return response()->view('install.welcome', [
            'requirements' => $requirements,
        ]);
    }

    public function databaseForm(): RedirectResponse|Response
    {
        if (! $this->requirementsMet()) {
            return redirect()->route('install.welcome');
        }

        return response()->view('install.database', [
            'defaults' => [
                'host' => old('db_host', env('DB_HOST', '127.0.0.1')),
                'port' => old('db_port', env('DB_PORT', '3306')),
                'database' => old('db_database', env('DB_DATABASE', 'policeasist')),
                'username' => old('db_username', env('DB_USERNAME', 'root')),
            ],
        ]);
    }

    public function databaseStore(Request $request): RedirectResponse
    {
        if (! $this->requirementsMet()) {
            return redirect()->route('install.welcome');
        }

        $validated = $request->validate([
            'db_host' => ['required', 'string', 'max:255'],
            'db_port' => ['nullable', 'string', 'max:8'],
            'db_database' => ['required', 'string', 'max:128'],
            'db_username' => ['required', 'string', 'max:128'],
            'db_password' => ['nullable', 'string', 'max:255'],
        ]);

        $port = $validated['db_port'] !== null && $validated['db_port'] !== ''
            ? (int) $validated['db_port']
            : 3306;

        try {
            $this->assertMysqlConnection(
                $validated['db_host'],
                $port,
                $validated['db_database'],
                $validated['db_username'],
                $validated['db_password'] ?? '',
            );
        } catch (PDOException $e) {
            return back()
                ->withInput()
                ->withErrors(['db_connection' => 'Veritabanına bağlanılamadı: '.$e->getMessage()]);
        }

        try {
            EnvWriter::merge([
                'DB_CONNECTION' => 'mysql',
                'DB_HOST' => $validated['db_host'],
                'DB_PORT' => (string) $port,
                'DB_DATABASE' => $validated['db_database'],
                'DB_USERNAME' => $validated['db_username'],
                'DB_PASSWORD' => $validated['db_password'] ?? '',
                'SESSION_DRIVER' => 'file',
            ]);
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['db_connection' => $e->getMessage()]);
        }

        Session::put('install.database_ok', true);

        return redirect()->route('install.admin');
    }

    public function adminForm(): RedirectResponse|Response
    {
        if (! $this->requirementsMet() || ! Session::get('install.database_ok')) {
            return redirect()->route('install.database');
        }

        return response()->view('install.admin', [
            'defaults' => [
                'app_name' => old('app_name', config('app.name', 'PoliçeAsist')),
                'app_url' => old('app_url', config('app.url', '')),
            ],
        ]);
    }

    public function adminStore(Request $request): RedirectResponse
    {
        if (! $this->requirementsMet() || ! Session::get('install.database_ok')) {
            return redirect()->route('install.database');
        }

        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'app_url' => ['required', 'url', 'max:255'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'admin_password' => ['required', 'confirmed', Password::defaults()],
        ]);

        try {
            Artisan::call('config:clear');
            Artisan::call('migrate', ['--force' => true]);

            $seeder = new RoleSeeder;
            $seeder->run();

            $role = Role::query()->where('slug', 'system_admin')->firstOrFail();

            User::query()->create([
                'name' => $validated['admin_name'],
                'email' => $validated['admin_email'],
                'password' => $validated['admin_password'],
                'role_id' => $role->id,
                'email_verified_at' => now(),
                'is_active' => true,
            ]);

            EnvWriter::merge([
                'APP_NAME' => $validated['app_name'],
                'APP_URL' => rtrim($validated['app_url'], '/'),
                'APP_ENV' => 'production',
                'APP_DEBUG' => false,
                'SESSION_DRIVER' => 'database',
            ]);

            Installer::markComplete();

            Artisan::call('config:clear');

            Session::forget('install.database_ok');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->withErrors(['finish' => 'Kurulum tamamlanamadı: '.$e->getMessage()]);
        }

        return redirect('/login')->with(
            'status',
            'Kurulum tamamlandı. Sistem yöneticisi hesabıyla giriş yapabilirsiniz.',
        );
    }

    /**
     * @return array{php_ok: bool, php_version: string, extensions_missing: list<string>, paths_ok: bool, env_writable: bool, storage_writable: bool, cache_writable: bool, details: list<string>}
     */
    private function requirements(): array
    {
        $requiredExt = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'json', 'ctype', 'fileinfo', 'xml'];
        $missing = [];
        foreach ($requiredExt as $ext) {
            if (! extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }

        $phpOk = version_compare(PHP_VERSION, '8.3.0', '>=');
        $envPath = base_path('.env');
        $envWritable = is_file($envPath) && is_writable($envPath);
        $storageWritable = is_writable(storage_path());
        $cacheWritable = is_writable(base_path('bootstrap/cache'));

        $details = [];
        if (! $phpOk) {
            $details[] = 'PHP 8.3 veya üzeri gerekir (şu an: '.PHP_VERSION.').';
        }
        if ($missing !== []) {
            $details[] = 'Eksik PHP eklentileri: '.implode(', ', $missing);
        }
        if (! is_file($envPath)) {
            $details[] = '.env dosyası yok — sunucuda .env.example dosyasını .env olarak kopyalayın.';
        } elseif (! $envWritable) {
            $details[] = '.env yazılabilir değil.';
        }
        if (! $storageWritable) {
            $details[] = 'storage/ dizini yazılabilir olmalı (chmod -R 775 storage).';
        }
        if (! $cacheWritable) {
            $details[] = 'bootstrap/cache yazılabilir olmalı.';
        }

        return [
            'php_ok' => $phpOk,
            'php_version' => PHP_VERSION,
            'extensions_missing' => $missing,
            'paths_ok' => $envWritable && $storageWritable && $cacheWritable && is_file($envPath),
            'env_writable' => $envWritable,
            'storage_writable' => $storageWritable,
            'cache_writable' => $cacheWritable,
            'details' => $details,
        ];
    }

    private function requirementsMet(): bool
    {
        $r = $this->requirements();

        return $r['php_ok'] && $r['extensions_missing'] === [] && $r['paths_ok'];
    }

    /**
     * @throws PDOException
     */
    private function assertMysqlConnection(
        string $host,
        int $port,
        string $database,
        string $username,
        string $password,
    ): void {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $database);
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_TIMEOUT => 8,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $pdo->query('SELECT 1');
    }
}
