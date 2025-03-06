<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('transaction_id');
            $table->bigInteger('external_transaction_id');
            $table->string('document_type'); // adjuntar_documento, upload_nrc, etc.
            $table->string('original_filename');
            $table->string('original_url')->nullable();
            $table->string('storage_path')->nullable();
            $table->enum('status', ['pending', 'downloaded', 'error'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->foreign('transaction_id')
                ->references('id')
                ->on('transactions')
                ->onDelete('cascade');

            $table->index('external_transaction_id');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('documents');
    }
};
