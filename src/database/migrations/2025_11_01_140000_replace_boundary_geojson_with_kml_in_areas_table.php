<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            if (Schema::hasColumn('areas', 'boundary_geojson')) {
                $table->dropColumn('boundary_geojson');
            }

            if (!Schema::hasColumn('areas', 'boundary_kml')) {
                $table->longText('boundary_kml')->nullable()->after('name')->comment('Boundary KML');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            if (Schema::hasColumn('areas', 'boundary_kml')) {
                $table->dropColumn('boundary_kml');
            }

            if (!Schema::hasColumn('areas', 'boundary_geojson')) {
                $table->longText('boundary_geojson')->nullable()->after('name')->comment('Boundary GeoJSON');
            }
        });
    }
};
