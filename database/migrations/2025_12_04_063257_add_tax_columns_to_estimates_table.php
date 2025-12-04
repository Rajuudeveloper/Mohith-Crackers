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
        Schema::table('estimates', function (Blueprint $table) {

            // Tax percentage like 0, 5, 12, 18
            $table->unsignedTinyInteger('tax_id')
                ->nullable();

            // Final tax amount (manual or auto)
            $table->decimal('tax_amt', 15, 2)
                ->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropColumn(['tax_id', 'tax_amt']);
        });
    }
};
