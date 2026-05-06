<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, mixed $default = null, bool $decrypt = false): mixed
    {
        $setting = self::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        $value = $setting->value;

        if ($decrypt && !empty($value)) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return $value;
            }
        }

        return $value;
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, mixed $value, bool $encrypt = false): void
    {
        if ($encrypt && !empty($value)) {
            $value = Crypt::encryptString((string) $value);
        }

        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
