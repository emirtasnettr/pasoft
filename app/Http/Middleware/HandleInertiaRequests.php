<?php

namespace App\Http\Middleware;

use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        if ($user) {
            $user->loadMissing('role:id,slug,name');
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
            ],
            'appSettings' => static function (): array {
                if (request()->is('install*')) {
                    return [
                        'logo_url' => null,
                        'favicon_url' => null,
                        'hero_screenshot_url' => null,
                        'site_title' => 'Kurulum',
                        'site_description' => '',
                        'pricing_plans' => AppSetting::defaultPricingPlans(),
                    ];
                }

                if (! Schema::hasTable('app_settings')) {
                    return [
                        'logo_url' => null,
                        'favicon_url' => null,
                        'hero_screenshot_url' => null,
                        'site_title' => 'PoliçeAsist',
                        'site_description' => '',
                        'pricing_plans' => AppSetting::defaultPricingPlans(),
                    ];
                }

                return AppSetting::current()->toPublicProps();
            },
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'warning' => $request->session()->get('warning'),
            ],
        ];
    }
}
