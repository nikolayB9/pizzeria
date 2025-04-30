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
        Schema::create('parameter_product_variant', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->constrained('product_variants');
            $table->foreignId('parameter_id')->constrained('parameters');
            $table->string('value')->nullable();
            $table->boolean('is_shared')->default(false);

            $table->primary(['product_variant_id', 'parameter_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parameter_product_variant');
    }
};
