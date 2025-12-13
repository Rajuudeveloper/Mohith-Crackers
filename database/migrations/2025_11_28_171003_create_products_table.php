<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Product Name - mandatory
            $table->string('uom_name')->nullable(); // optional
            $table->decimal('price', 15, 2)->nullable(); // optional
            $table->unsignedInteger('packs_per_case');
            $table->integer('opening_stock')->nullable(); // optional
            $table->text('description')->nullable(); // optional
            $table->string('image')->nullable(); // optional - path to uploaded image
            $table->string('hsn_code', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
