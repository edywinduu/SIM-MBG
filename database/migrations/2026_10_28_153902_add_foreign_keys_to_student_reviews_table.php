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
        Schema::table('student_reviews', function (Blueprint $table) {
            $table->foreign(['student_id'], 'student_reviews_ibfk_1')->references(['id'])->on('students')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['menu_id'], 'student_reviews_ibfk_2')->references(['id'])->on('menus')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_reviews', function (Blueprint $table) {
            $table->dropForeign('student_reviews_ibfk_1');
            $table->dropForeign('student_reviews_ibfk_2');
        });
    }
};
