<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\MarketingCost;
use App\Models\Project;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RomiReportTest extends TestCase
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

        // --- Подготовка тестовых данных ---
        $today = Carbon::today();

        // 1. Прибыльный канал (google / cpc)
        MarketingCost::factory()->create([
            'project_id' => $this->project->id,
            'source' => 'google',
            'medium' => 'cpc',
            'cost' => 1000,
            'date' => $today,
        ]);
        $this->createRevenueEvent('google', 5000, $today);

        // 2. Убыточный канал (yandex / cpc)
        MarketingCost::factory()->create([
            'project_id' => $this->project->id,
            'source' => 'yandex',
            'medium' => 'cpc',
            'cost' => 2000,
            'date' => $today,
        ]);
        $this->createRevenueEvent('yandex', 500, $today);

        // 3. Канал без выручки (vk / cpc)
        MarketingCost::factory()->create([
            'project_id' => $this->project->id,
            'source' => 'vk',
            'medium' => 'cpc',
            'cost' => 500,
            'date' => $today,
        ]);
    }

    /**
     * Тест: Проверяем правильность расчетов ROMI.
     */
    public function test_romi_report_calculates_correctly()
    {
        $todayString = Carbon::today()->toDateString();
        
        // ИСПРАВЛЕНИЕ: Явно передаем диапазон дат в запросе, чтобы тест был надежным
        $response = $this->actingAs($this->user)
            ->get(route('analytics.romi-report', [
                'project' => $this->project,
                'start_date' => $todayString,
                'end_date' => $todayString,
            ]));

        $response->assertOk();
        $reportData = $response->viewData('reportData');

        $this->assertCount(3, $reportData, 'Report should contain data for all 3 channels.');

        // --- Тест 1: Прибыльный канал ---
        $profitChannelData = $reportData->firstWhere('channel', 'google / cpc');
        $this->assertNotNull($profitChannelData, 'Data for the profitable channel should exist.');
        $this->assertEquals(1000, $profitChannelData['cost']);
        $this->assertEquals(5000, $profitChannelData['revenue']);
        $this->assertEquals(400, $profitChannelData['romi']);

        // --- Тест 2: Убыточный канал ---
        $lossChannelData = $reportData->firstWhere('channel', 'yandex / cpc');
        $this->assertNotNull($lossChannelData, 'Data for the loss-making channel should exist.');
        $this->assertEquals(2000, $lossChannelData['cost']);
        $this->assertEquals(500, $lossChannelData['revenue']);
        $this->assertEquals(-75, $lossChannelData['romi']);

        // --- Тест 3: Канал без выручки ---
        $noRevenueChannelData = $reportData->firstWhere('channel', 'vk / cpc');
        $this->assertNotNull($noRevenueChannelData, 'Data for the no-revenue channel should exist.');
        $this->assertEquals(500, $noRevenueChannelData['cost']);
        $this->assertEquals(0, $noRevenueChannelData['revenue']);
        $this->assertEquals(-100, $noRevenueChannelData['romi']);
    }

    /**
     * Вспомогательная функция для создания события 'purchase'.
     */
    private function createRevenueEvent(string $source, float $value, Carbon $date)
    {
        $visit = Visit::factory()->for($this->project)->create([
            'utm_source' => $source,
            'utm_medium' => 'cpc',
            'created_at' => $date,
        ]);
        Event::factory()->for($this->project)->create([
            'visit_id' => $visit->id,
            'event_name' => 'purchase',
            'value' => $value,
            'created_at' => $date,
        ]);
    }
}

