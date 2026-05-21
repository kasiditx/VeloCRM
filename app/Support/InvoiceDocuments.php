<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceDocuments
{
    public const TYPE_QUOTATION = 'quotation';

    public const TYPE_BILLING_NOTE = 'billing_note';

    public const TYPE_INVOICE = 'invoice';

    public const TYPE_TAX_INVOICE = 'tax_invoice';

    public const TYPE_RECEIPT = 'receipt';

    public const TYPE_TAX_INVOICE_RECEIPT = 'tax_invoice_receipt';

    public const DEFAULT_TYPE = self::TYPE_INVOICE;

    private const PREFIX_SETTING_PREFIX = 'document_number_prefix_';

    private const NEXT_SETTING_PREFIX = 'document_number_next_';

    public static function types(): array
    {
        return [
            self::TYPE_QUOTATION,
            self::TYPE_BILLING_NOTE,
            self::TYPE_INVOICE,
            self::TYPE_TAX_INVOICE,
            self::TYPE_RECEIPT,
            self::TYPE_TAX_INVOICE_RECEIPT,
        ];
    }

    public static function labels(): array
    {
        return [
            self::TYPE_QUOTATION => 'ใบเสนอราคา',
            self::TYPE_BILLING_NOTE => 'ใบวางบิล',
            self::TYPE_INVOICE => 'ใบแจ้งหนี้',
            self::TYPE_TAX_INVOICE => 'ใบกำกับภาษี',
            self::TYPE_RECEIPT => 'ใบเสร็จรับเงิน',
            self::TYPE_TAX_INVOICE_RECEIPT => 'ใบกำกับภาษี/ใบเสร็จรับเงิน',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::TYPE_QUOTATION => 'QUOTATION',
            self::TYPE_BILLING_NOTE => 'BILLING NOTE',
            self::TYPE_INVOICE => 'INVOICE',
            self::TYPE_TAX_INVOICE => 'TAX INVOICE',
            self::TYPE_RECEIPT => 'RECEIPT',
            self::TYPE_TAX_INVOICE_RECEIPT => 'TAX INVOICE / RECEIPT',
        ];
    }

    public static function footerLabels(): array
    {
        return [
            self::TYPE_QUOTATION => 'เอกสารนี้เป็นใบเสนอราคาและยังไม่ใช่ใบกำกับภาษีหรือใบเสร็จรับเงิน',
            self::TYPE_BILLING_NOTE => 'เอกสารนี้เป็นใบวางบิลสำหรับแจ้งยอดค้างชำระ',
            self::TYPE_INVOICE => 'เอกสารนี้เป็นใบแจ้งหนี้ กรุณาชำระเงินตามกำหนด',
            self::TYPE_TAX_INVOICE => 'เอกสารนี้เป็นใบกำกับภาษี',
            self::TYPE_RECEIPT => 'เอกสารนี้เป็นหลักฐานการรับชำระเงิน',
            self::TYPE_TAX_INVOICE_RECEIPT => 'เอกสารนี้เป็นใบกำกับภาษีและใบเสร็จรับเงิน',
        ];
    }

    public static function prefixes(): array
    {
        return [
            self::TYPE_QUOTATION => 'QUO',
            self::TYPE_BILLING_NOTE => 'BN',
            self::TYPE_INVOICE => 'INV',
            self::TYPE_TAX_INVOICE => 'TAX',
            self::TYPE_RECEIPT => 'REC',
            self::TYPE_TAX_INVOICE_RECEIPT => 'TIR',
        ];
    }

    public static function normalize(?string $type): string
    {
        $type = $type ?: self::DEFAULT_TYPE;

        return in_array($type, self::types(), true) ? $type : self::DEFAULT_TYPE;
    }

    public static function label(?string $type): string
    {
        return self::labels()[self::normalize($type)];
    }

    public static function englishLabel(?string $type): string
    {
        return self::englishLabels()[self::normalize($type)];
    }

    public static function footer(?string $type): string
    {
        return self::footerLabels()[self::normalize($type)];
    }

    public static function prefix(?string $type): string
    {
        $type = self::normalize($type);
        $default = self::prefixes()[$type];

        return strtoupper(trim((string) Setting::get(self::prefixSettingKey($type), $default))) ?: $default;
    }

    public static function nextNumber(?string $type, mixed $date = null): string
    {
        $type = self::normalize($type);
        $year = self::year($date);

        return DB::transaction(function () use ($type, $year): string {
            $key = self::nextSettingKey($type, $year);
            $setting = Setting::query()->where('key', $key)->lockForUpdate()->first();
            $sequence = max((int) ($setting?->value ?? 1), 1);

            do {
                $number = self::formatNumber($type, $year, $sequence);
                $sequence++;
            } while (Invoice::query()->where('number', $number)->exists());

            Setting::set($key, (string) $sequence);

            return $number;
        });
    }

    public static function previewNumber(?string $type, mixed $date = null): string
    {
        $type = self::normalize($type);
        $year = self::year($date);
        $sequence = max((int) Setting::get(self::nextSettingKey($type, $year), 1), 1);

        return self::formatNumber($type, $year, $sequence);
    }

    public static function isGeneratedNumber(?string $number): bool
    {
        if ($number === null || trim($number) === '') {
            return true;
        }

        $prefixes = array_map(
            static fn (string $type): string => preg_quote(self::prefix($type), '/'),
            self::types()
        );

        return preg_match('/^('.implode('|', $prefixes).')-\d{4}-\d{4}$/', trim($number)) === 1;
    }

    public static function prefixSettingKey(string $type): string
    {
        return self::PREFIX_SETTING_PREFIX.self::normalize($type);
    }

    public static function nextSettingKey(string $type, int|string|null $year = null): string
    {
        return self::NEXT_SETTING_PREFIX.self::normalize($type).'_'.($year ?: now()->year);
    }

    private static function formatNumber(string $type, int $year, int $sequence): string
    {
        return sprintf('%s-%d-%04d', self::prefix($type), $year, $sequence);
    }

    private static function year(mixed $date): int
    {
        if ($date === null || $date === '') {
            return (int) now()->format('Y');
        }

        return (int) Carbon::parse($date)->format('Y');
    }
}
