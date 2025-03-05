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
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');

            // Guard name for consistency
            $table->string('guard_name')->default('web');

            // Audit timestamps
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');

            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->onDelete('cascade');

            // Composite primary key
            $table->primary(['role_id', 'permission_id']);

            // Index for performance
            $table->index(['permission_id', 'role_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('role_permissions');
    }
};
