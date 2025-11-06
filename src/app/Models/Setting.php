<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    public const KEY_GOOGLE_MAPS_DEFAULT_LAT = 'GOOGLE_MAPS_DEFAULT_LAT';
    public const KEY_GOOGLE_MAPS_DEFAULT_LNG = 'GOOGLE_MAPS_DEFAULT_LNG';
    public const KEY_BUILDING_MAP_HALF_SIDE_KM = 'BUILDING_MAP_HALF_SIDE_KM';
    public const KEY_ROOM_VISITED_ALERT_DAYS = 'ROOM_VISITED_ALERT_DAYS';
    public const KEY_ROOM_NOT_AT_HOME_ALERT_DAYS = 'ROOM_NOT_AT_HOME_ALERT_DAYS';

    /**
     * Cached key/value settings to avoid repeated database queries per request.
     *
     * @var array<string, string>|null
     */
    protected static ?array $cache = null;

    /**
     * @var string[]
     */
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Settings are always stored as strings in the database.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'value' => 'string',
    ];

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function fieldDefinitions(): array
    {
        return [
            self::KEY_GOOGLE_MAPS_DEFAULT_LAT => [
                'type' => 'float',
                'default' => 35.0238868,
                'label' => 'Google マップ初期位置（緯度）',
                'description' => 'マンションマップの中心となる場所の緯度を設定します。',
                'input' => [
                    'type' => 'number',
                    'step' => '0.0000001',
                    'min' => -90,
                    'max' => 90,
                ],
            ],
            self::KEY_GOOGLE_MAPS_DEFAULT_LNG => [
                'type' => 'float',
                'default' => 135.760201,
                'label' => 'Google マップ初期位置（経度）',
                'description' => 'マンションマップの中心となる場所の経度を設定します。',
                'input' => [
                    'type' => 'number',
                    'step' => '0.0000001',
                    'min' => -180,
                    'max' => 180,
                ],
            ],
            self::KEY_BUILDING_MAP_HALF_SIDE_KM => [
                'type' => 'float',
                'default' => 1.0,
                'label' => 'マンションマップ表示範囲（km）',
                'description' => '地図の中心から何 km 四方の範囲でマンションを表示するか設定します。',
                'input' => [
                    'type' => 'number',
                    'step' => '0.1',
                    'min' => 0.1,
                    'max' => 100,
                ],
            ],
            self::KEY_ROOM_VISITED_ALERT_DAYS => [
                'type' => 'int',
                'default' => 90,
                'label' => '訪問済みのアラート日数',
                'description' => '「在宅」「投函」などに更新された部屋へアラートを表示する期間（日数）を設定します。',
                'input' => [
                    'type' => 'number',
                    'step' => '1',
                    'min' => 0,
                    'max' => 3650,
                ],
            ],
            self::KEY_ROOM_NOT_AT_HOME_ALERT_DAYS => [
                'type' => 'int',
                'default' => 7,
                'label' => '不在のアラート日数',
                'description' => '「不在」のまま一定期間経過した部屋へアラートを表示する期間（日数）を設定します。',
                'input' => [
                    'type' => 'number',
                    'step' => '1',
                    'min' => 0,
                    'max' => 365,
                ],
            ],
        ];
    }

    public static function managedKeys(): array
    {
        return array_keys(static::fieldDefinitions());
    }

    public static function defaults(): array
    {
        $defaults = [];

        foreach (static::fieldDefinitions() as $key => $definition) {
            $envValue = env($key, $definition['default']);

            if (($definition['type'] ?? null) === 'float') {
                $defaults[$key] = (float) $envValue;
            } elseif (($definition['type'] ?? null) === 'int') {
                $defaults[$key] = (int) $envValue;
            } else {
                $defaults[$key] = $envValue;
            }
        }

        return $defaults;
    }

    public static function flushCache(): void
    {
        static::$cache = null;
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        if (static::$cache === null) {
            static::$cache = static::query()->pluck('value', 'key')->all();
        }

        return static::$cache[$key] ?? $default;
    }

    public static function getFloat(string $key, float $default = 0.0): float
    {
        return (float) static::getValue($key, $default);
    }

    public static function getInt(string $key, int $default = 0): int
    {
        return (int) static::getValue($key, $default);
    }

    public static function setValue(string $key, float|int|string $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value],
        );

        static::flushCache();
    }
}
