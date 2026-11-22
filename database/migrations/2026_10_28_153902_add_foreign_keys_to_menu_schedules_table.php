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
        Schema::table('menu_schedules', function (Blueprint $table) {
            $table->foreign(['menu_id'], 'menu_schedules_ibfk_1')->references(['id'])->on('menus')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['sppg_id'], 'menu_schedules_ibfk_2')->references(['id'])->on('sppg_units')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_schedules', function (Blueprint $table) {
            $table->dropForeign('menu_schedules_ibfk_1');
            $table->dropForeign('menu_schedules_ibfk_2');
        });
    }
};
