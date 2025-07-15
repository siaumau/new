<?php

namespace Database\Factories;

use App\Models\PosinItem;
use App\Models\Posin;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class PosinItemFactory extends Factory
{
    protected $model = PosinItem::class;

    public function definition(): array
    {
        $item = Item::factory()->create();
        
        return [
            'posin_id' => Posin::factory(),
            'itemtype' => $this->faker->numberBetween(1, 5),
            'item_id' => $item->item_id,
            'item_name' => $item->item_name,
            'item_sn' => $item->item_sn,
            'item_spec' => $item->item_spec,
            'item_batch' => $this->faker->regexify('[A-Z0-9]{6,10}'),
            'item_count' => $this->faker->numberBetween(1, 100),
            'item_price' => $this->faker->randomFloat(2, 10, 500),
            'item_expireday' => $this->faker->dateTimeBetween('+6 months', '+3 years'),
            'item_validyear' => $this->faker->randomElement(['2', '3', '5']),
        ];
    }

    /**
     * 指定特定商品的項目
     */
    public function forItem(Item $item): static
    {
        return $this->state(fn (array $attributes) => [
            'item_id' => $item->item_id,
            'item_name' => $item->item_name,
            'item_sn' => $item->item_sn,
            'item_spec' => $item->item_spec,
        ]);
    }

    /**
     * 指定特定進貨單的項目
     */
    public function forPosin(Posin $posin): static
    {
        return $this->state(fn (array $attributes) => [
            'posin_id' => $posin->posin_id,
        ]);
    }
}