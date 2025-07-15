<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Repositories\PosinRepository;
use App\Models\Posin;
use App\Models\Item;
use App\Models\PosinItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PosinRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PosinRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PosinRepository(new Posin());
    }

    /** @test */
    public function it_can_create_posin()
    {
        // Arrange
        $data = [
            '_users_id' => 1,
            'posin_sn' => 'PO123456',
            'posin_user' => 'Test Supplier',
            'posin_dt' => '2024-01-15',
            'posin_note' => 'Test note'
        ];

        // Act
        $posin = $this->repository->create($data);

        // Assert
        $this->assertInstanceOf(Posin::class, $posin);
        $this->assertEquals('PO123456', $posin->posin_sn);
        $this->assertEquals('Test Supplier', $posin->posin_user);
        $this->assertDatabaseHas('posin', [
            'posin_sn' => 'PO123456',
            'posin_user' => 'Test Supplier'
        ]);
    }

    /** @test */
    public function it_can_find_posin_by_id()
    {
        // Arrange
        $posin = Posin::factory()->create();

        // Act
        $found = $this->repository->find($posin->posin_id);

        // Assert
        $this->assertInstanceOf(Posin::class, $found);
        $this->assertEquals($posin->posin_id, $found->posin_id);
    }

    /** @test */
    public function it_returns_null_when_posin_not_found()
    {
        // Act
        $found = $this->repository->find(999);

        // Assert
        $this->assertNull($found);
    }

    /** @test */
    public function it_can_find_or_fail_posin()
    {
        // Arrange
        $posin = Posin::factory()->create();

        // Act
        $found = $this->repository->findOrFail($posin->posin_id);

        // Assert
        $this->assertInstanceOf(Posin::class, $found);
        $this->assertEquals($posin->posin_id, $found->posin_id);
    }

    /** @test */
    public function it_throws_exception_when_find_or_fail_not_found()
    {
        // Act & Assert
        $this->expectException(ModelNotFoundException::class);
        $this->repository->findOrFail(999);
    }

    /** @test */
    public function it_can_find_by_order_number()
    {
        // Arrange
        $posin = Posin::factory()->create(['posin_sn' => 'PO123456']);

        // Act
        $found = $this->repository->findByOrderNumber('PO123456');

        // Assert
        $this->assertInstanceOf(Posin::class, $found);
        $this->assertEquals('PO123456', $found->posin_sn);
    }

    /** @test */
    public function it_returns_null_when_order_number_not_found()
    {
        // Act
        $found = $this->repository->findByOrderNumber('NONEXISTENT');

        // Assert
        $this->assertNull($found);
    }

    /** @test */
    public function it_can_update_posin()
    {
        // Arrange
        $posin = Posin::factory()->create();
        $updateData = [
            'posin_user' => 'Updated Supplier',
            'posin_note' => 'Updated note'
        ];

        // Act
        $updated = $this->repository->update($posin->posin_id, $updateData);

        // Assert
        $this->assertInstanceOf(Posin::class, $updated);
        $this->assertEquals('Updated Supplier', $updated->posin_user);
        $this->assertEquals('Updated note', $updated->posin_note);
    }

    /** @test */
    public function it_can_delete_posin()
    {
        // Arrange
        $posin = Posin::factory()->create();

        // Act
        $result = $this->repository->delete($posin->posin_id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('posin', [
            'posin_id' => $posin->posin_id
        ]);
    }

    /** @test */
    public function it_can_apply_search_filter()
    {
        // Arrange
        Posin::factory()->create(['posin_sn' => 'PO123456', 'posin_user' => 'ABC Company']);
        Posin::factory()->create(['posin_sn' => 'PO789012', 'posin_user' => 'XYZ Company']);
        
        $query = $this->repository->getQueryBuilder();

        // Act
        $filteredQuery = $this->repository->applySearch($query, 'ABC');
        $results = $filteredQuery->get();

        // Assert
        $this->assertCount(1, $results);
        $this->assertEquals('ABC Company', $results->first()->posin_user);
    }

    /** @test */
    public function it_can_apply_status_filter_for_completed()
    {
        // Arrange
        Posin::factory()->create(['posin_log' => null]); // 進行中
        Posin::factory()->completed()->create(); // 已完成
        
        $query = $this->repository->getQueryBuilder();

        // Act
        $filteredQuery = $this->repository->applyStatusFilter($query, '已完成');
        $results = $filteredQuery->get();

        // Assert
        $this->assertCount(1, $results);
        $this->assertNotNull($results->first()->posin_log);
    }

    /** @test */
    public function it_can_apply_status_filter_for_pending()
    {
        // Arrange
        Posin::factory()->create(['posin_log' => null]); // 進行中
        Posin::factory()->completed()->create(); // 已完成
        
        $query = $this->repository->getQueryBuilder();

        // Act
        $filteredQuery = $this->repository->applyStatusFilter($query, '進行中');
        $results = $filteredQuery->get();

        // Assert
        $this->assertCount(1, $results);
        $this->assertNull($results->first()->posin_log);
    }

    /** @test */
    public function it_can_get_items_with_relations()
    {
        // Arrange
        $posin = Posin::factory()->create();
        $item = Item::factory()->create();
        PosinItem::factory()->forPosin($posin)->forItem($item)->create();

        // Act
        $items = $this->repository->getItemsWithRelations($posin->posin_id);

        // Assert
        $this->assertCount(1, $items);
        $this->assertTrue($items->first()->relationLoaded('item'));
    }

    /** @test */
    public function it_can_delete_items()
    {
        // Arrange
        $posin = Posin::factory()->create();
        $item1 = PosinItem::factory()->forPosin($posin)->create();
        $item2 = PosinItem::factory()->forPosin($posin)->create();

        // Act
        $this->repository->deleteItems($posin->posin_id);

        // Assert
        $this->assertDatabaseMissing('posinitem', [
            'posinitem_id' => $item1->posinitem_id
        ]);
        $this->assertDatabaseMissing('posinitem', [
            'posinitem_id' => $item2->posinitem_id
        ]);
    }

    /** @test */
    public function it_can_check_existing_item()
    {
        // Arrange
        $user = \App\Models\User::factory()->create();
        $posin = Posin::factory()->create([
            '_users_id' => $user->id
        ]);
        $item = Item::factory()->create();
        
        // Create a PosinItem directly
        $posinItem = PosinItem::create([
            'posin_id' => $posin->posin_id,
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
        ]);

        $expireDate = \Carbon\Carbon::parse('2025-12-31');
        $itemData = [
            'item_id' => $item->item_id,
            'item_batch' => 'BATCH001',
            'item_expireday' => $expireDate->format('Y-m-d')
        ];

        // Act
        $exists = $this->repository->hasExistingItem($posin->posin_id, $itemData);

        // Assert
        $this->assertTrue($exists);
    }

    /** @test */
    public function it_returns_false_when_item_does_not_exist()
    {
        // Arrange
        $posin = Posin::factory()->create();
        $itemData = [
            'item_id' => 999,
            'item_batch' => 'NONEXISTENT',
            'item_expireday' => '2025-12-31'
        ];

        // Act
        $exists = $this->repository->hasExistingItem($posin->posin_id, $itemData);

        // Assert
        $this->assertFalse($exists);
    }

    /** @test */
    public function it_can_get_statistics()
    {
        // Arrange
        Posin::factory()->create(['posin_log' => null]); // 進行中
        Posin::factory()->completed()->create(); // 已完成
        Posin::factory()->usGenerated()->create(); // 美國進貨單

        // Act
        $stats = $this->repository->getStatistics();

        // Assert
        $this->assertEquals(3, $stats['total_count']);
        $this->assertEquals(2, $stats['pending_count']); // 包含美國進貨單
        $this->assertEquals(1, $stats['completed_count']);
        $this->assertEquals(1, $stats['us_generated_count']);
    }

    /** @test */
    public function it_can_get_recent_records()
    {
        // Arrange
        Posin::factory()->count(15)->create();

        // Act
        $recent = $this->repository->getRecent(10);

        // Assert
        $this->assertCount(10, $recent);
        // 檢查是否按日期降序排列
        $dates = $recent->pluck('posin_dt')->toArray();
        $sortedDates = collect($dates)->sort()->reverse()->values()->toArray();
        $this->assertEquals($sortedDates, $dates);
    }
}