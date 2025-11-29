<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // Mandatory
            $table->string('email')->nullable();    // Optional
            $table->string('mobile')->nullable();   // Optional
            $table->decimal('opening_balance', 15, 2)->default(0); // Optional
            $table->enum('cr_dr', ['Cr', 'Dr'])->default('Dr');    // Optional, default Cr
            $table->text('address')->nullable();    // Optional
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
