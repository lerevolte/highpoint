<?php

namespace Database\Factories;

use App\Models\MarketingCost;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MarketingCost>
 */
class MarketingCostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MarketingCost::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'project_id' => Project::factory(),
            'date' => $this->faker->date(),
            'source' => $this->faker->randomElement(['google', 'yandex', 'vk', 'facebook']),
            'medium' => $this->faker->randomElement(['cpc', 'organic', 'referral']),
            'campaign' => 'campaign_' . $this->faker->word,
            'cost' => $this->faker->randomFloat(2, 100, 5000),
        ];
    }
}
