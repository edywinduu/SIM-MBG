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
        Schema::table('distributions', function (Blueprint $table) {
            $table->foreign(['sppg_id'], 'distributions_ibfk_1')->references(['id'])->on('sppg_units')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['school_id'], 'distributions_ibfk_2')->references(['id'])->on('schools')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distributions', function (Blueprint $table) {
            $table->dropForeign('distributions_ibfk_1');
            $table->dropForeign('distributions_ibfk_2');
        });
    }
};
