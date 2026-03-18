<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('data_origin', 20)->nullable()->default('mobile')->after('form_status');
            // 'mobile' = collecte terrain via app mobile
            // 'import' = import Excel back-office
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('data_origin');
        });
    }
};
