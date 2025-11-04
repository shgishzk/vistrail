<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id()->comment('Room ID');
            $table->foreignId('building_id')->comment('Building ID')->constrained('buildings')->onDelete('cascade');
            $table->string('number', 10)->comment('Room Number');
            $table->string('status', 32)->default('never_visited')->comment('Room Status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
