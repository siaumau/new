<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Posin;
use App\Models\Item;
use App\Models\PosinItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class PosinControllerRefactoredTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 確保服務提供者已註冊
        $this->app->register(\App\Providers\RepositoryServiceProvider::class);
    }

    /** @test */
    public function it_can_get_posin_list()
    {
        // Arrange
        Posin::factory()->count(3)->create();

        // Act
        $response = $this->getJson('/api/v1/posin');

        // Assert
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        '*' => [
                            'id',
                            'order_number',
                            'supplier',
                            'purchase_date',
                            'status',
                            'items_count'
                        ]
                    ],
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => '進貨單列表獲取成功'
                ]);

        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function it_can_search_posin_list()
    {
        // Arrange
        Posin::factory()->create(['posin_sn' => 'PO123456', 'posin_user' => 'ABC Company']);
        Posin::factory()->create(['posin_sn' => 'PO789012', 'posin_user' => 'XYZ Company']);

        // Act
        $response = $this->getJson('/api/v1/posin?search=ABC');

        // Assert
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('ABC Company', $data[0]['supplier']);
    }

    /** @test */
    public function it_can_filter_posin_by_status()
    {
        // Arrange
        Posin::factory()->create(['posin_log' => null]); // 進行中
        Posin::factory()->completed()->create(); // 已完成

        // Act
        $response = $this->getJson('/api/v1/posin?status=已完成');

        // Assert
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('已完成', $data[0]['status']);
    }

    /** @test */
    public function it_can_create_posin_with_valid_data()
    {
        // Arrange
        $user = \App\Models\User::factory()->create();
        $item = Item::factory()->create();
        $posinData = [
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

        // Act
        $response = $this->postJson('/api/v1/posin', $posinData);

        // Assert
        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'order_number',
                        'supplier',
                        'items'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => '進貨單創建成功'
                ]);

        $this->assertDatabaseHas('posin', [
            'posin_sn' => 'PO123456',
            'posin_user' => 'Test Supplier'
        ]);

        $this->assertDatabaseHas('posinitem', [
            'item_id' => $item->item_id,
            'item_batch' => 'BATCH001',
            'item_count' => 10
        ]);
    }

    /** @test */
    public function it_fails_to_create_posin_with_invalid_data()
    {
        // Arrange
        $invalidData = [
            'posin_sn' => '', // 空的訂單號
            'posin_user' => 'Test Supplier',
            'posin_items' => [] // 空的項目陣列
        ];

        // Act
        $response = $this->postJson('/api/v1/posin', $invalidData);

        // Assert
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['posin_sn', 'posin_items', '_users_id', 'posin_dt', 'posin_note']);
    }

    /** @test */
    public function it_fails_to_create_posin_with_non_existent_item()
    {
        // Arrange
        $posinData = [
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

        // Act
        $response = $this->postJson('/api/v1/posin', $posinData);

        // Assert
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['posin_items.0.item_id']);
    }

    /** @test */
    public function it_can_show_posin_details()
    {
        // Arrange
        $posin = Posin::factory()->create();
        $item = Item::factory()->create();
        PosinItem::factory()->forPosin($posin)->forItem($item)->create();

        // Act
        $response = $this->getJson("/api/v1/posin/{$posin->posin_id}");

        // Assert
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'order_number',
                        'supplier',
                        'items'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $posin->posin_id,
                        'order_number' => $posin->posin_sn
                    ]
                ]);
    }

    /** @test */
    public function it_returns_404_when_posin_not_found()
    {
        // Act
        $response = $this->getJson('/api/v1/posin/999');

        // Assert
        $response->assertStatus(400); // BusinessLogicException returns 400
    }

    /** @test */
    public function it_can_update_posin()
    {
        // Arrange
        $posin = Posin::factory()->create();
        $item = Item::factory()->create();
        
        $updateData = [
            '_users_id' => 1,
            'posin_sn' => $posin->posin_sn, // 保持原訂單號
            'posin_user' => 'Updated Supplier',
            'posin_dt' => '2024-01-16',
            'posin_note' => 'Updated note',
            'posin_items' => [
                [
                    'itemtype' => 1,
                    'item_id' => $item->item_id,
                    'item_name' => $item->item_name,
                    'item_sn' => $item->item_sn,
                    'item_spec' => $item->item_spec,
                    'item_batch' => 'BATCH002',
                    'item_count' => 20,
                    'item_price' => 150.00
                ]
            ]
        ];

        // Act
        $response = $this->putJson("/api/v1/posin/{$posin->posin_id}", $updateData);

        // Assert
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => '進貨單更新成功'
                ]);

        $this->assertDatabaseHas('posin', [
            'posin_id' => $posin->posin_id,
            'posin_user' => 'Updated Supplier'
        ]);
    }

    /** @test */
    public function it_can_delete_posin()
    {
        // Arrange
        $posin = Posin::factory()->create();

        // Act
        $response = $this->deleteJson("/api/v1/posin/{$posin->posin_id}");

        // Assert
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => '進貨單刪除成功'
                ]);

        $this->assertDatabaseMissing('posin', [
            'posin_id' => $posin->posin_id
        ]);
    }

    /** @test */
    public function it_can_get_posin_items()
    {
        // Arrange
        $posin = Posin::factory()->create();
        $item = Item::factory()->create();
        PosinItem::factory()->count(3)->forPosin($posin)->forItem($item)->create();

        // Act
        $response = $this->getJson("/api/v1/posin/{$posin->posin_id}/items");

        // Assert
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'posinitem_id',
                            'item_name',
                            'item_sn',
                            'item_batch',
                            'item_count'
                        ]
                    ],
                    'meta' => [
                        'items_count'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'meta' => [
                        'items_count' => 3
                    ]
                ]);
    }

    /** @test */
    public function it_can_convert_to_us_purchase_order()
    {
        // Arrange
        $posin = Posin::factory()->create(['us_purchase_order_status' => 'pending']);

        // Act
        $response = $this->patchJson("/api/v1/posin/{$posin->posin_id}/generate-us-purchase-order");

        // Assert
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => '美國進貨單轉換成功',
                    'data' => [
                        'status' => 'generated'
                    ]
                ]);

        $this->assertDatabaseHas('posin', [
            'posin_id' => $posin->posin_id,
            'us_purchase_order_status' => 'generated'
        ]);
    }

    /** @test */
    public function it_fails_to_convert_already_converted_posin()
    {
        // Arrange
        $posin = Posin::factory()->usGenerated()->create();

        // Act
        $response = $this->patchJson("/api/v1/posin/{$posin->posin_id}/generate-us-purchase-order");

        // Assert
        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'error' => [
                        'type' => 'BusinessLogicException'
                    ]
                ]);
    }

    /** @test */
    public function it_can_batch_import_posin()
    {
        // Arrange
        $item = Item::factory()->create();
        $batchData = [
            'purchase_orders' => [
                [
                    'order_number' => 'PO123456',
                    'user_name' => 'Test Supplier',
                    'order_date' => '2024-01-15',
                    'notes' => 'Test import',
                    'item_id' => $item->item_id,
                    'item_batch' => 'BATCH001',
                    'item_count' => 10,
                    'item_price' => 100.00,
                    'itemtype' => 1
                ]
            ]
        ];

        // Act
        $response = $this->postJson('/api/v1/posin/batch', $batchData);

        // Assert
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'created_count',
                        'error_count',
                        'errors'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => '批量匯入完成'
                ]);

        $this->assertDatabaseHas('posin', [
            'posin_sn' => 'PO123456',
            'posin_user' => 'Test Supplier'
        ]);
    }
}