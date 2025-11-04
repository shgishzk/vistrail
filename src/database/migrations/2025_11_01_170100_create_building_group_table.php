<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('building_group', function (Blueprint $table) {
            $table->id()->comment('Building Group ID');
            $table->foreignId('building_id')->constrained()->cascadeOnDelete()->comment('Building ID');
            $table->foreignId('group_id')->constrained()->cascadeOnDelete()->comment('Group ID');
            $table->timestamps();

            $table->unique(['building_id', 'group_id'], 'building_group_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('building_group');
    }
};
