<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            // Basic fields
            $table->id();  // Creates auto-incrementing integer
            $table->string('name')->unique();  // Permission name must be unique
            $table->string('description')->nullable();

            // Audit timestamps
            $table->timestamps();  // Adds created_at and updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('permissions');
    }
};
