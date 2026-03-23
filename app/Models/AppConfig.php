<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppConfig extends Model
{
    protected $table = 'app_config';
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::allCached();
        return $all[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('app_config_all');
    }

    /** Ambil semua config sekaligus, di-cache 60 menit */
    public static function allCached(): \Illuminate\Support\Collection
    {
        return Cache::remember('app_config_all', 3600, function () {
            return static::all()->pluck('value', 'key');
        });
    }
}
