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
        Schema::create('estimates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->decimal('sub_total', 15, 2)->default(0);
            $table->decimal('packing_percent', 8, 2)->default(0);
            $table->decimal('packing_charges', 15, 2)->default(0);

            $table->decimal('tax', 15, 2)->default(0);
            $table->unsignedTinyInteger('tax_id')->nullable();
            $table->decimal('tax_amt', 15, 2)->default(0);
            $table->boolean('is_round_off')->default(false);
            $table->decimal('round_off_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimates');
    }
};
