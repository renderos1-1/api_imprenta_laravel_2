<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            // Primary key
            $table->uuid('id')->primary();

            // Authentication fields
            $table->string('dui', 10)->unique();  // Format: 00000000-0
            $table->string('password');
            $table->rememberToken();

            // Personal information
            $table->string('full_name');

            // Role and status
            $table->foreignId('role_id')->constrained();
            $table->boolean('is_active')->default(true);

            // Audit fields
            $table->timestamp('last_login')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->uuid('last_modified_by')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Indexes
            $table->index('dui');
            $table->index('is_active');
        });

        // Add a check constraint for DUI format
        DB::statement("ALTER TABLE users ADD CONSTRAINT check_dui_format CHECK (dui ~ '^[0-9]{8}-[0-9]$')");
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
