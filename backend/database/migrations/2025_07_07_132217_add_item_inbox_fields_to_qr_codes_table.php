<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            $table->integer('item_inbox_status')->default(0)->comment('商品入庫狀態 (0:未入庫, 1:已入庫)');
            $table->string('item_inbox', 100)->nullable()->comment('商品入庫資訊 (從item資料表獲取)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            $table->dropColumn(['item_inbox_status', 'item_inbox']);
        });
    }
};
