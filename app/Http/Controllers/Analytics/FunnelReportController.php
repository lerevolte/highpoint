<?php
// Файл: app/Http/Controllers/Analytics/FunnelReportController.php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class FunnelReportController extends Controller
{
    public function index(Project $project)
    {
        $funnelSteps = ['page_view', 'add_to_cart', 'begin_checkout', 'purchase'];
        $funnelData = [];
        $previousStepUserIds = null;

        foreach ($funnelSteps as $index => $step) {
            // Начинаем строить запрос для текущего шага
            $query = DB::table('events')
                ->join('visits', 'events.visit_id', '=', 'visits.id')
                ->join('customer_identities', function ($join) {
                    $join->on('visits.session_id', '=', 'customer_identities.identity_value')
                        ->orOn('visits.user_id', '=', 'customer_identities.identity_value')
                        ->orOn('visits.metrika_client_id', '=', 'customer_identities.identity_value');
                })
                ->where('events.project_id', $project->id)
                ->where('events.event_name', $step);

            // Для всех шагов, кроме первого, мы ищем только среди тех пользователей,
            // кто уже прошел предыдущий шаг. Это обеспечивает корректность воронки.
            if ($previousStepUserIds !== null) {
                $query->whereIn('customer_identities.unified_customer_id', $previousStepUserIds);
            }

            // Получаем уникальных пользователей на текущем шаге
            $currentUserIds = $query->select('customer_identities.unified_customer_id')->distinct()->pluck('unified_customer_id');
            $userCount = $currentUserIds->count();

            $funnelData[] = [
                'name' => $this->translateStepName($step),
                'count' => $userCount,
            ];

            // Сохраняем пользователей этого шага для фильтрации на следующем
            $previousStepUserIds = $currentUserIds;
        }
        
        // Считаем конверсии
        for ($i = 0; $i < count($funnelData); $i++) {
            if ($i === 0) {
                $funnelData[$i]['conversion_from_start'] = 100.0;
                $funnelData[$i]['conversion_from_previous'] = 100.0;
            } else {
                $totalUsers = $funnelData[0]['count'];
                $previousStepUsers = $funnelData[$i - 1]['count'];
                $currentUsers = $funnelData[$i]['count'];

                $funnelData[$i]['conversion_from_start'] = $totalUsers > 0 ? round(($currentUsers / $totalUsers) * 100, 2) : 0;
                $funnelData[$i]['conversion_from_previous'] = $previousStepUsers > 0 ? round(($currentUsers / $previousStepUsers) * 100, 2) : 0;
            }
        }

        return view('reports.funnel', compact('project', 'funnelData'));
    }

    private function translateStepName(string $step): string
    {
        $translations = [
            'page_view' => 'Посещение сайта',
            'add_to_cart' => 'Добавление в корзину',
            'begin_checkout' => 'Начало оформления',
            'purchase' => 'Покупка',
        ];
        return $translations[$step] ?? $step;
    }
}

