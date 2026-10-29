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
        Schema::create('sppg_staff', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id')->unique();
            $table->string('name', 100);
            $table->string('position', 80);
            $table->integer('sppg_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sppg_staff');
    }
};
