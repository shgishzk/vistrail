<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buildings', function (Blueprint $table) {
            $table->id()->comment('Building ID');
            $table->string('name', 200)->comment('Building Name');
            $table->string('self_lock_type')->comment('Self Lock Type');
            $table->unsignedTinyInteger('is_public')->comment('Public Flag');
            $table->decimal('lat', 10, 7)->comment('Latitude');
            $table->decimal('lng', 10, 7)->comment('Longitude');
            $table->string('url')->nullable()->comment('Source URL');
            $table->text('memo')->nullable()->comment('Memo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buildings');
    }
};
