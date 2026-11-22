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
        Schema::table('student_allergies', function (Blueprint $table) {
            $table->foreign(['student_id'], 'student_allergies_ibfk_1')->references(['id'])->on('students')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['allergen_id'], 'student_allergies_ibfk_2')->references(['id'])->on('allergens')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['verified_by'], 'student_allergies_ibfk_3')->references(['id'])->on('sppg_staff')->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_allergies', function (Blueprint $table) {
            $table->dropForeign('student_allergies_ibfk_1');
            $table->dropForeign('student_allergies_ibfk_2');
            $table->dropForeign('student_allergies_ibfk_3');
        });
    }
};
