<?php

namespace App\Support;

use App\Models\Organization;

class Currency
{
    /**
     * @var array<string, array{name: string, symbol: string, precision: int, position: string}>
     */
    public const CATALOG = [
        'BDT' => ['name' => 'Bangladeshi Taka', 'symbol' => '৳', 'precision' => 0, 'position' => 'before'],
        'USD' => ['name' => 'US Dollar', 'symbol' => '$', 'precision' => 2, 'position' => 'before'],
        'EUR' => ['name' => 'Euro', 'symbol' => '€', 'precision' => 2, 'position' => 'before'],
        'GBP' => ['name' => 'British Pound', 'symbol' => '£', 'precision' => 2, 'position' => 'before'],
        'INR' => ['name' => 'Indian Rupee', 'symbol' => '₹', 'precision' => 0, 'position' => 'before'],
        'PKR' => ['name' => 'Pakistani Rupee', 'symbol' => '₨', 'precision' => 0, 'position' => 'before'],
        'SAR' => ['name' => 'Saudi Riyal', 'symbol' => 'ر.س', 'precision' => 2, 'position' => 'after'],
        'AED' => ['name' => 'UAE Dirham', 'symbol' => 'د.إ', 'precision' => 2, 'position' => 'after'],
        'QAR' => ['name' => 'Qatari Riyal', 'symbol' => 'ر.ق', 'precision' => 2, 'position' => 'after'],
        'MYR' => ['name' => 'Malaysian Ringgit', 'symbol' => 'RM', 'precision' => 2, 'position' => 'before'],
        'SGD' => ['name' => 'Singapore Dollar', 'symbol' => 'S$', 'precision' => 2, 'position' => 'before'],
        'AUD' => ['name' => 'Australian Dollar', 'symbol' => 'A$', 'precision' => 2, 'position' => 'before'],
        'CAD' => ['name' => 'Canadian Dollar', 'symbol' => 'C$', 'precision' => 2, 'position' => 'before'],
    ];

    public static function organization(): ?Organization
    {
        $user = auth()->user();

        if ($user?->relationLoaded('organization') && $user->organization) {
            return $user->organization;
        }

        if ($user?->organization_id) {
            return $user->organization()->first();
        }

        return Organization::query()->first();
    }

    public static function code(): string
    {
        $code = strtoupper((string) (static::organization()?->currency_code ?: config('app.currency', 'BDT')));

        return array_key_exists($code, self::CATALOG) ? $code : 'BDT';
    }

    /**
     * @return array{name: string, symbol: string, precision: int, position: string}
     */
    public static function meta(?string $code = null): array
    {
        $code = strtoupper($code ?: static::code());

        return self::CATALOG[$code] ?? self::CATALOG['BDT'];
    }

    public static function symbol(?string $code = null): string
    {
        return static::meta($code)['symbol'];
    }

    public static function name(?string $code = null): string
    {
        return static::meta($code)['name'];
    }

    public static function precision(?string $code = null): int
    {
        return static::meta($code)['precision'];
    }

    public static function format(float|int|string|null $amount, ?string $code = null): string
    {
        $code = $code ?: static::code();
        $meta = static::meta($code);
        $value = number_format((float) $amount, $meta['precision']);

        return $meta['position'] === 'after'
            ? $value.' '.$meta['symbol']
            : $meta['symbol'].$value;
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::CATALOG as $code => $meta) {
            $options[$code] = sprintf('%s — %s (%s)', $code, $meta['name'], $meta['symbol']);
        }

        return $options;
    }
}
