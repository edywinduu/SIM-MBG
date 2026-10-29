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
        Schema::create('students', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id')->nullable()->unique();
            $table->string('nisn', 12)->unique();
            $table->string('name', 100);
            $table->integer('school_id')->index();
            $table->string('class_name', 20);
            $table->enum('recipient_status', ['active', 'inactive'])->default('active');
            $table->string('profile_picture')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
