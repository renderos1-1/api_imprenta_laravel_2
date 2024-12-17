<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // First drop existing ENUM types if they exist
        DB::statement('DROP TYPE IF EXISTS document_type CASCADE');
        DB::statement('DROP TYPE IF EXISTS person_type CASCADE');
        DB::statement('DROP TYPE IF EXISTS transaction_status CASCADE');

        // Create ENUM types to match API values
        DB::statement("CREATE TYPE document_type AS ENUM ('dui', 'pasaporte', 'nit', 'carnet_residente')");
        DB::statement("CREATE TYPE person_type AS ENUM ('persona_natural', 'persona_juridica')");
        DB::statement("CREATE TYPE transaction_status AS ENUM ('pendiente', 'completado', 'cancelado')");

        Schema::create('transactions', function (Blueprint $table) {
            // Primary key - UUID
            $table->uuid('id')->primary();

            // API related fields
            $table->bigInteger('external_id')->unique();  // Maps to API 'id'
            $table->integer('proceso_id');

            // Document fields - mapped from datos array
            $table->string('document_number')->nullable();  // Maps to 'dui' in datos
            $table->enum('document_type', ['dui', 'pasaporte', 'nit'])->default('dui');
            $table->enum('person_type', ['persona_natural', 'persona_juridica'])->default('persona_natural');

            // Personal information - mapped from datos array
            $table->string('full_name')->nullable();  // Concatenate nombre_titular + apellidos_titular
            $table->string('email')->nullable();      // Maps to email_de_titular
            $table->string('phone')->nullable();      // Maps to n_celular or n_telefono

            // Location information - from departamento_y_municipio
            $table->string('state_code')->nullable();     // Maps to cstateCode
            $table->string('state_name')->nullable();     // Maps to cstateName
            $table->string('city_code')->nullable();      // Maps to ccityCode
            $table->string('city_name')->nullable();      // Maps to ccityName

            // Status and dates
            $table->enum('status', ['pendiente', 'completado', 'cancelado'])->default('pendiente');
            $table->enum('sync_status', ['pending', 'synced', 'failed'])->default('pending');
            $table->jsonb('full_json');              // Store complete API response

            // Timestamps
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();

            // Indexes
            $table->index('external_id');
            $table->index('proceso_id');
            $table->index('document_number');
            $table->index('status');
            $table->index('created_at');
            $table->index(['sync_status', 'last_sync_at']);
            $table->index(['state_code', 'city_code']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');

        // Drop the ENUM types
        DB::statement('DROP TYPE IF EXISTS document_type CASCADE');
        DB::statement('DROP TYPE IF EXISTS person_type CASCADE');
        DB::statement('DROP TYPE IF EXISTS transaction_status CASCADE');
    }
};
