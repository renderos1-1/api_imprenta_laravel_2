<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // First create the enum type for status
        DB::statement("DROP TYPE IF EXISTS import_job_status CASCADE");
        DB::statement("CREATE TYPE import_job_status AS ENUM ('running', 'completed', 'failed')");

        Schema::create('import_jobs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('finished_at')->nullable();
            // Use the enum type for status
            $table->enum('status', ['running', 'completed', 'failed']);
            $table->text('error_message')->nullable();
            $table->integer('records_processed')->default(0);
            $table->timestamps();

            // Add indexes for common queries
            $table->index('status');
            $table->index('started_at');
            $table->index(['status', 'started_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('import_jobs');

        // Clean up the enum type
        DB::statement("DROP TYPE IF EXISTS import_job_status CASCADE");
    }
};
