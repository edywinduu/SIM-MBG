<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kitchen_audits', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('sppg_id')->index();
            $table->date('audit_date');
            $table->enum('audit_type', ['hygiene', 'nutrition']);
            $table->string('result', 100);
            $table->string('certificate_file')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_audits');
    }
};
