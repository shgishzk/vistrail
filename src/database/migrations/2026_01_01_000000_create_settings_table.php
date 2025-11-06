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
            $table->string('value');
            $table->timestamps();
        });

        $now = now();

        $defaultSettings = [
            'GOOGLE_MAPS_DEFAULT_LAT' => env('GOOGLE_MAPS_DEFAULT_LAT', 35.0238868),
            'GOOGLE_MAPS_DEFAULT_LNG' => env('GOOGLE_MAPS_DEFAULT_LNG', 135.760201),
            'BUILDING_MAP_HALF_SIDE_KM' => env('BUILDING_MAP_HALF_SIDE_KM', 1.0),
            'ROOM_VISITED_ALERT_DAYS' => env('ROOM_VISITED_ALERT_DAYS', 90),
            'ROOM_NOT_AT_HOME_ALERT_DAYS' => env('ROOM_NOT_AT_HOME_ALERT_DAYS', 7),
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

