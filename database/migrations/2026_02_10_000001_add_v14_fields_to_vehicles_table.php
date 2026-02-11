<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Section 5.7 : Identification de l'utilisateur
            $table->string('user_direction', 100)->nullable()->after('form_status')
                ->comment('Direction de l\'utilisateur');
            $table->string('user_matricule', 7)->nullable()->after('user_direction')
                ->comment('Matricule employe (7 caracteres)');
            $table->string('user_driver_license', 50)->nullable()->after('user_matricule')
                ->comment('N° Permis de conduire');

            // Section 5.9 : Donnees post-inventaires
            $table->enum('financing_mode', ['Leasing', 'Direct'])->nullable()->after('rejection_comment')
                ->comment('Mode de financement');
            $table->string('bank_name', 50)->nullable()->after('financing_mode')
                ->comment('Nom de la banque (si Leasing)');
            $table->string('contract_number', 50)->nullable()->after('bank_name')
                ->comment('N° contrat leasing');
            $table->date('withdrawal_start_date')->nullable()->after('contract_number')
                ->comment('Date debut prelevement');
            $table->date('withdrawal_end_date')->nullable()->after('withdrawal_start_date')
                ->comment('Date fin prelevement');
            $table->date('contract_start_date')->nullable()->after('withdrawal_end_date')
                ->comment('Date debut contrat');
            $table->date('provision_date')->nullable()->after('contract_start_date')
                ->comment('Date mise a disposition');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'user_direction',
                'user_matricule',
                'user_driver_license',
                'financing_mode',
                'bank_name',
                'contract_number',
                'withdrawal_start_date',
                'withdrawal_end_date',
                'contract_start_date',
                'provision_date',
            ]);
        });
    }
};
