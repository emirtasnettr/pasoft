<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSystemSettingsRequest;
use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SystemSettingsController extends Controller
{
    public function edit(): Response
    {
        $setting = AppSetting::current();

        return Inertia::render('Admin/Settings', [
            'settings' => [
                'site_title' => $setting->site_title ?? '',
                'site_description' => $setting->site_description ?? '',
                'logo_url' => $setting->logo_path ? '/storage/'.ltrim($setting->logo_path, '/') : null,
                'favicon_url' => $setting->favicon_path ? '/storage/'.ltrim($setting->favicon_path, '/') : null,
                'hero_screenshot_url' => $setting->hero_screenshot_path ? '/storage/'.ltrim($setting->hero_screenshot_path, '/') : null,
                'pricing_plans' => $setting->resolvedPricingPlans(),
            ],
        ]);
    }

    public function update(UpdateSystemSettingsRequest $request): RedirectResponse
    {
        $setting = AppSetting::current();

        if ($request->boolean('remove_logo') && $setting->logo_path) {
            Storage::disk('public')->delete($setting->logo_path);
            $setting->logo_path = null;
        }

        if ($request->boolean('remove_favicon') && $setting->favicon_path) {
            Storage::disk('public')->delete($setting->favicon_path);
            $setting->favicon_path = null;
        }

        if ($request->boolean('remove_hero_screenshot') && $setting->hero_screenshot_path) {
            Storage::disk('public')->delete($setting->hero_screenshot_path);
            $setting->hero_screenshot_path = null;
        }

        if ($request->hasFile('logo')) {
            if ($setting->logo_path) {
                Storage::disk('public')->delete($setting->logo_path);
            }
            $setting->logo_path = $request->file('logo')->store('site', 'public');
        }

        if ($request->hasFile('favicon')) {
            if ($setting->favicon_path) {
                Storage::disk('public')->delete($setting->favicon_path);
            }
            $setting->favicon_path = $request->file('favicon')->store('site', 'public');
        }

        if ($request->hasFile('hero_screenshot')) {
            if ($setting->hero_screenshot_path) {
                Storage::disk('public')->delete($setting->hero_screenshot_path);
            }
            $setting->hero_screenshot_path = $request->file('hero_screenshot')->store('site', 'public');
        }

        $setting->site_title = $request->input('site_title') ?: null;
        $setting->site_description = $request->input('site_description') ?: null;

        if ($request->has('pricing_plans')) {
            $setting->pricing_plans = $request->input('pricing_plans');
        }

        $setting->save();

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', 'Site ayarları güncellendi.');
    }
}
