<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePinsTable extends Migration
{
    public function up(): void
    {
        Schema::create('pins', function (Blueprint $table) {
            $table->id()->comment('Pin ID');
            $table->foreignId('user_id')->comment('User ID')->constrained()->onDelete('cascade');
            $table->foreignId('area_id')->comment('Area ID')->constrained()->onDelete('cascade');
            $table->foreignId('visit_id')->nullable()->comment('Visit ID')->constrained()->onDelete('set null');

            $table->decimal('lat', 10, 7)->comment('Latitude');
            $table->decimal('lng', 10, 7)->comment('Longitude');
            $table->enum('status', ['visited', 'not_visited'])->comment('Visit Status');
            $table->text('memo')->nullable()->comment('Memo');
            $table->timestamps();

            $table->index(['area_id', 'lat', 'lng']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pins');
    }
}
