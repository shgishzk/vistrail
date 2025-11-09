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
    public const KEY_GOOGLE_MARKER_HAS_LOCK_BACKGROUND = 'GOOGLE_MARKER_HAS_LOCK_BACKGROUND';
    public const KEY_GOOGLE_MARKER_HAS_LOCK_BORDER_COLOR = 'GOOGLE_MARKER_HAS_LOCK_BORDER_COLOR';
    public const KEY_GOOGLE_MARKER_HAS_LOCK_GLYPH_COLOR = 'GOOGLE_MARKER_HAS_LOCK_GLYPH_COLOR';
    public const KEY_GOOGLE_MARKER_NO_LOCK_BACKGROUND = 'GOOGLE_MARKER_NO_LOCK_BACKGROUND';
    public const KEY_GOOGLE_MARKER_NO_LOCK_BORDER_COLOR = 'GOOGLE_MARKER_NO_LOCK_BORDER_COLOR';
    public const KEY_GOOGLE_MARKER_NO_LOCK_GLYPH_COLOR = 'GOOGLE_MARKER_NO_LOCK_GLYPH_COLOR';
    public const KEY_GOOGLE_MAPS_ASSIGNED_TERRITORY_BOUNDARY = 'GOOGLE_MAPS_ASSIGNED_TERRITORY_BOUNDARY';
    public const KEY_AREA_VACANCY_REQUIRE_START_ELAPSED_ENABLED = 'AREA_VACANCY_REQUIRE_START_ELAPSED_ENABLED';
    public const KEY_AREA_VACANCY_REQUIRE_START_ELAPSED_DAYS = 'AREA_VACANCY_REQUIRE_START_ELAPSED_DAYS';
    public const KEY_AREA_VACANCY_REQUIRE_END_ELAPSED_ENABLED = 'AREA_VACANCY_REQUIRE_END_ELAPSED_ENABLED';
    public const KEY_AREA_VACANCY_REQUIRE_END_ELAPSED_DAYS = 'AREA_VACANCY_REQUIRE_END_ELAPSED_DAYS';

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
                'label' => '緯度',
                'description' => 'マンションマップの中心となる場所の緯度を設定します。',
                'group' => 'google_maps',
                'group_label' => 'Google マップ',
                'group_order' => 1,
                'section' => 'google_maps_default_position',
                'section_label' => 'Google マップ初期位置',
                'section_order' => 1,
                'field_order' => 1,
                'input' => [
                    'type' => 'text',
                    'inputmode' => 'decimal',
                    'placeholder' => '例: 35.0238868',
                ],
            ],
            self::KEY_GOOGLE_MAPS_DEFAULT_LNG => [
                'type' => 'float',
                'default' => 135.760201,
                'label' => '経度',
                'description' => 'マンションマップの中心となる場所の経度を設定します。',
                'group' => 'google_maps',
                'group_label' => 'Google マップ',
                'group_order' => 1,
                'section' => 'google_maps_default_position',
                'section_label' => 'Google マップ初期位置',
                'section_order' => 1,
                'field_order' => 2,
                'input' => [
                    'type' => 'text',
                    'inputmode' => 'decimal',
                    'placeholder' => '例: 135.760201',
                ],
            ],
            self::KEY_BUILDING_MAP_HALF_SIDE_KM => [
                'type' => 'float',
                'default' => 1.0,
                'label' => 'マンション表示範囲（km）',
                'description' => '地図の中心から何 km 四方の範囲でマンションを表示するか設定します。',
                'group' => 'google_maps',
                'group_label' => 'Google マップ',
                'group_order' => 1,
                'section' => 'building_map_range',
                'section_label' => 'マンションマップ表示範囲',
                'section_order' => 2,
                'field_order' => 1,
                'input' => [
                    'type' => 'text',
                    'inputmode' => 'decimal',
                    'placeholder' => '例: 1.0',
                ],
            ],
            self::KEY_ROOM_VISITED_ALERT_DAYS => [
                'type' => 'int',
                'default' => 90,
                'label' => '訪問済みのアラート日数',
                'description' => '「在宅」「投函」などに更新された部屋へアラートを表示する期間（日数）を設定します。',
                'group' => 'building_map',
                'group_label' => 'マンションマップ',
                'group_order' => 2,
                'field_order' => 1,
                'input' => [
                    'type' => 'text',
                    'inputmode' => 'numeric',
                    'placeholder' => '例: 90',
                ],
        ],
            self::KEY_ROOM_NOT_AT_HOME_ALERT_DAYS => [
                'type' => 'int',
                'default' => 7,
                'label' => '不在のアラート日数',
                'description' => '「不在」のまま一定期間経過した部屋へアラートを表示する期間（日数）を設定します。',
                'group' => 'building_map',
                'group_label' => 'マンションマップ',
                'group_order' => 2,
                'field_order' => 2,
                'input' => [
                    'type' => 'text',
                    'inputmode' => 'numeric',
                    'placeholder' => '例: 7',
                ],
            ],
            self::KEY_GOOGLE_MARKER_HAS_LOCK_BACKGROUND => [
                'type' => 'string',
                'default' => '#EA4335',
                'label' => 'オートロックあり（背景）',
                'description' => 'オートロックありマンションのマーカー背景色です。',
                'group' => 'google_maps',
                'group_label' => 'Google マップ',
                'group_order' => 1,
                'section' => 'marker_style_has_lock',
                'section_label' => 'マーカー（オートロックあり）',
                'section_order' => 3,
                'field_order' => 1,
                'input' => [
                    'type' => 'color',
                ],
            ],
            self::KEY_GOOGLE_MARKER_HAS_LOCK_BORDER_COLOR => [
                'type' => 'string',
                'default' => '#C5221F',
                'label' => 'オートロックあり（枠）',
                'description' => 'オートロックありマンションのマーカー枠線色です。',
                'group' => 'google_maps',
                'group_label' => 'Google マップ',
                'group_order' => 1,
                'section' => 'marker_style_has_lock',
                'section_label' => 'マーカー（オートロックあり）',
                'section_order' => 3,
                'field_order' => 2,
                'input' => [
                    'type' => 'color',
                ],
            ],
            self::KEY_GOOGLE_MARKER_HAS_LOCK_GLYPH_COLOR => [
                'type' => 'string',
                'default' => '#FFFFFF',
                'label' => 'オートロックあり（アイコン）',
                'description' => 'オートロックありマンションのマーカー内アイコン色です。',
                'group' => 'google_maps',
                'group_label' => 'Google マップ',
                'group_order' => 1,
                'section' => 'marker_style_has_lock',
                'section_label' => 'マーカー（オートロックあり）',
                'section_order' => 3,
                'field_order' => 3,
                'input' => [
                    'type' => 'color',
                ],
            ],
            self::KEY_GOOGLE_MARKER_NO_LOCK_BACKGROUND => [
                'type' => 'string',
                'default' => '#4CAF50',
                'label' => 'オートロックなし（背景）',
                'description' => 'オートロックなしマンションのマーカー背景色です。',
                'group' => 'google_maps',
                'group_label' => 'Google マップ',
                'group_order' => 1,
                'section' => 'marker_style_no_lock',
                'section_label' => 'マーカー（オートロックなし）',
                'section_order' => 4,
                'field_order' => 1,
                'input' => [
                    'type' => 'color',
                ],
            ],
            self::KEY_GOOGLE_MARKER_NO_LOCK_BORDER_COLOR => [
                'type' => 'string',
                'default' => '#388E3C',
                'label' => 'オートロックなし（枠）',
                'description' => 'オートロックなしマンションのマーカー枠線色です。',
                'group' => 'google_maps',
                'group_label' => 'Google マップ',
                'group_order' => 1,
                'section' => 'marker_style_no_lock',
                'section_label' => 'マーカー（オートロックなし）',
                'section_order' => 4,
                'field_order' => 2,
                'input' => [
                    'type' => 'color',
                ],
            ],
            self::KEY_GOOGLE_MARKER_NO_LOCK_GLYPH_COLOR => [
                'type' => 'string',
                'default' => '#388E3C',
                'label' => 'オートロックなし（アイコン）',
                'description' => 'オートロックなしマンションのマーカー内アイコン色です。',
                'group' => 'google_maps',
                'group_label' => 'Google マップ',
                'group_order' => 1,
                'section' => 'marker_style_no_lock',
                'section_label' => 'マーカー（オートロックなし）',
                'section_order' => 4,
                'field_order' => 3,
                'input' => [
                    'type' => 'color',
                ],
            ],
            self::KEY_GOOGLE_MAPS_ASSIGNED_TERRITORY_BOUNDARY => [
                'type' => 'string',
                'default' => trim(<<<'KML'
<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2">
  <Document>
    <Placemark>
      <name>Assigned Territory Boundary</name>
      <Polygon>
        <outerBoundaryIs>
          <LinearRing>
            <coordinates>
              135.8097895142034,35.016822663688465,0
              135.7963627854132,35.017102089918986,0
              135.7948305798865,35.01722662634175,0
              135.79475284564097,35.01691512619964,0
              135.7935010964614,35.01689133809732,0
              135.79342269826367,35.01722574096474,0
              135.7920930424106,35.01685218392447,0
              135.788318434658,35.01661564873406,0
              135.78253531026078,35.01736328155626,0
              135.7824285039411,35.023954114796915,0
              135.78187778975814,35.02393796175924,0
              135.7818970410715,35.0253038387143,0
              135.77863613806738,35.02523742795895,0
              135.77855991327874,35.02187966698486,0
              135.7714673707042,35.02174802907746,0
              135.7717268426114,35.02899758320975,0
              135.769621917379,35.02916682126747,0
              135.7572809626104,35.029059319063784,0
              135.75489714604544,35.029695237988435,0
              135.752078,35.029711,0
              135.75187999999997,35.03369899999999,0
              135.751298,35.035533,0
              135.751816,35.03733900000001,0
              135.751778,35.04064399999999,0
              135.75707,35.04353100000001,0
              135.762791,35.04378100000002,0
              135.760529,35.047058,0
              135.759251,35.04645500000001,0
              135.748585,35.04625200000002,0
              135.74693500000004,35.04566699999999,0
              135.745854,35.04570600000001,0
              135.743974,35.045415,0
              135.743768,35.045168,0
              135.74225499999997,35.044992,0
              135.738199,35.044794,0
              135.738507,35.040886,0
              135.733745,35.04039200000002,0
              135.733357,35.039092,0
              135.73211199999997,35.035075000000006,0
              135.735214,35.035019,0
              135.73613799999998,35.03446,0
              135.738806,35.034333,0
              135.738689,35.033612,0
              135.738849,35.032079,0
              135.738792,35.03096899999999,0
              135.739468,35.029656000000024,0
              135.739367,35.026083,0
              135.739319,35.01844300000001,0
              135.742602,35.01822800000001,0
              135.747095,35.01812500000001,0
              135.748376,35.017346999999994,0
              135.751801,35.01732100000002,0
              135.7715689088955,35.01759131466224,0
              135.7717193411938,35.009111669356265,0
              135.77896141175077,35.009498235127495,0
              135.7823649531376,35.00921686993657,0
              135.78568905706612,35.009557746424434,0
              135.7880820821915,35.00937898852457,0
              135.7893892356328,35.008937598853414,0
              135.7902451451536,35.00779182895502,0
              135.7904269487763,35.0070248333571,0
              135.791211254427,35.00705646230426,0
              135.7930368782471,35.006514788884594,0
              135.80148891369356,35.00855244295466,0
              135.8040830348283,35.014036930206764,0
              135.8097895142034,35.016822663688465,0
            </coordinates>
          </LinearRing>
        </outerBoundaryIs>
      </Polygon>
    </Placemark>
  </Document>
</kml>
KML),
                'label' => '会衆の割当区域（KML）',
                'description' => 'KML形式で会衆の割当区域を定義できます。',
                'group' => 'google_maps',
                'group_label' => 'Google マップ',
                'group_order' => 1,
                'section' => 'territory_assignment',
                'section_label' => '会衆の割当区域',
                'section_order' => 5,
                'field_order' => 1,
                'input' => [
                    'type' => 'textarea',
                    'rows' => 12,
                ],
            ],
            self::KEY_AREA_VACANCY_REQUIRE_START_ELAPSED_ENABLED => [
                'type' => 'int',
                'default' => 0,
                'label' => '訪問開始日によるクールダウンを有効化',
                'description' => '最新訪問の開始日から指定日数が経過するまで空き区域として扱いません。',
                'group' => 'areas',
                'group_label' => '区域',
                'group_order' => 3,
                'section' => 'areas_vacancy',
                'section_label' => '空き区域の判定',
                'section_order' => 1,
                'field_order' => 1,
                'input' => [
                    'type' => 'checkbox',
                    'data-controls' => '#setting-area-vacancy-require-start-elapsed-days',
                ],
            ],
            self::KEY_AREA_VACANCY_REQUIRE_START_ELAPSED_DAYS => [
                'type' => 'int',
                'default' => 30,
                'label' => '訪問開始から経過させる日数 (n)',
                'description' => '最新訪問の開始日からこの日数が過ぎていない区域は「空き区域」に含めません。',
                'group' => 'areas',
                'group_label' => '区域',
                'group_order' => 3,
                'section' => 'areas_vacancy',
                'section_label' => '空き区域の判定',
                'section_order' => 1,
                'field_order' => 2,
                'input' => [
                    'type' => 'number',
                    'min' => '0',
                    'step' => '1',
                    'placeholder' => '例: 30',
                    'data-controlled-by' => '#setting-area-vacancy-require-start-elapsed-enabled',
                ],
            ],
            self::KEY_AREA_VACANCY_REQUIRE_END_ELAPSED_ENABLED => [
                'type' => 'int',
                'default' => 0,
                'label' => '訪問終了日によるクールダウンを有効化',
                'description' => '最新訪問の終了日から指定日数が経過するまで空き区域として扱いません。',
                'group' => 'areas',
                'group_label' => '区域',
                'group_order' => 3,
                'section' => 'areas_vacancy',
                'section_label' => '空き区域の判定',
                'section_order' => 1,
                'field_order' => 3,
                'input' => [
                    'type' => 'checkbox',
                    'data-controls' => '#setting-area-vacancy-require-end-elapsed-days',
                ],
            ],
            self::KEY_AREA_VACANCY_REQUIRE_END_ELAPSED_DAYS => [
                'type' => 'int',
                'default' => 14,
                'label' => '訪問終了から経過させる日数 (i)',
                'description' => '最新訪問の終了日からこの日数が過ぎていない区域は「空き区域」に含めません。',
                'group' => 'areas',
                'group_label' => '区域',
                'group_order' => 3,
                'section' => 'areas_vacancy',
                'section_label' => '空き区域の判定',
                'section_order' => 1,
                'field_order' => 4,
                'input' => [
                    'type' => 'number',
                    'min' => '0',
                    'step' => '1',
                    'placeholder' => '例: 14',
                    'data-controlled-by' => '#setting-area-vacancy-require-end-elapsed-enabled',
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
                $stringValue = (string) $envValue;
                if (($definition['type'] ?? null) === 'string' && str_starts_with($stringValue, '#')) {
                    $stringValue = strtoupper($stringValue);
                }
                $defaults[$key] = $stringValue;
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
