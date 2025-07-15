<?php

namespace Tests\Unit\Requests;

use Tests\TestCase;
use App\Http\Requests\PosinCreateRequest;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

class PosinCreateRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_passes_validation_with_valid_data()
    {
        // Arrange
        $user = \App\Models\User::factory()->create();
        $item = Item::factory()->create();
        $data = [
            '_users_id' => $user->id,
            'posin_sn' => 'PO123456',
            'posin_user' => 'Test Supplier',
            'posin_dt' => '2024-01-15',
            'posin_note' => 'Test note',
            'posin_items' => [
                [
                    'itemtype' => 1,
                    'item_id' => $item->item_id,
                    'item_name' => $item->item_name,
                    'item_sn' => $item->item_sn,
                    'item_spec' => $item->item_spec,
                    'item_batch' => 'BATCH001',
                    'item_count' => 10,
                    'item_price' => 100.00,
                    'item_expireday' => '2025-12-31',
                    'item_validyear' => '2'
                ]
            ]
        ];

        $request = new PosinCreateRequest();

        // Act
        $validator = Validator::make($data, $request->rules(), $request->messages());

        // Assert
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_fails_validation_with_missing_required_fields()
    {
        // Arrange
        $data = []; // 空資料

        $request = new PosinCreateRequest();

        // Act
        $validator = Validator::make($data, $request->rules(), $request->messages());

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('_users_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('posin_sn', $validator->errors()->toArray());
        $this->assertArrayHasKey('posin_user', $validator->errors()->toArray());
        $this->assertArrayHasKey('posin_dt', $validator->errors()->toArray());
        $this->assertArrayHasKey('posin_note', $validator->errors()->toArray());
        $this->assertArrayHasKey('posin_items', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_with_duplicate_order_number()
    {
        // Arrange
        \App\Models\Posin::factory()->create(['posin_sn' => 'PO123456']);
        
        $item = Item::factory()->create();
        $data = [
            '_users_id' => 1,
            'posin_sn' => 'PO123456', // 重複的訂單號
            'posin_user' => 'Test Supplier',
            'posin_dt' => '2024-01-15',
            'posin_note' => 'Test note',
            'posin_items' => [
                [
                    'itemtype' => 1,
                    'item_id' => $item->item_id,
                    'item_name' => $item->item_name,
                    'item_sn' => $item->item_sn,
                    'item_spec' => $item->item_spec,
                    'item_batch' => 'BATCH001',
                    'item_count' => 10,
                    'item_price' => 100.00
                ]
            ]
        ];

        $request = new PosinCreateRequest();

        // Act
        $validator = Validator::make($data, $request->rules(), $request->messages());

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('posin_sn', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_with_non_existent_item()
    {
        // Arrange
        $data = [
            '_users_id' => 1,
            'posin_sn' => 'PO123456',
            'posin_user' => 'Test Supplier',
            'posin_dt' => '2024-01-15',
            'posin_note' => 'Test note',
            'posin_items' => [
                [
                    'itemtype' => 1,
                    'item_id' => 999, // 不存在的商品ID
                    'item_name' => 'Test Item',
                    'item_sn' => 'TEST001',
                    'item_spec' => '100ml',
                    'item_batch' => 'BATCH001',
                    'item_count' => 10,
                    'item_price' => 100.00
                ]
            ]
        ];

        $request = new PosinCreateRequest();

        // Act
        $validator = Validator::make($data, $request->rules(), $request->messages());

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('posin_items.0.item_id', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_with_invalid_batch_format()
    {
        // Arrange
        $item = Item::factory()->create();
        $data = [
            '_users_id' => 1,
            'posin_sn' => 'PO123456',
            'posin_user' => 'Test Supplier',
            'posin_dt' => '2024-01-15',
            'posin_note' => 'Test note',
            'posin_items' => [
                [
                    'itemtype' => 1,
                    'item_id' => $item->item_id,
                    'item_name' => $item->item_name,
                    'item_sn' => $item->item_sn,
                    'item_spec' => $item->item_spec,
                    'item_batch' => 'BATCH@001', // 包含非法字符
                    'item_count' => 10,
                    'item_price' => 100.00
                ]
            ]
        ];

        $request = new PosinCreateRequest();

        // Act
        $validator = Validator::make($data, $request->rules(), $request->messages());

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('posin_items.0.item_batch', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_with_invalid_count()
    {
        // Arrange
        $item = Item::factory()->create();
        $data = [
            '_users_id' => 1,
            'posin_sn' => 'PO123456',
            'posin_user' => 'Test Supplier',
            'posin_dt' => '2024-01-15',
            'posin_note' => 'Test note',
            'posin_items' => [
                [
                    'itemtype' => 1,
                    'item_id' => $item->item_id,
                    'item_name' => $item->item_name,
                    'item_sn' => $item->item_sn,
                    'item_spec' => $item->item_spec,
                    'item_batch' => 'BATCH001',
                    'item_count' => 0, // 無效的數量
                    'item_price' => 100.00
                ]
            ]
        ];

        $request = new PosinCreateRequest();

        // Act
        $validator = Validator::make($data, $request->rules(), $request->messages());

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('posin_items.0.item_count', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_with_invalid_price()
    {
        // Arrange
        $item = Item::factory()->create();
        $data = [
            '_users_id' => 1,
            'posin_sn' => 'PO123456',
            'posin_user' => 'Test Supplier',
            'posin_dt' => '2024-01-15',
            'posin_note' => 'Test note',
            'posin_items' => [
                [
                    'itemtype' => 1,
                    'item_id' => $item->item_id,
                    'item_name' => $item->item_name,
                    'item_sn' => $item->item_sn,
                    'item_spec' => $item->item_spec,
                    'item_batch' => 'BATCH001',
                    'item_count' => 10,
                    'item_price' => -10.00 // 負數價格
                ]
            ]
        ];

        $request = new PosinCreateRequest();

        // Act
        $validator = Validator::make($data, $request->rules(), $request->messages());

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('posin_items.0.item_price', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_with_past_expiry_date()
    {
        // Arrange
        $item = Item::factory()->create();
        $data = [
            '_users_id' => 1,
            'posin_sn' => 'PO123456',
            'posin_user' => 'Test Supplier',
            'posin_dt' => '2024-01-15',
            'posin_note' => 'Test note',
            'posin_items' => [
                [
                    'itemtype' => 1,
                    'item_id' => $item->item_id,
                    'item_name' => $item->item_name,
                    'item_sn' => $item->item_sn,
                    'item_spec' => $item->item_spec,
                    'item_batch' => 'BATCH001',
                    'item_count' => 10,
                    'item_price' => 100.00,
                    'item_expireday' => '2020-01-15' // 過去的日期
                ]
            ]
        ];

        $request = new PosinCreateRequest();

        // Act
        $validator = Validator::make($data, $request->rules(), $request->messages());

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('posin_items.0.item_expireday', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_with_empty_items_array()
    {
        // Arrange
        $data = [
            '_users_id' => 1,
            'posin_sn' => 'PO123456',
            'posin_user' => 'Test Supplier',
            'posin_dt' => '2024-01-15',
            'posin_note' => 'Test note',
            'posin_items' => [] // 空陣列
        ];

        $request = new PosinCreateRequest();

        // Act
        $validator = Validator::make($data, $request->rules(), $request->messages());

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('posin_items', $validator->errors()->toArray());
    }

    /** @test */
    public function it_provides_custom_error_messages()
    {
        // Arrange
        $data = [
            'posin_sn' => '',
            'posin_items' => []
        ];

        $request = new PosinCreateRequest();

        // Act
        $validator = Validator::make($data, $request->rules(), $request->messages());

        // Assert
        $this->assertFalse($validator->passes());
        $errors = $validator->errors()->toArray();
        
        $this->assertStringContainsString('進貨單號是必填項', $errors['posin_sn'][0]);
        $this->assertStringContainsString('進貨項目是必填項', $errors['posin_items'][0]);
    }
}