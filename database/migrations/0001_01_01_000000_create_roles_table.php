<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            // Basic fields
            $table->id();  // Creates auto-incrementing integer
            $table->string('name')->unique();  // Role name must be unique
            $table->string('description')->nullable();

            // Audit timestamps
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('roles');
    }
};
