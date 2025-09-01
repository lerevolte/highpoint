<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Project;
use App\Models\UnifiedCustomer;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FunnelReportTest extends TestCase
{
    use RefreshDatabase;

    private Project $project;
    private UnifiedCustomer $customer1;
    private UnifiedCustomer $customer2;
    private User $user;

    /**
     * Настраиваем базовое окружение для каждого теста.
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Создаем проект, пользователя и привязываем его к проекту
        $this->project = Project::factory()->create();
        $this->user = User::factory()->create();
        $this->project->users()->attach($this->user->id, ['is_admin' => true]);

        // Создаем тестовых клиентов
        $this->customer1 = UnifiedCustomer::factory()->for($this->project)->create();
        $this->customer2 = UnifiedCustomer::factory()->for($this->project)->create();
    }

    /**
     * Тест 1: Проверяем идеальный сценарий, когда один клиент проходит всю воронку.
     */
    public function test_funnel_shows_full_conversion_path_correctly()
    {
        // Создаем визит и все 4 события для первого клиента
        $this->createFunnelEventsForCustomer($this->customer1, ['page_view', 'add_to_cart', 'begin_checkout', 'purchase']);

        // Запрашиваем отчет от имени аутентифицированного пользователя
        $response = $this->actingAs($this->user)
                         ->get(route('analytics.sales-funnel', $this->project));

        // Проверяем, что страница открылась успешно
        $response->assertOk();
        // Проверяем, что переменная funnelData передана в представление
        $response->assertViewHas('funnelData');

        $funnelData = $response->viewData('funnelData');

        // Проверяем количество пользователей на каждом шаге
        $this->assertEquals(1, $funnelData[0]['count']); // Посещение сайта
        $this->assertEquals(1, $funnelData[1]['count']); // Добавление в корзину
        $this->assertEquals(1, $funnelData[2]['count']); // Начало оформления
        $this->assertEquals(1, $funnelData[3]['count']); // Покупка

        // Проверяем расчет конверсий
        $this->assertEquals(100.0, $funnelData[1]['conversion_from_previous']);
        $this->assertEquals(100.0, end($funnelData)['conversion_from_start']);
    }

    /**
     * Тест 2: Проверяем сценарий, когда клиент бросает воронку на полпути.
     */
    public function test_funnel_shows_partial_conversion_path_correctly()
    {
        // Клиент только посмотрел страницу и добавил товар в корзину
        $this->createFunnelEventsForCustomer($this->customer1, ['page_view', 'add_to_cart']);

        $response = $this->actingAs($this->user)
                         ->get(route('analytics.sales-funnel', $this->project));
        $response->assertOk();
        $funnelData = $response->viewData('funnelData');

        // Проверяем количество
        $this->assertEquals(1, $funnelData[0]['count']); // Посещение сайта
        $this->assertEquals(1, $funnelData[1]['count']); // Добавление в корзину
        $this->assertEquals(0, $funnelData[2]['count']); // Начало оформления
        $this->assertEquals(0, $funnelData[3]['count']); // Покупка
        
        // Проверяем расчет конверсий
        $this->assertEquals(0.0, end($funnelData)['conversion_from_start']);
    }

    /**
     * Тест 3: Проверяем сложный сценарий с несколькими клиентами.
     */
    public function test_funnel_handles_multiple_users_correctly()
    {
        // Первый клиент прошел всю воронку
        $this->createFunnelEventsForCustomer($this->customer1, ['page_view', 'add_to_cart', 'begin_checkout', 'purchase']);
        // Второй клиент только посетил сайт
        $this->createFunnelEventsForCustomer($this->customer2, ['page_view']);

        $response = $this->actingAs($this->user)
                         ->get(route('analytics.sales-funnel', $this->project));
        $response->assertOk();
        $funnelData = $response->viewData('funnelData');

        // Проверяем количество
        $this->assertEquals(2, $funnelData[0]['count']); // 2 клиента посетили сайт
        $this->assertEquals(1, $funnelData[1]['count']); // 1 клиент добавил в корзину
        $this->assertEquals(1, $funnelData[2]['count']); // 1 клиент начал оформление
        $this->assertEquals(1, $funnelData[3]['count']); // 1 клиент купил

        // Проверяем расчет конверсий
        // Конверсия из "Посещение" (2) в "Корзину" (1) должна быть 50%
        $this->assertEquals(50.0, $funnelData[1]['conversion_from_previous']);
        // Общая конверсия (1 из 2) должна быть 50%
        $this->assertEquals(50.0, end($funnelData)['conversion_from_start']);
    }

    /**
     * Вспомогательная функция для создания цепочки событий для клиента.
     */
    private function createFunnelEventsForCustomer(UnifiedCustomer $customer, array $eventNames)
    {
        // Создаем один визит для клиента
        $visit = Visit::factory()->for($this->project)->create([
            'session_id' => 'session_' . $customer->id,
        ]);
        // Привязываем идентификатор визита к клиенту
        $customer->identities()->create([
            'identity_type' => 'session_id', // <-- ИСПРАВЛЕНИЕ
            'identity_value' => $visit->session_id
        ]);

        $eventTime = Carbon::now();
        foreach ($eventNames as $eventName) {
            Event::create([
                'project_id' => $this->project->id,
                'visit_id' => $visit->id,
                'event_name' => $eventName,
                'created_at' => $eventTime,
                'updated_at' => $eventTime,
            ]);
            // Сдвигаем время для следующего события, чтобы сохранить порядок
            $eventTime->addMinute();
        }
    }
}

