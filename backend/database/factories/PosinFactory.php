<?php

namespace Database\Factories;

use App\Models\Posin;
use Illuminate\Database\Eloquent\Factories\Factory;

class PosinFactory extends Factory
{
    protected $model = Posin::class;

    public function definition(): array
    {
        return [
            '_users_id' => 1,
            'posin_sn' => 'PO' . $this->faker->unique()->numberBetween(100000, 999999),
            'posin_user' => $this->faker->company(),
            'posin_dt' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'posin_note' => $this->faker->sentence(),
            'posin_log' => null,
            'us_purchase_order_status' => 'pending',
        ];
    }

    /**
     * 已完成狀態的進貨單
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'posin_log' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * 已轉換為美國進貨單
     */
    public function usGenerated(): static
    {
        return $this->state(fn (array $attributes) => [
            'us_purchase_order_status' => 'generated',
        ]);
    }
}