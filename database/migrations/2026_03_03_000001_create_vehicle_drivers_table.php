<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_drivers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('vehicle_id');
            $table->string('direction', 100);
            $table->string('matricule', 7);
            $table->string('driver_license', 50);
            $table->boolean('is_primary')->default(false);
            $table->tinyInteger('position')->unsigned()->default(0);
            $table->timestamps();

            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->index('vehicle_id');
            $table->unique(['vehicle_id', 'matricule']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_drivers');
    }
};
