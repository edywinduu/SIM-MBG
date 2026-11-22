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
        Schema::create('menu_schedules', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('menu_id')->index();
            $table->integer('sppg_id')->index();
            $table->date('date');
            $table->string('cycle', 40)->nullable();
            $table->time('cutoff_time')->default('20:00:00');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_schedules');
    }
};
