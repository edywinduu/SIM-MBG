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
        Schema::create('menu_selections', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('student_id');
            $table->integer('schedule_id')->index();
            $table->integer('menu_id')->index();
            $table->enum('consumption_status', ['active', 'medical_review'])->default('active');
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->unique(['student_id', 'schedule_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_selections');
    }
};
