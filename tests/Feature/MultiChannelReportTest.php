<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\UnifiedCustomer;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MultiChannelReportTest extends TestCase
{
    use RefreshDatabase;

    private Project $project;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->project = Project::factory()->create();
        $this->user = User::factory()->create();
        $this->project->users()->attach($this->user->id, ['is_admin' => true]);
    }

    /**
     * Тест: Проверяем, что отчет правильно фильтрует клиентов.
     */
    public function test_multi_channel_report_filters_correctly()
    {
        // --- Arrange ---
        // Клиент 1: Попадает в выборку по дате и источнику
        $customer1 = UnifiedCustomer::factory()->for($this->project)->create();
        $visit1 = Visit::factory()->for($this->project)->create([
            'created_at' => Carbon::today(),
            'utm_source' => 'google',
            'session_id' => 'session_1'
        ]);
        $customer1->identities()->create(['identity_type' => 'session_id', 'identity_value' => 'session_1']);

        // Клиент 2: Не попадает в выборку по дате
        $customer2 = UnifiedCustomer::factory()->for($this->project)->create();
        $visit2 = Visit::factory()->for($this->project)->create([
            'created_at' => Carbon::yesterday(),
            'utm_source' => 'google',
            'session_id' => 'session_2'
        ]);
        $customer2->identities()->create(['identity_type' => 'session_id', 'identity_value' => 'session_2']);

        $filterParams = http_build_query([
            'start_date' => Carbon::today()->toDateString(),
            'end_date' => Carbon::today()->toDateString(),
            'utm_source' => 'google'
        ]);

        // --- Act ---
        $response = $this->actingAs($this->user)
            ->get(route('analytics.multi-channel-sequences', $this->project) . '?' . $filterParams);

        // --- Assert ---
        $response->assertOk();
        // Проверяем, что в представлении есть только один клиент
        $response->assertViewHas('customers', function ($customers) {
            return $customers->count() === 1;
        });
        $response->assertSee('google / cpc');
        $response->assertDontSee('session_2');
    }

    /**
     * Тест: Проверяем, что экспорт в CSV работает корректно.
     */
    public function test_multi_channel_report_exports_to_csv_correctly()
    {
        // --- Arrange ---
        $customer = UnifiedCustomer::factory()->for($this->project)->create();
        $visit = Visit::factory()->for($this->project)->create([
            'created_at' => Carbon::today(),
            'utm_source' => 'google',
            'session_id' => 'session_export'
        ]);
        $customer->identities()->create(['identity_type' => 'session_id', 'identity_value' => 'session_export']);

        // --- Act ---
        $response = $this->actingAs($this->user)
            ->get(route('analytics.multi-channel-sequences.export', $this->project));

        // --- Assert ---
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        // --- КЛЮЧЕВОЕ ИСПРАВЛЕНИЕ ---
        // Проверяем, что контент начинается с правильной строки заголовка,
        // включая кавычки, которые добавляет fputcsv.
        $expectedHeader = '"Customer ID",Path,"Visits Count","First Visit","Last Visit"';
        $this->assertStringContainsString($expectedHeader, $response->getContent());
        // --- КОНЕЦ ИСПРАВЛЕНИЯ ---
    }
}
