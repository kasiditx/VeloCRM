<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\InvoiceItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class InvoiceItemCatalog
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(int $limit = 25): array
    {
        return self::items()
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function search(?string $query, int $limit = 8): array
    {
        $normalizedQuery = Str::lower(trim((string) $query));

        if ($normalizedQuery === '') {
            return self::all($limit);
        }

        return self::items()
            ->filter(function (array $item) use ($normalizedQuery): bool {
                $haystack = Str::lower(implode(' ', [
                    $item['name'] ?? '',
                    $item['code'] ?? '',
                    $item['sku'] ?? '',
                    $item['description'] ?? '',
                ]));

                return Str::contains($haystack, $normalizedQuery);
            })
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(string $key): ?array
    {
        return self::items()
            ->first(fn (array $item): bool => (string) ($item['key'] ?? '') === $key);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private static function items(): Collection
    {
        return self::configuredItems()
            ->merge(self::recentInvoiceItems())
            ->unique(fn (array $item): string => (string) ($item['key'] ?? $item['description'] ?? ''))
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private static function configuredItems(): Collection
    {
        return collect(config('invoice_catalog.items', []))
            ->map(fn (array $item): array => self::normalize($item, 'catalog'));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private static function recentInvoiceItems(): Collection
    {
        return InvoiceItem::query()
            ->select(['description', 'unit_price', 'wht_rate'])
            ->whereNotNull('description')
            ->latest('id')
            ->limit(40)
            ->get()
            ->map(function (InvoiceItem $item): array {
                return self::normalize([
                    'key' => 'recent-'.Str::slug($item->description).'-'.md5((string) $item->unit_price),
                    'name' => $item->description,
                    'code' => 'RECENT',
                    'sku' => null,
                    'description' => $item->description,
                    'unit_price' => (float) $item->unit_price,
                    'unit' => null,
                    'currency' => null,
                    'default_tax' => ((float) $item->wht_rate > 0) ? 'wht_'.rtrim(rtrim((string) $item->wht_rate, '0'), '.') : 'none',
                ], 'recent');
            });
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private static function normalize(array $item, string $source): array
    {
        return [
            'key' => (string) ($item['key'] ?? Str::uuid()),
            'name' => (string) ($item['name'] ?? $item['description'] ?? ''),
            'code' => filled($item['code'] ?? null) ? (string) $item['code'] : null,
            'sku' => filled($item['sku'] ?? null) ? (string) $item['sku'] : null,
            'description' => (string) ($item['description'] ?? $item['name'] ?? ''),
            'unit_price' => (float) ($item['unit_price'] ?? 0),
            'unit' => filled($item['unit'] ?? null) ? (string) $item['unit'] : null,
            'currency' => filled($item['currency'] ?? null) ? strtoupper((string) $item['currency']) : null,
            'default_tax' => (string) ($item['default_tax'] ?? 'none'),
            'source' => $source,
        ];
    }
}
