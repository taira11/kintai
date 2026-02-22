<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Database\Eloquent\Factories\Factory;

class BreakTimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = BreakTime::class;

    public function definition(): array
    {
        return [
            'attendance_id' => Attendance::factory(),
            'break_in_at' => $this->faker->dateTime(),
            'break_out_at' => $this->faker->dateTime(),
        ];
    }
}
