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
            $table->string('gateway_payment_id')->nullable();
            $table->foreignId('status')->constrained('payment_statuses', 'id');
            $table->foreignId('gateway')->nullable()->constrained('payment_gateways', 'id');
            $table->decimal('amount', 10, 2);
            $table->string('idempotence_key');
            $table->json('metadata')->nullable();
            $table->json('raw_response')->nullable();
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
