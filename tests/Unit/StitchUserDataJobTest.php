<?php

namespace Tests\Unit;

use App\Jobs\StitchUserDataJob;
use App\Models\Project;
use App\Models\UnifiedCustomer;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StitchUserDataJobTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Сценарий 1: Создание нового единого профиля для визита с новыми идентификаторами.
     */
    public function test_it_creates_a_new_unified_customer_for_a_new_visit()
    {
        // 1. Подготовка: Создаем визит с уникальными идентификаторами.
        $visit = Visit::factory()->create([
            'user_id' => 'new_user_123',
            'session_id' => 'new_session_abc',
        ]);

        // 2. Действие: Запускаем обработку задачи напрямую.
        (new StitchUserDataJob($visit))->handle();

        // 3. Проверки:
        // Убеждаемся, что в базе был создан только ОДИН единый профиль.
        $this->assertDatabaseCount('unified_customers', 1);

        // Находим этот профиль.
        $customer = UnifiedCustomer::first();
        $this->assertNotNull($customer);

        // Проверяем, что к этому профилю привязаны оба идентификатора из визита.
        $this->assertDatabaseHas('customer_identities', [
            'unified_customer_id' => $customer->id,
            'identity_type' => 'user_id',
            'identity_value' => 'new_user_123',
        ]);
        $this->assertDatabaseHas('customer_identities', [
            'unified_customer_id' => $customer->id,
            'identity_type' => 'session_id',
            'identity_value' => 'new_session_abc',
        ]);
    }

    /**
     * Сценарий 2: Привязка визита к существующему профилю.
     */
    public function test_it_finds_an_existing_customer()
    {
        // 1. Подготовка:
        // Создаем существующий профиль клиента.
        $customer = UnifiedCustomer::factory()->create();
        // Привязываем к нему идентификатор сессии.
        $customer->identities()->create([
            'identity_type' => 'session_id',
            'identity_value' => 'existing_session_xyz',
        ]);

        // Создаем новый визит, который имеет тот же идентификатор сессии.
        $visit = Visit::factory()->create([
            'session_id' => 'existing_session_xyz',
        ]);

        // 2. Действие: Запускаем обработку задачи.
        (new StitchUserDataJob($visit))->handle();

        // 3. Проверки:
        // Убеждаемся, что НОВЫЙ профиль не был создан. В базе по-прежнему только один.
        $this->assertDatabaseCount('unified_customers', 1);
    }

    /**
     * Сценарий 3: Добавление нового идентификатора к существующему профилю.
     */
    public function test_it_adds_a_new_identity_to_an_existing_customer()
    {
        // 1. Подготовка:
        // Создаем существующий профиль с одним идентификатором (сессия).
        $customer = UnifiedCustomer::factory()->create();
        $customer->identities()->create([
            'identity_type' => 'session_id',
            'identity_value' => 'session_to_enrich_123',
        ]);

        // Создаем новый визит с тем же ID сессии, но с добавленным user_id.
        $visit = Visit::factory()->create([
            'session_id' => 'session_to_enrich_123',
            'user_id' => 'enriched_user_id_456',
        ]);

        // 2. Действие: Запускаем обработку задачи.
        (new StitchUserDataJob($visit))->handle();

        // 3. Проверки:
        // Убеждаемся, что новый профиль не был создан.
        $this->assertDatabaseCount('unified_customers', 1);
        // Убеждаемся, что в таблице идентификаторов теперь ДВЕ записи,
        // и обе привязаны к нашему первому профилю.
        $this->assertDatabaseCount('customer_identities', 2);
        $this->assertDatabaseHas('customer_identities', [
            'unified_customer_id' => $customer->id,
            'identity_type' => 'user_id',
            'identity_value' => 'enriched_user_id_456',
        ]);
    }
}
