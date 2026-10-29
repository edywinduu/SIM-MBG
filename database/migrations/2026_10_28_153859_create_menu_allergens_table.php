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
        Schema::create('menu_allergens', function (Blueprint $table) {
            $table->integer('menu_id');
            $table->integer('allergen_id')->index();
            $table->enum('type', ['contains', 'free_from']);

            $table->primary(['menu_id', 'allergen_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_allergens');
    }
};
