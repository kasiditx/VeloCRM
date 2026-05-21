<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

if (! function_exists('velocrm_setting_value')) {
    function velocrm_setting_value(string $key, mixed $default = null): mixed
    {
        try {
            if (! file_exists(storage_path('installed')) || ! Schema::hasTable('settings')) {
                return $default;
            }

            return Setting::get($key, $default);
        } catch (Throwable) {
            return $default;
        }
    }
}

if (! function_exists('velocrm_currency_symbol')) {
    function velocrm_currency_symbol(): string
    {
        return (string) velocrm_setting_value('currency_symbol', '$');
    }
}

if (! function_exists('velocrm_currency_code')) {
    function velocrm_currency_code(): string
    {
        return strtoupper((string) velocrm_setting_value('default_currency', velocrm_setting_value('currency_code', 'USD')));
    }
}

if (! function_exists('velocrm_currency_symbol_for')) {
    function velocrm_currency_symbol_for(?string $currency = null): string
    {
        $currency = strtoupper((string) ($currency ?: velocrm_currency_code()));

        $symbols = [
            'THB' => '฿',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'CNY' => '¥',
            'SGD' => 'S$',
            'AUD' => 'A$',
            'CAD' => 'C$',
            'HKD' => 'HK$',
        ];

        if ($currency === velocrm_currency_code()) {
            return velocrm_currency_symbol();
        }

        return $symbols[$currency] ?? $currency.' ';
    }
}

if (! function_exists('velocrm_date_format')) {
    function velocrm_date_format(): string
    {
        return (string) velocrm_setting_value('date_format', 'd/m/Y');
    }
}

if (! function_exists('velocrm_app_name')) {
    function velocrm_app_name(): string
    {
        return (string) velocrm_setting_value('site_title', config('app.name', 'VeloCRM'));
    }
}

if (! function_exists('velocrm_company_name')) {
    function velocrm_company_name(): string
    {
        return (string) velocrm_setting_value('company_name', velocrm_app_name());
    }
}

if (! function_exists('velocrm_auth_headline')) {
    function velocrm_auth_headline(): string
    {
        return (string) velocrm_setting_value('auth_headline', __('Sign in to your workspace'));
    }
}

if (! function_exists('velocrm_auth_subtitle')) {
    function velocrm_auth_subtitle(): string
    {
        return (string) velocrm_setting_value('auth_subtitle', __('Continue managing leads, follow-ups, and customer records.'));
    }
}

if (! function_exists('velocrm_money')) {
    function velocrm_money(float|int|string|null $amount, ?string $currency = null, int $decimals = 2): string
    {
        $value = is_null($amount) ? 0 : (float) $amount;
        $currency = strtoupper((string) ($currency ?: velocrm_currency_code()));

        return velocrm_currency_symbol_for($currency).number_format($value, $decimals).' '.$currency;
    }
}

if (! function_exists('format_currency')) {
    function format_currency(float|int|string|null $amount, int $decimals = 2, ?string $currency = null): string
    {
        if ($currency !== null) {
            return velocrm_money($amount, $currency, $decimals);
        }

        $value = is_null($amount) ? 0 : (float) $amount;

        return velocrm_currency_symbol().number_format($value, $decimals);
    }
}

if (! function_exists('velocrm_baht_text')) {
    function velocrm_baht_text(float|int|string|null $amount): string
    {
        $value = $amount === null ? 0.0 : (float) str_replace(',', '', (string) $amount);
        $value = round(abs($value), 2);
        $baht = (int) floor($value);
        $satang = (int) round(($value - $baht) * 100);

        if ($satang === 100) {
            $baht++;
            $satang = 0;
        }

        $bahtText = $baht === 0
            ? 'ศูนย์บาท'
            : velocrm_thai_number_text($baht).'บาท';

        if ($satang === 0) {
            return $bahtText.'ถ้วน';
        }

        return $bahtText.velocrm_thai_number_text($satang).'สตางค์';
    }
}

if (! function_exists('velocrm_thai_number_text')) {
    function velocrm_thai_number_text(int $number): string
    {
        if ($number === 0) {
            return 'ศูนย์';
        }

        if ($number >= 1000000) {
            $millions = intdiv($number, 1000000);
            $remainder = $number % 1000000;

            return velocrm_thai_number_text($millions).'ล้าน'.($remainder > 0 ? velocrm_thai_number_under_million($remainder) : '');
        }

        return velocrm_thai_number_under_million($number);
    }
}

if (! function_exists('velocrm_thai_number_under_million')) {
    function velocrm_thai_number_under_million(int $number): string
    {
        $digitWords = ['', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า'];
        $positionWords = ['', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน'];
        $digits = str_split((string) $number);
        $lastPosition = count($digits) - 1;
        $text = '';

        foreach ($digits as $index => $digit) {
            $value = (int) $digit;
            $position = $lastPosition - $index;

            if ($value === 0) {
                continue;
            }

            $text .= match ($position) {
                0 => $value === 1 && $lastPosition > 0 ? 'เอ็ด' : $digitWords[$value],
                1 => match ($value) {
                    1 => 'สิบ',
                    2 => 'ยี่สิบ',
                    default => $digitWords[$value].'สิบ',
                },
                default => $digitWords[$value].$positionWords[$position],
            };
        }

        return $text;
    }
}

if (! function_exists('format_date')) {
    function format_date(mixed $value, ?string $format = null): string
    {
        if (blank($value)) {
            return '';
        }

        return Carbon::parse($value)->format($format ?: velocrm_date_format());
    }
}
