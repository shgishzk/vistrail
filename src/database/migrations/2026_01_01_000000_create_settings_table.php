<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value');
            $table->timestamps();
        });

        $now = now();

        $defaultSettings = [
            'GOOGLE_MAPS_DEFAULT_LAT' => env('GOOGLE_MAPS_DEFAULT_LAT', 35.0238868),
            'GOOGLE_MAPS_DEFAULT_LNG' => env('GOOGLE_MAPS_DEFAULT_LNG', 135.760201),
            'BUILDING_MAP_HALF_SIDE_KM' => env('BUILDING_MAP_HALF_SIDE_KM', 1.0),
            'ROOM_VISITED_ALERT_DAYS' => env('ROOM_VISITED_ALERT_DAYS', 90),
            'ROOM_NOT_AT_HOME_ALERT_DAYS' => env('ROOM_NOT_AT_HOME_ALERT_DAYS', 7),
            'GOOGLE_MARKER_HAS_LOCK_BACKGROUND' => env('GOOGLE_MARKER_HAS_LOCK_BACKGROUND', '#FFC107'),
            'GOOGLE_MARKER_HAS_LOCK_BORDER_COLOR' => env('GOOGLE_MARKER_HAS_LOCK_BORDER_COLOR', '#FF8F00'),
            'GOOGLE_MARKER_HAS_LOCK_GLYPH_COLOR' => env('GOOGLE_MARKER_HAS_LOCK_GLYPH_COLOR', '#FF8F00'),
            'GOOGLE_MARKER_NO_LOCK_BACKGROUND' => env('GOOGLE_MARKER_NO_LOCK_BACKGROUND', '#4CAF50'),
            'GOOGLE_MARKER_NO_LOCK_BORDER_COLOR' => env('GOOGLE_MARKER_NO_LOCK_BORDER_COLOR', '#388E3C'),
            'GOOGLE_MARKER_NO_LOCK_GLYPH_COLOR' => env('GOOGLE_MARKER_NO_LOCK_GLYPH_COLOR', '#388E3C'),
            'GOOGLE_MAPS_ASSIGNED_TERRITORY_BOUNDARY' => env('GOOGLE_MAPS_ASSIGNED_TERRITORY_BOUNDARY', trim(<<<'KML'
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
KML)),
        ];

        $rows = array_map(static function ($key) use ($defaultSettings, $now) {
            return [
                'key' => $key,
                'value' => (string) $defaultSettings[$key],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, array_keys($defaultSettings));

        DB::table('settings')->insert($rows);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
