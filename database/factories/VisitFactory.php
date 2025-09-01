<?php
namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Visit>
 */
class VisitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'project_id' => Project::factory(),
            'session_id' => $this->faker->uuid,
            'user_id' => null,
            'metrika_client_id' => null,
            'url' => $this->faker->url,
            'tracker_domain' => $this->faker->domainName,
            'user_agent' => $this->faker->userAgent,
            'ip_address' => $this->faker->ipv4,
            'is_new_session' => $this->faker->boolean,
        ];
    }
}