<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = [
        'logo_path',
        'favicon_path',
        'hero_screenshot_path',
        'site_title',
        'site_description',
        'pricing_plans',
    ];

    protected function casts(): array
    {
        return [
            'pricing_plans' => 'array',
        ];
    }

    /**
     * @return list<array{period: string, price_label: string, highlight: bool, badge: ?string}>
     */
    public static function defaultPricingPlans(): array
    {
        return [
            ['period' => '1 Aylık', 'price_label' => '', 'highlight' => false, 'badge' => null],
            ['period' => '3 Aylık', 'price_label' => '', 'highlight' => false, 'badge' => null],
            ['period' => '6 Aylık', 'price_label' => '', 'highlight' => false, 'badge' => 'Esnek'],
            ['period' => '12 Aylık', 'price_label' => '', 'highlight' => true, 'badge' => 'En avantajlı'],
        ];
    }

    /**
     * @return list<array{period: string, price_label: string, highlight: bool, badge: ?string}>
     */
    public function resolvedPricingPlans(): array
    {
        $stored = $this->pricing_plans;
        if (! is_array($stored) || $stored === []) {
            return static::defaultPricingPlans();
        }

        $defaults = static::defaultPricingPlans();
        $out = [];
        foreach ($defaults as $i => $default) {
            $row = $stored[$i] ?? [];
            $badge = array_key_exists('badge', $row)
                ? (is_string($row['badge']) && $row['badge'] !== '' ? $row['badge'] : null)
                : $default['badge'];

            $out[] = [
                'period' => is_string($row['period'] ?? null) ? $row['period'] : $default['period'],
                'price_label' => is_string($row['price_label'] ?? null) ? $row['price_label'] : ($default['price_label'] ?? ''),
                'highlight' => filter_var($row['highlight'] ?? $default['highlight'], FILTER_VALIDATE_BOOLEAN),
                'badge' => $badge,
            ];
        }

        return $out;
    }

    public static function current(): self
    {
        $row = static::query()->first();
        if ($row !== null) {
            return $row;
        }

        return static::query()->create([]);
    }

    /**
     * @return array{logo_url: ?string, favicon_url: ?string, hero_screenshot_url: ?string, site_title: string, site_description: string, pricing_plans: list<array{period: string, price_label: string, highlight: bool, badge: ?string}>}
     */
    public function toPublicProps(): array
    {
        return [
            'logo_url' => $this->logo_path ? '/storage/'.ltrim($this->logo_path, '/') : null,
            'favicon_url' => $this->favicon_path ? '/storage/'.ltrim($this->favicon_path, '/') : null,
            'hero_screenshot_url' => $this->hero_screenshot_path ? '/storage/'.ltrim($this->hero_screenshot_path, '/') : null,
            'site_title' => $this->site_title ?: 'PoliçeAsist',
            'site_description' => $this->site_description ?? '',
            'pricing_plans' => $this->resolvedPricingPlans(),
        ];
    }
}
