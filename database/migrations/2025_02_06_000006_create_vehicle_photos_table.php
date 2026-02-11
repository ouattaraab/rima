<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_photos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->enum('photo_type', ['front', 'rear', 'side', 'additional', 'registration_card']);
            $table->string('file_path');
            $table->string('thumbnail_path')->nullable();
            $table->string('original_name')->nullable();
            $table->integer('size_bytes')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->decimal('gps_latitude', 10, 8)->nullable();
            $table->decimal('gps_longitude', 11, 8)->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->string('comment', 500)->nullable();
            $table->timestamps();

            $table->index('vehicle_id');
            $table->index('photo_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_photos');
    }
};
