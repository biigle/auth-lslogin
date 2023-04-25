<?php

namespace Biigle\Modules\AuthLSLogin\Database\Factories;

use Biigle\Modules\AuthLSLogin\LsloginId;
use Biigle\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LsloginIdFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LsloginId::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => $this->faker->uuid(),
            'user_id' => User::factory(),
        ];
    }
}
