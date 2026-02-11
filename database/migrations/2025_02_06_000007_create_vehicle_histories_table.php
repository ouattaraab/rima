<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->enum('action', ['created', 'updated', 'validated', 'rejected', 'status_changed', 'synchronized']);
            $table->foreignUuid('changed_by')->constrained('users');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index('vehicle_id');
            $table->index('changed_by');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_histories');
    }
};
