<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\UnifiedCustomer;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AnalyticsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Создаем пользователя-администратора, если его нет
        $adminUser = User::firstOrCreate(
            ['email' => 'lerevolte@yandex.ru'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('i2251qbg')
            ]
        );

        // 2. Создаем основной проект и привязываем к нему администратора
        $project = Project::firstOrCreate(
            ['name' => 'Основной проект'],
            [
                'currency' => 'RUB',
                'domain' => 'expressbuket.com',
            ]
        );

        // Привязываем администратора к проекту, если он еще не привязан
        if (!$project->users()->where('user_id', $adminUser->id)->exists()) {
            $project->users()->attach($adminUser->id, ['is_admin' => true]);
        }

        $this->command->info('Администратор и основной проект созданы/проверены.');

        // 3. Определяем возможные источники трафика для имитации
        $sources = [
            ['source' => 'google', 'medium' => 'cpc'],
            ['source' => 'yandex', 'medium' => 'cpc'],
            ['source' => 'vk.com', 'medium' => 'referral'],
            ['source' => 'facebook.com', 'medium' => 'social'],
            ['source' => null, 'medium' => null], // Прямой заход
        ];

        // 4. Создаем 15 фейковых "единых клиентов" для основного проекта
        for ($i = 0; $i < 15; $i++) {
            $customer = UnifiedCustomer::create([
                'project_id' => $project->id,
                'crm_user_id' => 'CRM-' . Str::random(6),
            ]);

            // Для каждого клиента создаем уникальные идентификаторы
            $sessionId = 'session_' . Str::uuid();
            $userId = 'user_' . Str::random(10);

            $customer->identities()->create([
                'identity_type' => 'session_id',
                'identity_value' => $sessionId,
            ]);
            $customer->identities()->create([
                'identity_type' => 'user_id',
                'identity_value' => $userId,
            ]);

            // 5. Для каждого клиента генерируем путь из 2-5 визитов
            $visitCount = rand(2, 5);
            $firstVisitDate = now()->subDays(rand(10, 30));

            for ($j = 0; $j < $visitCount; $j++) {
                $sourceInfo = $sources[array_rand($sources)];

                Visit::create([
                    'project_id' => $project->id,
                    'session_id' => $sessionId,
                    'user_id' => ($j > 1) ? $userId : null,
                    'url' => 'https://main-project.com/page/' . ($j + 1),
                    'tracker_domain' => 'main-project.com',
                    'user_agent' => 'Seeder User Agent',
                    'ip_address' => '127.0.0.1',
                    'is_new_session' => ($j === 0),
                    'utm_source' => $sourceInfo['source'],
                    'utm_medium' => $sourceInfo['medium'],
                    'referrer' => $sourceInfo['medium'] === 'referral' ? 'https://' . $sourceInfo['source'] : null,
                    'created_at' => $firstVisitDate->addDays($j * 2)->addHours(rand(1, 12)),
                ]);
            }
        }

        $this->command->info('Тестовые данные для аналитики успешно созданы!');
    }
}