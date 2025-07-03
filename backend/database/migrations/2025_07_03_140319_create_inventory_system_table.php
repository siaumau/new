<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::create('item', function (Blueprint $table) {
            $table->bigInteger('item_id', true);
            $table->string('item_name', 200)->comment('品項名稱');
            $table->boolean('item_cid')->default(true)->comment('產品類型');
            $table->string('item_sn', 200)->comment('型號');
            $table->string('item_spec', 200)->comment('規格');
            $table->text('item_eng')->nullable()->comment('產品英文');
            $table->bigInteger('item_save')->comment('安全庫存量');
            $table->bigInteger('item_save2')->nullable()->comment('銷售最低庫存量');
            $table->decimal('item_price', 10, 0)->comment('成本');
            $table->integer('suggested_retail_price')->default(0)->comment('建議售價');
            $table->text('item_note');
            $table->boolean('item_open');
            $table->bigInteger('item_sort');
            $table->boolean('item_mstock')->comment('希望可撐月數');
            $table->text('item_type')->comment('看管類別');
            $table->string('item_years', 10)->nullable()->comment('有效年數');
            $table->boolean('item_holdmonth')->comment('希望可撐月數');
            $table->text('item_outvyear')->comment('目前出貨效期');
            $table->boolean('item_predict')->comment('是否要預估');
            $table->dateTime('item_insertdate');
            $table->dateTime('item_editdate');
            $table->string('item_barcode', 20)->comment('產品條碼');
            $table->smallInteger('item_inbox')->comment('每箱產品數量');
            $table->bigInteger('ppt_id')->comment('膚質檢測用');
            $table->string('item_vcode', 8)->nullable()->comment('即期品安全驗證碼');
            $table->string('item_size', 20)->nullable()->default('')->comment('尺寸size');
        });

        Schema::create('location_code_settings', function (Blueprint $table) {
            $table->comment('位置代碼設定表');
            $table->integer('id', true);
            $table->enum('code_type', ['building', 'storage_type', 'area', 'sub_area'])->comment('代碼類型');
            $table->string('code_key', 10)->comment('代碼鍵值');
            $table->string('code_value', 20)->comment('代碼對應值');
            $table->string('description', 100)->nullable()->comment('說明');
            $table->integer('max_length')->nullable()->default(2)->comment('最大長度');
            $table->boolean('is_active')->nullable()->default(true)->comment('是否啟用');
            $table->integer('sort_order')->nullable()->default(0)->comment('排序');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();

            $table->unique(['code_type', 'code_key'], 'uk_code_type_key');
        });

        Schema::create('location_product_bindings', function (Blueprint $table) {
            $table->comment('位置商品綁定表');
            $table->integer('id', true);
            $table->integer('location_id')->index('idx_location_id')->comment('位置ID');
            $table->string('location_code', 20)->index('idx_location_code')->comment('位置代碼');
            $table->string('product_code', 50)->index('idx_product_code')->comment('商品條碼');
            $table->string('product_name', 200)->nullable()->comment('商品名稱');
            $table->integer('quantity')->nullable()->default(1)->comment('數量');
            $table->timestamp('binding_date')->useCurrent()->index('idx_binding_date')->comment('綁定時間');
            $table->string('binding_user', 100)->nullable()->default('System User')->comment('綁定操作員');
            $table->text('notes')->nullable()->comment('備註');
            $table->boolean('is_active')->nullable()->default(true)->comment('是否有效');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();

            $table->unique(['location_id', 'product_code'], 'uk_location_product');
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->comment('位置資訊表');
            $table->integer('id', true);
            $table->string('location_code', 20)->unique('uk_location_code')->comment('位置完整代碼');
            $table->string('location_name', 100)->comment('位置名稱');
            $table->string('building_code', 10)->comment('所在地代碼');
            $table->string('floor_number', 10)->comment('樓層');
            $table->string('floor_area_code', 10)->nullable()->comment('樓層區碼');
            $table->string('storage_type_code', 20)->index('idx_storage_type')->comment('存放類別代碼');
            $table->string('sub_area_code', 10)->nullable()->comment('存放小區/層代碼');
            $table->string('position_code', 20)->comment('存放代碼');
            $table->integer('capacity')->nullable()->default(0)->comment('容量');
            $table->integer('current_stock')->nullable()->default(0)->comment('目前庫存');
            $table->text('qr_code_data')->nullable()->comment('QR Code資料');
            $table->text('notes')->nullable()->comment('備註');
            $table->boolean('is_active')->nullable()->default(true)->comment('是否啟用');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();

            $table->index(['building_code', 'floor_number'], 'idx_building_floor');
        });

        Schema::create('operation_logs', function (Blueprint $table) {
            $table->comment('操作記錄表');
            $table->integer('id', true);
            $table->string('user_id', 50)->nullable()->index('idx_user_id')->comment('用戶ID');
            $table->string('user_name', 100)->nullable()->comment('用戶名稱');
            $table->string('operation_type', 50)->index('idx_operation_type')->comment('操作類型');
            $table->string('operation_module', 50)->index('idx_operation_module')->comment('操作模組');
            $table->text('operation_description')->comment('操作描述');
            $table->string('target_id', 50)->nullable()->comment('操作目標ID');
            $table->string('target_type', 50)->nullable()->comment('操作目標類型');
            $table->string('ip_address', 45)->nullable()->comment('IP地址');
            $table->text('user_agent')->nullable()->comment('用戶代理');
            $table->json('request_data')->nullable()->comment('請求資料');
            $table->json('response_data')->nullable()->comment('回應資料');
            $table->integer('execution_time_ms')->nullable()->comment('執行時間(毫秒)');
            $table->enum('status', ['success', 'failure', 'warning'])->nullable()->default('success')->index('idx_status')->comment('操作狀態');
            $table->text('error_message')->nullable()->comment('錯誤訊息');
            $table->char('description')->nullable();
            $table->char('target_object')->nullable();
            $table->char('user_ip')->nullable();
            $table->char('details')->nullable();
            $table->char('session_id')->nullable();
            $table->timestamp('created_at')->useCurrent()->index('idx_created_at');

            $table->index(['target_type', 'target_id'], 'idx_target');
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->comment('權限表');
            $table->integer('id', true);
            $table->string('module', 50)->comment('模組名稱');
            $table->string('name', 50)->unique('name')->comment('權限名稱');
            $table->json('actions')->comment('可執行動作');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });

        Schema::create('posin', function (Blueprint $table) {
            $table->bigInteger('posin_id', true);
            $table->bigInteger('_users_id');
            $table->bigInteger('_users_id2')->nullable();
            $table->text('posin_sn');
            $table->text('posin_user');
            $table->text('posin_user2')->nullable();
            $table->dateTime('posin_dt');
            $table->dateTime('posin_log')->nullable();
            $table->text('posin_note');
        });

        Schema::create('posinitem', function (Blueprint $table) {
            $table->bigInteger('posinitem_id', true);
            $table->bigInteger('posin_id')->index('posin_id');
            $table->bigInteger('itemtype');
            $table->bigInteger('item_id')->index('item_id');
            $table->text('item_name');
            $table->text('item_sn')->index('idx_item_sn');
            $table->text('item_spec');
            $table->string('item_batch', 20)->index('idx_item_batch')->comment('批號');
            $table->bigInteger('item_count');
            $table->decimal('item_price', 10, 0);
            $table->date('item_expireday')->nullable()->index('idx_item_expireday')->comment('到期日');
            $table->string('item_validyear', 10)->nullable()->comment('有效年數');
        });

        Schema::create('qr_codes', function (Blueprint $table) {
            $table->integer('qr_id', true);
            $table->integer('posin_id')->nullable()->index('idx_posin_id');
            $table->integer('posinitem_id')->nullable()->index('idx_posinitem_id');
            $table->string('item_code', 50)->index('idx_item_code');
            $table->string('item_name');
            $table->string('item_batch', 50)->nullable();
            $table->date('expiry_date')->nullable();
            $table->integer('box_number')->default(1)->index('idx_box_number')->comment('箱號（流水號）');
            $table->integer('location_id')->nullable()->index('idx_location_id')->comment('位置ID');
            $table->string('floor_level', 10)->nullable()->index('idx_floor_level')->comment('樓層（僅層架類型位置使用）');
            $table->text('qr_content');
            $table->string('file_name');
            $table->string('zip_file_name')->nullable()->index('idx_zip_file_name')->comment('ZIP檔案名稱');
            $table->timestamp('generated_at')->useCurrent()->index('idx_generated_at');
            $table->string('generated_by', 100)->nullable();
            $table->enum('status', ['generated', 'printed', 'used'])->nullable()->default('generated');
            $table->text('notes')->nullable();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->comment('角色權限關聯表');
            $table->integer('id', true);
            $table->integer('role_id');
            $table->integer('permission_id')->index('permission_id');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['role_id', 'permission_id'], 'unique_role_permission');
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->comment('角色表');
            $table->integer('id', true);
            $table->string('name', 50)->unique('name')->comment('角色名稱');
            $table->text('description')->nullable()->comment('角色描述');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->comment('系統設定表');
            $table->integer('id', true);
            $table->string('setting_key', 50)->unique('unique_setting_key')->comment('設定鍵值');
            $table->text('setting_value')->comment('設定值');
            $table->string('setting_type', 20)->nullable()->default('string')->comment('設定類型 (string, int, json)');
            $table->string('description')->nullable()->comment('設定描述');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->boolean('is_active')->default(true);
        });

        Schema::table('location_product_bindings', function (Blueprint $table) {
            $table->foreign(['location_id'], 'location_product_bindings_ibfk_1')->references(['id'])->on('locations')->onDelete('CASCADE');
        });

        Schema::table('role_permissions', function (Blueprint $table) {
            $table->foreign(['role_id'], 'role_permissions_ibfk_1')->references(['id'])->on('roles')->onDelete('CASCADE');
            $table->foreign(['permission_id'], 'role_permissions_ibfk_2')->references(['id'])->on('permissions')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('role_permissions', function (Blueprint $table) {
            $table->dropForeign('role_permissions_ibfk_1');
            $table->dropForeign('role_permissions_ibfk_2');
        });

        Schema::table('location_product_bindings', function (Blueprint $table) {
            $table->dropForeign('location_product_bindings_ibfk_1');
        });

        Schema::dropIfExists('system_settings');

        Schema::dropIfExists('system_settings');

        Schema::dropIfExists('roles');

        Schema::dropIfExists('role_permissions');

        Schema::dropIfExists('qr_codes');

        Schema::dropIfExists('posinitem');

        Schema::dropIfExists('posin');

        Schema::dropIfExists('posin');

        Schema::dropIfExists('permissions');

        Schema::dropIfExists('permissions');

        Schema::dropIfExists('operation_logs');

        Schema::dropIfExists('locations');

        Schema::dropIfExists('location_product_bindings');

        Schema::dropIfExists('location_code_settings');

        Schema::dropIfExists('item');

        Schema::dropIfExists('item');
    }
};
