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
        Schema::table('posin', function (Blueprint $table) {
            $table->enum('us_purchase_order_status', ['pending', 'generated', 'reviewed'])
                  ->default('pending')
                  ->comment('美國進貨單狀態：pending=待處理, generated=已產生, reviewed=已審查');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posin', function (Blueprint $table) {
            $table->dropColumn('us_purchase_order_status');
        });
    }
};
