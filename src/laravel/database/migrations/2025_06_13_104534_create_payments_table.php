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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('user_id')->constrained('users');
            $table->string('gateway_payment_id');
            $table->foreignId('status')->constrained('payment_statuses', 'id');
            $table->foreignId('gateway')->constrained('payment_gateways', 'id');
            $table->decimal('amount', 10, 2);
            $table->string('idempotence_key');
            $table->json('metadata');
            $table->json('raw_response');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->unique(['gateway', 'gateway_payment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
