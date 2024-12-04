<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_logs', function (Blueprint $table) {
            $table->id();
            $table->string('dui', 10);  // Format: 00000000-0
            $table->string('action');    // 'login' or 'logout'
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Add foreign key constraint
            $table->foreign('dui')
                ->references('dui')
                ->on('users')
                ->onDelete('cascade');

            // Add index for faster queries
            $table->index(['dui', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_logs');
    }
};
