<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Identification
            $table->enum('vehicle_type', ['Auto', 'Moto']);
            $table->enum('category', ['Utilitaire', 'Berline', 'Pick-up', 'Camion', 'Moto']);
            $table->string('brand', 50);
            $table->string('model', 100);
            $table->string('version', 50)->nullable();
            $table->date('commissioning_date');
            $table->enum('contract_type', ['Sous contrat', 'Flotte']);
            $table->string('color', 30);

            // Immatriculation & Chassis
            $table->string('registration_number', 20)->nullable()->unique();
            $table->string('temporary_registration', 20)->nullable();
            $table->string('chassis_number', 30)->nullable();
            $table->boolean('chassis_readable');

            // Caracteristiques techniques
            $table->enum('fuel_type', ['Essence', 'Gasoil', 'Hybride', 'Electrique']);
            $table->enum('transmission', ['Automatique', 'Manuelle'])->nullable();
            $table->integer('engine_displacement')->nullable();
            $table->integer('seats_count');
            $table->integer('load_capacity')->nullable();
            $table->integer('mileage');

            // Statut & Equipement
            $table->enum('status', ['En service', 'En reparation', 'Reforme', 'Cede']);
            $table->string('structure_ci', 10)->nullable();
            $table->boolean('has_roll_bars')->nullable();
            $table->text('special_equipment')->nullable();

            // Visite technique
            $table->date('technical_inspection_date');

            // Assurance
            $table->boolean('is_insured');
            $table->string('insurance_company', 100)->nullable();
            $table->string('policy_number', 30)->nullable();
            $table->string('coverage_type', 30)->nullable();
            $table->date('insurance_start_date')->nullable();
            $table->date('insurance_end_date')->nullable();

            // GPS
            $table->decimal('gps_latitude', 10, 8)->nullable();
            $table->decimal('gps_longitude', 11, 8)->nullable();
            $table->decimal('gps_accuracy', 6, 2)->nullable();

            // Metadata
            $table->timestamp('collected_at')->useCurrent();
            $table->foreignUuid('collected_by')->constrained('users');
            $table->enum('form_status', ['draft', 'synchronized', 'validated', 'rejected'])->default('draft');

            // Validation SODECI
            $table->foreignUuid('validated_by')->nullable()->constrained('users');
            $table->timestamp('validated_at')->nullable();
            $table->string('rejection_reason', 50)->nullable();
            $table->text('rejection_comment')->nullable();

            // Versionning
            $table->integer('revision')->default(1);
            $table->timestamps();

            // Index
            $table->index('form_status');
            $table->index('collected_by');
            $table->index('brand');
            $table->index('collected_at');
            $table->index('status');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
