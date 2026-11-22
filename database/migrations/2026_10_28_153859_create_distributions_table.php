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
        Schema::create('distributions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('sppg_id')->index();
            $table->integer('school_id')->index();
            $table->date('date');
            $table->integer('portion_count');
            $table->time('eta_start')->nullable();
            $table->time('eta_end')->nullable();
            $table->enum('status', ['pending', 'in_transit', 'delivered'])->default('pending');
            $table->string('fleet', 30)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributions');
    }
};
