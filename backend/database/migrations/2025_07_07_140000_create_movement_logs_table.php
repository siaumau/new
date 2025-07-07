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
        Schema::create('movement_logs', function (Blueprint $table) {
            $table->comment('移動記錄表');
            $table->id();
            $table->integer('qr_code_id')->comment('QR Code ID');
            $table->string('item_code', 50)->comment('商品代碼');
            $table->string('item_name', 100)->comment('商品名稱');
            $table->string('box_number', 10)->comment('箱號');
            $table->integer('from_location_id')->nullable()->comment('原位置ID');
            $table->string('from_location_code', 20)->nullable()->comment('原位置代碼');
            $table->integer('to_location_id')->nullable()->comment('新位置ID');
            $table->string('to_location_code', 20)->nullable()->comment('新位置代碼');
            $table->string('movement_type', 20)->comment('移動類型 (assign:分配, move:移動, return:歸位)');
            $table->string('reason', 200)->nullable()->comment('移動原因');
            $table->string('operator', 50)->comment('操作者');
            $table->timestamp('moved_at')->useCurrent()->comment('移動時間');
            $table->text('notes')->nullable()->comment('備註');

            $table->index(['qr_code_id'], 'idx_qr_code_id');
            $table->index(['from_location_id'], 'idx_from_location');
            $table->index(['to_location_id'], 'idx_to_location');
            $table->index(['moved_at'], 'idx_moved_at');
            $table->index(['movement_type'], 'idx_movement_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movement_logs');
    }
};
