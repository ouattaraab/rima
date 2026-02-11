<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_queue', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignUuid('agent_id')->constrained('users');
            $table->enum('sync_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->integer('retry_count')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index('sync_status');
            $table->index('agent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_queue');
    }
};
