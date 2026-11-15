<?php

namespace Database\Factories;

use App\Models\Node;
use Illuminate\Database\Eloquent\Factories\Factory;

class NodeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Node::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $code = $this->faker->numberBetween($min = 1, $max = 100);
        return [
            'name' => 'NODO '.$code,
            'code' => $code,
            'zone_id' => $this->faker->numberBetween(1, 5)
        ];
    }
}
