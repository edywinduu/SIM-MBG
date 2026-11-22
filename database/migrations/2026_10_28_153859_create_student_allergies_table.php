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
        Schema::create('student_allergies', function (Blueprint $table) {
            $table->integer('student_id');
            $table->integer('allergen_id')->index();
            $table->string('medical_notes')->nullable();
            $table->enum('verification_status', ['active', 'medical_review'])->default('medical_review');
            $table->integer('verified_by')->nullable()->index();

            $table->primary(['student_id', 'allergen_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_allergies');
    }
};
