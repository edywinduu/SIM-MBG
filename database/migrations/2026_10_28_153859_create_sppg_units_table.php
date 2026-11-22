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
        Schema::create('sppg_units', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->string('region', 100);
            $table->text('address')->nullable();
            $table->integer('capacity')->default(0);
            $table->string('contact_person', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->enum('status', ['active', 'maintenance', 'inactive'])->default('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sppg_units');
    }
};
