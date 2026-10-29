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
        Schema::table('school_feedbacks', function (Blueprint $table) {
            $table->foreign(['school_id'], 'school_feedbacks_ibfk_1')->references(['id'])->on('schools')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_feedbacks', function (Blueprint $table) {
            $table->dropForeign('school_feedbacks_ibfk_1');
        });
    }
};
