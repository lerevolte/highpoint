<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Project;
use App\Models\UnifiedCustomer;
use App\Models\Visit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class FunnelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $project = Project::where('name', 'Основной проект')->first();
        if (!$project) {
            $this->command->warn('Основной проект не найден, FunnelSeeder пропущен.');
            return;
        }

        // Берем 15 случайных клиентов для генерации воронок
        $customers = UnifiedCustomer::where('project_id', $project->id)->inRandomOrder()->take(15)->get();
        if ($customers->isEmpty()) {
            $this->command->warn('Клиенты не найдены, FunnelSeeder пропущен.');
            return;
        }

        $funnelSteps = ['page_view', 'add_to_cart', 'begin_checkout', 'purchase'];

        foreach ($customers as $customer) {
            // Находим последний визит клиента, чтобы привязать к нему события
            $lastVisit = $this->findCustomerVisit($customer);
            if (!$lastVisit) continue;

            $eventTime = Carbon::parse($lastVisit->created_at)->addMinutes(1);

            // Случайно определяем, как далеко зайдет пользователь по воронке
            $maxStepIndex = rand(0, count($funnelSteps)); // Может пройти от 0 до всех шагов

            for ($i = 0; $i < $maxStepIndex; $i++) {
                $eventName = $funnelSteps[$i];
                Event::create([
                    'project_id' => $project->id,
                    'visit_id' => $lastVisit->id,
                    'event_name' => $eventName,
                    'value' => $eventName === 'purchase' ? rand(500, 10000) / 10 : null, // Добавляем сумму для покупок
                    'created_at' => $eventTime,
                    'updated_at' => $eventTime,
                ]);
                $eventTime->addMinutes(rand(1, 5));
            }
        }
    }

    private function findCustomerVisit(UnifiedCustomer $customer)
    {
        $identityValues = $customer->identities->pluck('identity_value');
        return Visit::whereIn('session_id', $identityValues)
            ->orWhereIn('user_id', $identityValues)
            ->orWhereIn('metrika_client_id', $identityValues)
            ->latest()
            ->first();
    }
}
