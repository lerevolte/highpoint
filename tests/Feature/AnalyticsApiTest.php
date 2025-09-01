<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Project;

class AnalyticsApiTest extends TestCase
{
    use RefreshDatabase; // Эта строка автоматически очищает базу данных перед каждым тестом

    /**
     * Тест успешного сбора данных ("Счастливый путь").
     *
     * @return void
     */
    public function test_collects_analytics_data_successfully()
    {
        // 1. Подготовка: Создаем проект в базе данных, чтобы на него можно было ссылаться.
        $project = Project::factory()->create();

        // 2. Данные: Готовим массив данных, имитирующий то, что отправляет tracker.js.
        $payload = [
            'project_id' => $project->counter_id, // Используем counter_id, как и в реальном запросе
            'domain' => 'example.com',
            'url' => 'https://example.com/test-page',
            'user_agent' => 'Test User Agent',
            'session_id' => 'session_12345',
            'is_new_session' => true,
            'user_id' => 'crm_user_abc',
            'metrika_client_id' => 'metrika_123456',
            'title' => 'Test Page Title',
            'scroll_depth' => 85,
            'time_on_page' => 120000, // в миллисекундах
            'events' => [
                [
                    'name' => 'button_click',
                    'data' => ['button_id' => 'cta-button']
                ]
            ]
        ];

        // 3. Действие: Отправляем POST-запрос на наш API-эндпоинт.
        $response = $this->postJson('/api/collect', $payload);

        // 4. Проверки (Assertions): Убеждаемся, что все прошло как надо.
        $response->assertStatus(201); // Проверяем, что ответ сервера - "201 Created"

        // Проверяем, что в таблице 'visits' появилась запись с нашими данными.
        // Важно: в базе мы ищем по числовому $project->id, а не по counter_id.
        $this->assertDatabaseHas('visits', [
            'project_id' => $project->id,
            'session_id' => 'session_12345',
            'url' => 'https://example.com/test-page',
            'user_id' => 'crm_user_abc',
            'scroll_depth' => 85,
        ]);

        // Проверяем, что в таблице 'events' появилось наше событие.
        $this->assertDatabaseHas('events', [
            'project_id' => $project->id,
            'event_name' => 'button_click',
        ]);
    }

    /**
     * Тест ошибки валидации при отсутствии обязательного поля.
     *
     * @return void
     */
    public function test_returns_validation_error_for_missing_project_id()
    {
        // 1. Подготовка: Готовим некорректные данные без project_id.
        $payload = [
            'domain' => 'example.com',
            'url' => 'https://example.com/test-page',
            'user_agent' => 'Test User Agent',
            'session_id' => 'session_12345',
            'is_new_session' => true,
        ];

        // 2. Действие: Отправляем некорректный запрос.
        $response = $this->postJson('/api/collect', $payload);

        // 3. Проверки:
        $response->assertStatus(422); // Проверяем, что сервер вернул ошибку валидации
        $response->assertJsonValidationErrors(['project_id']); // Убеждаемся, что ошибка именно для поля 'project_id'
    }
}
