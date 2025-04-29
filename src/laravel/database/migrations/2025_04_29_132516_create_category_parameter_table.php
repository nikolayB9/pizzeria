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
        Schema::create('category_parameter', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('parameter_id')->constrained('parameters');
            $table->unique(['category_id', 'parameter_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_parameter');
    }
};
