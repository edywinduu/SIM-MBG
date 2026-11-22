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
        Schema::table('menu_selections', function (Blueprint $table) {
            $table->foreign(['student_id'], 'menu_selections_ibfk_1')->references(['id'])->on('students')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['schedule_id'], 'menu_selections_ibfk_2')->references(['id'])->on('menu_schedules')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['menu_id'], 'menu_selections_ibfk_3')->references(['id'])->on('menus')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_selections', function (Blueprint $table) {
            $table->dropForeign('menu_selections_ibfk_1');
            $table->dropForeign('menu_selections_ibfk_2');
            $table->dropForeign('menu_selections_ibfk_3');
        });
    }
};
