<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'item_name' => $this->faker->words(3, true) . ' 產品',
            'item_cid' => true,
            'item_sn' => 'ITEM' . $this->faker->unique()->numberBetween(10000, 99999),
            'item_spec' => $this->faker->randomElement(['100ml', '250ml', '500ml', '1L']),
            'item_eng' => $this->faker->words(3, true) . ' Product',
            'item_save' => $this->faker->numberBetween(10, 100),
            'item_save2' => $this->faker->numberBetween(5, 50),
            'item_price' => $this->faker->randomFloat(2, 10, 1000),
            'suggested_retail_price' => $this->faker->randomFloat(2, 20, 2000),
            'item_note' => $this->faker->sentence(),
            'item_open' => true,
            'item_sort' => $this->faker->numberBetween(1, 100),
            'item_mstock' => $this->faker->boolean(),
            'item_type' => $this->faker->randomElement(['護膚', '彩妝', '香水', '身體護理']),
            'item_years' => $this->faker->randomElement(['2', '3', '5']),
            'item_holdmonth' => $this->faker->numberBetween(6, 24),
            'item_outvyear' => $this->faker->dateTimeBetween('+1 year', '+3 years')->format('Y-m'),
            'item_predict' => $this->faker->boolean(),
            'item_insertdate' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'item_editdate' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'item_barcode' => $this->faker->unique()->ean13(),
            'item_inbox' => $this->faker->randomElement([12, 24, 48, 96]),
            'ppt_id' => $this->faker->numberBetween(1, 10),
            'item_vcode' => $this->faker->optional()->regexify('[A-Z0-9]{8}'),
            'item_size' => $this->faker->optional()->randomElement(['S', 'M', 'L', 'XL']),
        ];
    }
}