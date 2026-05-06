<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;

if (! function_exists('velocrm_setting_value')) {
    function velocrm_setting_value(string $key, mixed $default = null): mixed
    {
        try {
            if (! file_exists(storage_path('installed')) || ! Schema::hasTable('settings')) {
                return $default;
            }

            return \App\Models\Setting::get($key, $default);
        } catch (\Throwable) {
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

if (! function_exists('format_currency')) {
    function format_currency(float|int|string|null $amount, int $decimals = 2): string
    {
        $value = is_null($amount) ? 0 : (float) $amount;

        return velocrm_currency_symbol() . number_format($value, $decimals);
    }
}

if (! function_exists('format_date')) {
    function format_date(mixed $value, ?string $format = null): string
    {
        if (blank($value)) {
            return '';
        }

        return \Illuminate\Support\Carbon::parse($value)->format($format ?: velocrm_date_format());
    }
}
