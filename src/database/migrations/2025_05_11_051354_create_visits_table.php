<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisitsTable extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id()->comment('Visit ID');
            $table->foreignId('user_id')
                ->comment('User ID')
                ->constrained()->onDelete('cascade');
            $table->date('start_date')->comment('Start Date');
            $table->date('end_date')->nullable()->comment('End Date');
            $table->text('memo')->nullable()->comment('Memo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
}