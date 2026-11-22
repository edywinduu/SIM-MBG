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
        Schema::create('menus', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->enum('category', ['regular', 'special', 'backup'])->default('regular');
            $table->integer('calories');
            $table->integer('carbs')->default(0);
            $table->integer('protein')->default(0);
            $table->integer('fat')->default(0);
            $table->decimal('fiber', 4, 1)->default(0);
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
