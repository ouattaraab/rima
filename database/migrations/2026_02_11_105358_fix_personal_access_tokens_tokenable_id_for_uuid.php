<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix tokenable_id to support UUID strings instead of bigint.
     */
    public function up(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropIndex('personal_access_tokens_tokenable_type_tokenable_id_index');
        });

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->string('tokenable_id', 36)->change();
        });

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->index(['tokenable_type', 'tokenable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropIndex(['tokenable_type', 'tokenable_id']);
        });

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->unsignedBigInteger('tokenable_id')->change();
        });

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->index(['tokenable_type', 'tokenable_id']);
        });
    }
};
