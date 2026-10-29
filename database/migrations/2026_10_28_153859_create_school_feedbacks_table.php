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
        Schema::create('school_feedbacks', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('school_id')->index();
            $table->string('sender_name', 100);
            $table->string('position', 100)->nullable();
            $table->decimal('score', 2, 1);
            $table->text('content');
            $table->string('container_temperature', 40)->nullable();
            $table->decimal('waste_percentage', 4, 1)->nullable()->default(0);
            $table->enum('status', ['very_satisfied', 'needs_evaluation']);
            $table->boolean('is_verified')->default(false);
            $table->text('kitchen_response')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_feedbacks');
    }
};
