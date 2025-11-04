<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAreasTable extends Migration
{
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id()->comment('Area ID');
            $table->string('number')->comment('Area Number');
            $table->string('name')->nullable()->comment('Area Name');
            $table->longText('boundary_kml')->comment('Boundary KML');
            $table->text('memo')->nullable()->comment('Memo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
}
