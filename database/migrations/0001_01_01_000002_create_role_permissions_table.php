<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            // Composite primary key
            $table->foreignId('role_id')->constrained()
                ->onDelete('cascade');  // If role is deleted, delete permissions
            $table->foreignId('permission_id')->constrained()
                ->onDelete('cascade');

            // Make both columns together a primary key
            $table->primary(['role_id', 'permission_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('role_permissions');
    }
};
