<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        // First drop existing ENUM types if they exist
        DB::statement('DROP TYPE IF EXISTS document_type CASCADE');
        DB::statement('DROP TYPE IF EXISTS person_type CASCADE');

        // Then create the ENUM types in PostgreSQL
        DB::statement("CREATE TYPE document_type AS ENUM ('dui', 'passport', 'nit')");
        DB::statement("CREATE TYPE person_type AS ENUM ('persona_natural', 'persona_juridica')");

        Schema::create('transactions', function (Blueprint $table) {
            // Primary key - UUID
            $table->uuid('id')->primary();

            // Internal reference number
            $table->bigInteger('internal_id')->autoIncrement();

            // Enum fields - using PostgreSQL native enums
            $table->enum('document_type', ['dui', 'passport', 'nit'])
                ->default('dui');
            $table->enum('person_type', ['persona_natural', 'persona_juridica'])
                ->default('persona_natural');

            // Basic fields
            $table->string('document_number');
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');

            // JSON data
            $table->jsonb('full_json');

            // Status and dates
            $table->string('status');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();

            // Indexes
            $table->index('internal_id');
            $table->index('document_number');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');

        // Drop the ENUM types
        DB::statement('DROP TYPE IF EXISTS document_type CASCADE');
        DB::statement('DROP TYPE IF EXISTS person_type CASCADE');
    }
};
