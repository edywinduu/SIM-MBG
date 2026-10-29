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
        Schema::create('schools', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('npsn', 12)->unique();
            $table->string('name', 120);
            $table->enum('education_level', ['SD', 'SMP', 'SMA']);
            $table->string('district', 80);
            $table->string('city', 80);
            $table->text('address')->nullable();
            $table->string('pic_name', 100)->nullable();
            $table->integer('sppg_id')->index();
            $table->enum('status', ['active', 'pending', 'inactive'])->default('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
