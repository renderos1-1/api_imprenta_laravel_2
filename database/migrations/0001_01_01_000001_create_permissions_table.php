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
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');  // Added this column
            $table->string('description')->nullable();
            $table->string('group')->nullable();  // Added this column

            // Audit timestamps
            $table->timestamps();

            // Index
            $table->index('name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('permissions');
    }
};
