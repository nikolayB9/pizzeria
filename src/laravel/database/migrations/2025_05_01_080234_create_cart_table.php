<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cart', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('session_id')->nullable();
            $table->foreignId('product_variant_id')->constrained('product_variants');
            $table->decimal('price');
            $table->unsignedInteger('qty');
            $table->foreignId('category_id')->constrained('categories');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'product_variant_id'], 'user_variant_unique');
            $table->unique(['session_id', 'product_variant_id'], 'session_variant_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
