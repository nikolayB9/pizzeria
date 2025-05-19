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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->boolean('is_default')->default(false);
            $table->foreignId('city_id')->constrained('cities', 'id');
            $table->foreignId('street_id')->constrained('streets', 'id');
            $table->string('house');
            $table->string('entrance')->nullable();
            $table->string('floor')->nullable();
            $table->string('flat')->nullable();
            $table->string('intercom_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
