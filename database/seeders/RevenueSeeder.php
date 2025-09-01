<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\UnifiedCustomer;
use App\Models\Visit;
use Illuminate\Database\Seeder;

class RevenueSeeder extends Seeder
{
    public function run()
    {
        // Находим клиентов, у которых есть user_id (имитация "залогиненных" пользователей)
        $customersWithUserId = UnifiedCustomer::whereNotNull('crm_user_id')->inRandomOrder()->limit(5)->get();

        if ($customersWithUserId->isEmpty()) {
            $this->command->info('Не найдены клиенты для создания событий о доходе.');
            return;
        }

        foreach ($customersWithUserId as $customer) {
            // Находим последний визит этого клиента
            $lastVisit = Visit::where('user_id', $customer->identities()->where('identity_type', 'user_id')->value('identity_value'))
                ->latest()
                ->first();

            if ($lastVisit) {
                // Создаем событие "покупки" для этого визита
                Event::create([
                    'project_id' => $lastVisit->project_id,
                    'visit_id' => $lastVisit->id,
                    'event_name' => 'purchase',
                    'value' => rand(1500, 25000) / 10.0, // Случайная сумма от 150 до 2500
                    'currency' => 'RUB',
                    'created_at' => $lastVisit->created_at->addMinutes(rand(5, 30)),
                ]);
            }
        }

        $this->command->info('Тестовые данные о доходах (события purchase) созданы.');
    }
}
