<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->date('estimate_date')->nullable()->after('customer_id');
            $table->string('estimate_no')->nullable()->unique()->after('estimate_date');
        });
    }

    public function down(): void
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropColumn(['estimate_date', 'estimate_no']);
        });
    }
};

