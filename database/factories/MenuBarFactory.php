<?php

namespace Database\Factories;

use App\Models\menuBar;
use Illuminate\Database\Eloquent\Factories\Factory;
use PhpParser\Node\Stmt\TryCatch;
use Symfony\Component\Console\Output\ConsoleOutput;

class MenuBarFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = menuBar::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        try {
            $menusIDRange = range(1, 1000);
            $key = array_rand($menusIDRange);
            return [
                'stock1s_id' => $menusIDRange[$key],
                'category' => $this->faker->name(),
                'stocks' => $this->faker->name(),
                'description' => $this->faker->realText(100, 2),
                'price' => rand(1000, 10000),
                'created_at' => now(),
                'updated_at' => now() 
            ];
        } catch (\Throwable $th) {
            //throw $th;
        }

    }
}
