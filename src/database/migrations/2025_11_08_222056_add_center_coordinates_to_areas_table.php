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
            $table->decimal('center_lat', 10, 7)
                ->default(0)
                ->after('boundary_kml')
                ->comment('Center latitude');
            $table->decimal('center_lng', 11, 7)
                ->default(0)
                ->after('center_lat')
                ->comment('Center longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->dropColumn(['center_lat', 'center_lng']);
        });
    }
};
