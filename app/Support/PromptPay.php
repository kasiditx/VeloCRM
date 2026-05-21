<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Invoice;

class PromptPay
{
    private const AID = 'A000000677010111';

    private const TAG_PAYLOAD_FORMAT = '00';

    private const TAG_POINT_OF_INITIATION = '01';

    private const TAG_MERCHANT_ACCOUNT = '29';

    private const TAG_MERCHANT_ACCOUNT_AID = '00';

    private const TAG_MOBILE = '01';

    private const TAG_NATIONAL_ID = '02';

    private const TAG_TRANSACTION_CURRENCY = '53';

    private const TAG_TRANSACTION_AMOUNT = '54';

    private const TAG_COUNTRY_CODE = '58';

    private const TAG_CRC = '63';

    private const THB_NUMERIC_CODE = '764';

    public static function payload(string $identifier, float|int|string $amount): string
    {
        $target = self::normalizeIdentifier($identifier);

        if ($target === null) {
            throw new \InvalidArgumentException('PromptPay ID must be a Thai mobile number, national ID, or corporate tax ID.');
        }

        $payload = self::tag(self::TAG_PAYLOAD_FORMAT, '01')
            .self::tag(self::TAG_POINT_OF_INITIATION, '12')
            .self::tag(self::TAG_MERCHANT_ACCOUNT, self::merchantAccountValue($target))
            .self::tag(self::TAG_TRANSACTION_CURRENCY, self::THB_NUMERIC_CODE)
            .self::tag(self::TAG_TRANSACTION_AMOUNT, self::formatAmount($amount))
            .self::tag(self::TAG_COUNTRY_CODE, 'TH');

        $payloadForCrc = $payload.self::TAG_CRC.'04';

        return $payloadForCrc.self::crc16($payloadForCrc);
    }

    public static function qrDataUri(string $identifier, float|int|string $amount, int $size = 180): string
    {
        $qrCode = app('qrcode');
        $svg = (string) $qrCode
            ->format('svg')
            ->size($size)
            ->margin(1)
            ->generate(self::payload($identifier, $amount));

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    public static function invoiceQrDataUri(Invoice $invoice, ?string $identifier, int $size = 180): ?string
    {
        if (! self::shouldDisplayForInvoice($invoice, $identifier)) {
            return null;
        }

        return self::qrDataUri((string) $identifier, (float) $invoice->balance_due, $size);
    }

    public static function shouldDisplayForInvoice(Invoice $invoice, ?string $identifier): bool
    {
        return $identifier !== null
            && self::normalizeIdentifier($identifier) !== null
            && strtoupper((string) ($invoice->currency ?: velocrm_currency_code())) === 'THB'
            && (float) $invoice->balance_due > 0;
    }

    public static function normalizeIdentifier(string $identifier): ?array
    {
        $digits = preg_replace('/\D/', '', $identifier) ?? '';

        if (str_starts_with($digits, '66') && strlen($digits) === 11) {
            return ['tag' => self::TAG_MOBILE, 'value' => '00'.$digits];
        }

        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            return ['tag' => self::TAG_MOBILE, 'value' => '0066'.substr($digits, 1)];
        }

        if (str_starts_with($digits, '0066') && strlen($digits) === 13) {
            return ['tag' => self::TAG_MOBILE, 'value' => $digits];
        }

        if (strlen($digits) === 13) {
            return ['tag' => self::TAG_NATIONAL_ID, 'value' => $digits];
        }

        return null;
    }

    private static function merchantAccountValue(array $target): string
    {
        return self::tag(self::TAG_MERCHANT_ACCOUNT_AID, self::AID)
            .self::tag($target['tag'], $target['value']);
    }

    private static function tag(string $id, string $value): string
    {
        return $id.str_pad((string) strlen($value), 2, '0', STR_PAD_LEFT).$value;
    }

    private static function formatAmount(float|int|string $amount): string
    {
        return number_format(max((float) $amount, 0), 2, '.', '');
    }

    private static function crc16(string $payload): string
    {
        $crc = 0xFFFF;
        $length = strlen($payload);

        for ($offset = 0; $offset < $length; $offset++) {
            $crc ^= ord($payload[$offset]) << 8;

            for ($bit = 0; $bit < 8; $bit++) {
                $crc = ($crc & 0x8000) !== 0
                    ? (($crc << 1) ^ 0x1021)
                    : ($crc << 1);
                $crc &= 0xFFFF;
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}
