<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Project;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'project_id' => Project::factory(),
            'visit_id' => Visit::factory(),
            'event_name' => $this->faker->randomElement(['page_view', 'add_to_cart', 'purchase']),
            'event_data' => null,
            'value' => $this->faker->optional()->randomFloat(2, 10, 1000),
        ];
    }
}
