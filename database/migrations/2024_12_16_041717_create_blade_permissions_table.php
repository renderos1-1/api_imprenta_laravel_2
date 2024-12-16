
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('blade_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');  // Name of the blade template
            $table->string('description')->nullable();
            $table->string('route_name');  // Route name associated with the blade
            $table->timestamps();
        });

        // Pivot table for role-blade permissions
        Schema::create('role_blade_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('blade_permission_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('role_blade_permissions');
        Schema::dropIfExists('blade_permissions');
    }
};
