<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PosinService;
use App\Repositories\PosinRepository;
use App\Repositories\ItemRepository;
use App\Models\Posin;
use App\Models\Item;
use App\Exceptions\BusinessLogicException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery;

class PosinServiceTest extends TestCase
{
    use RefreshDatabase;

    private PosinService $posinService;
    private PosinRepository $posinRepository;
    private ItemRepository $itemRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->posinRepository = new PosinRepository(new Posin());
        $this->itemRepository = new ItemRepository(new Item());
        
        $this->posinService = new PosinService(
            $this->posinRepository,
            $this->itemRepository
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
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
        $result = $this->posinService->createPosin($posinData);

        // Assert
        $this->assertInstanceOf(Posin::class, $result);
        $this->assertEquals('PO123456', $result->posin_sn);
        $this->assertCount(1, $result->posinItems);
    }

    /** @test */
    public function it_throws_exception_when_creating_posin_with_invalid_item()
    {
        // Arrange
        $user = \App\Models\User::factory()->create();
        
        $posinData = [
            '_users_id' => $user->id,
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

        // Act & Assert
        $this->expectException(BusinessLogicException::class);
        $this->expectExceptionMessage('商品ID 999 不存在');

        $this->posinService->createPosin($posinData);
    }

    /** @test */
    public function it_can_update_posin_successfully()
    {
        // Arrange
        $user = \App\Models\User::factory()->create();
        $item = Item::factory()->create();
        
        // Create initial posin
        $posin = $this->posinService->createPosin([
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
        ]);

        $updateData = [
            '_users_id' => $user->id,
            'posin_sn' => 'PO123456-UPDATED',
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
                    'item_price' => 150.00,
                    'item_expireday' => '2025-12-31',
                    'item_validyear' => '2'
                ]
            ]
        ];

        // Act
        $result = $this->posinService->updatePosin($posin->posin_id, $updateData);

        // Assert
        $this->assertInstanceOf(Posin::class, $result);
        $this->assertEquals('PO123456-UPDATED', $result->posin_sn);
        $this->assertEquals('Updated Supplier', $result->posin_user);
    }

    /** @test */
    public function it_throws_exception_when_updating_non_existent_posin()
    {
        // Arrange
        $posinId = 999;
        $user = \App\Models\User::factory()->create();
        $updateData = [
            '_users_id' => $user->id,
            'posin_sn' => 'PO123456',
            'posin_user' => 'Test Supplier',
            'posin_dt' => '2024-01-15',
            'posin_note' => 'Test note',
            'posin_items' => []
        ];

        // Act & Assert
        $this->expectException(BusinessLogicException::class);

        $this->posinService->updatePosin($posinId, $updateData);
    }

    /** @test */
    public function it_can_delete_posin_when_no_qr_codes_exist()
    {
        // Arrange
        $user = \App\Models\User::factory()->create();
        $item = Item::factory()->create();
        
        // Create a posin
        $posin = $this->posinService->createPosin([
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
        ]);

        // Act
        $result = $this->posinService->deletePosin($posin->posin_id);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_throws_exception_when_deleting_posin_with_qr_codes()
    {
        // Arrange
        $user = \App\Models\User::factory()->create();
        $item = Item::factory()->create();
        
        // Create a posin
        $posin = $this->posinService->createPosin([
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
        ]);
        
        // Create a QR code for this posin to simulate the condition
        \DB::table('qr_codes')->insert([
            'posin_id' => $posin->posin_id,
            'item_code' => 'TEST001',
            'item_name' => 'Test Item',
            'qr_content' => 'test-content',
            'file_name' => 'test.png',
            'generated_at' => now()
        ]);

        // Act & Assert
        $this->expectException(BusinessLogicException::class);
        $this->expectExceptionMessage('該進貨單已生成QR碼，無法刪除');

        $this->posinService->deletePosin($posin->posin_id);
    }

    /** @test */
    public function it_can_convert_to_us_purchase_order()
    {
        // Arrange
        $user = \App\Models\User::factory()->create();
        $item = Item::factory()->create();
        
        // Create a posin with pending status
        $posin = $this->posinService->createPosin([
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
        ]);

        // Act
        $result = $this->posinService->convertToUsPurchaseOrder($posin->posin_id);

        // Assert
        $this->assertEquals('美國進貨單轉換成功', $result['message']);
        $this->assertEquals('generated', $result['status']);
    }

    /** @test */
    public function it_throws_exception_when_converting_already_converted_posin()
    {
        // Arrange
        $user = \App\Models\User::factory()->create();
        $item = Item::factory()->create();
        
        // Create a posin
        $posin = $this->posinService->createPosin([
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
        ]);
        
        // Convert it first
        $this->posinService->convertToUsPurchaseOrder($posin->posin_id);

        // Act & Assert
        $this->expectException(BusinessLogicException::class);
        $this->expectExceptionMessage('該進貨單已經轉換為美國進貨單');

        $this->posinService->convertToUsPurchaseOrder($posin->posin_id);
    }
}