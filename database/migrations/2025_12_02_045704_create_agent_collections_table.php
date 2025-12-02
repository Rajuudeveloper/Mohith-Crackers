<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('agent_collections', function (Blueprint $table) {
            $table->id();

            // ✅ FK → AGENTS TABLE (NOT USERS)
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();

            // ✅ PAYMENT MODE AS INTEGER ID
            $table->tinyInteger('payment_mode');

            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_collections');
    }
};
