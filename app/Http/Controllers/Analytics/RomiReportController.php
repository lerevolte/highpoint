<?php
// Файл: app/Http/Controllers/Analytics/RomiReportController.php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\MarketingCost;
use App\Models\Project;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RomiReportController extends Controller
{
    public function index(Request $request, Project $project)
    {
        // ИСПРАВЛЕНИЕ: Определяем синтаксис конкатенации в зависимости от драйвера БД
        $dbDriver = DB::connection()->getDriverName();
        $channelConcat = ($dbDriver === 'sqlite')
            ? "source || ' / ' || medium"
            : "CONCAT(source, ' / ', medium)";
        $visitChannelConcat = ($dbDriver === 'sqlite')
            ? "visits.utm_source || ' / ' || visits.utm_medium"
            : "CONCAT(visits.utm_source, ' / ', visits.utm_medium)";

        // 1. Получаем расходы
        $costsQuery = MarketingCost::where('project_id', $project->id)
            ->select(
                DB::raw("$channelConcat as channel"),
                DB::raw('SUM(cost) as total_cost')
            )
            ->groupBy('channel');
        
        $costsQuery->when($request->filled('start_date'), fn($q) => $q->whereDate('date', '>=', $request->start_date));
        $costsQuery->when($request->filled('end_date'), fn($q) => $q->whereDate('date', '<=', $request->end_date));
        
        $costs = $costsQuery->get()->keyBy('channel');

        // 2. Получаем доходы
        $revenuesQuery = Visit::join('events', 'visits.id', '=', 'events.visit_id')
            ->where('events.project_id', $project->id)
            ->where('events.event_name', 'purchase')
            ->select(
                DB::raw("$visitChannelConcat as channel"),
                DB::raw('SUM(events.value) as total_revenue')
            )
            ->groupBy('channel');

        $revenuesQuery->when($request->filled('start_date'), fn($q) => $q->whereDate('events.created_at', '>=', $request->start_date));
        $revenuesQuery->when($request->filled('end_date'), fn($q) => $q->whereDate('events.created_at', '<=', $request->end_date));

        $revenues = $revenuesQuery->get()->keyBy('channel');

        // 3. Объединяем данные
        $allChannels = $costs->keys()->union($revenues->keys());
        $reportData = collect();

        foreach ($allChannels as $channel) {
            $cost = $costs->get($channel)->total_cost ?? 0;
            $revenue = $revenues->get($channel)->total_revenue ?? 0;

            if ($cost == 0 && $revenue == 0) continue;

            $romi = ($cost > 0) ? (($revenue - $cost) / $cost) * 100 : null;

            $reportData->push([
                'channel' => $channel,
                'cost' => $cost,
                'revenue' => $revenue,
                'romi' => $romi,
                'profit' => $revenue - $cost
            ]);
        }

        return view('reports.romi', [
            'project' => $project,
            'reportData' => $reportData,
        ]);
    }

    /**
     * Готовит данные для графика и возвращает в JSON.
     */
    public function chartData(Request $request, Project $project)
    {
        $reportData = $this->buildReportData($request, $project);

        // Форматируем данные для Google Charts
        $chartData = $reportData->map(function ($row) {
            $channelName = ($row['source'] ?? 'N/A') . ' / ' . ($row['medium'] ?? 'N/A') . ' / ' . ($row['campaign'] ?? 'N/A');
            return [$channelName, $row['cost'], $row['revenue']];
        })->values()->all();

        // Добавляем заголовки
        array_unshift($chartData, ['Канал', 'Расходы', 'Доходы']);

        return response()->json($chartData);
    }

    public function exportCsv(Request $request, Project $project)
    {
        $reportData = $this->buildReportData($request, $project)->map(function ($row) {
            $row['profit'] = $row['revenue'] - $row['cost'];
            $row['romi'] = $row['cost'] > 0 ? round(($row['profit'] / $row['cost']) * 100, 2) : null;
            return $row;
        });

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="romi_report_'.date("Y-m-d").'.csv"',
        ];

        $callback = function () use ($reportData) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            
            fputcsv($file, ['Источник', 'Канал', 'Кампания', 'Расходы', 'Доходы', 'Прибыль', 'ROMI (%)']);

            foreach ($reportData as $row) {
                fputcsv($file, [
                    $row['source'] ?? '',
                    $row['medium'] ?? '',
                    $row['campaign'] ?? '',
                    $row['cost'],
                    $row['revenue'],
                    $row['profit'],
                    $row['romi'],
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Основная логика для сбора и объединения данных о расходах и доходах.
     */
    private function buildReportData(Request $request, Project $project)
    {
        $costsQuery = $project->marketingCosts()
            ->select('source', 'medium', 'campaign', DB::raw('SUM(cost) as total_cost'))
            ->groupBy('source', 'medium', 'campaign');

        $revenueQuery = Event::join('visits', 'events.visit_id', '=', 'visits.id')
            ->where('events.project_id', $project->id)
            ->where('events.event_name', 'purchase')
            ->select(
                'visits.utm_source as source', 'visits.utm_medium as medium', 'visits.utm_campaign as campaign',
                DB::raw('SUM(events.value) as total_revenue')
            )
            ->groupBy('visits.utm_source', 'visits.utm_medium', 'visits.utm_campaign');
        
        if ($request->filled('start_date')) {
            $costsQuery->where('date', '>=', $request->start_date);
            $revenueQuery->where('events.created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $costsQuery->where('date', '<=', $request->end_date);
            $revenueQuery->where('events.created_at', '<=', $request->end_date);
        }

        $costs = $costsQuery->get();
        $revenues = $revenueQuery->get();

        $reportData = collect();
        
        foreach ($costs as $cost) {
            $key = ($cost->source ?? '') . '|' . ($cost->medium ?? '') . '|' . ($cost->campaign ?? '');
            $reportData->put($key, [
                'source' => $cost->source, 'medium' => $cost->medium, 'campaign' => $cost->campaign,
                'cost' => (float) $cost->total_cost, 'revenue' => 0,
            ]);
        }

        foreach ($revenues as $revenue) {
            $key = ($revenue->source ?? '') . '|' . ($revenue->medium ?? '') . '|' . ($revenue->campaign ?? '');
            
            $rowData = $reportData->get($key, [
                'source' => $revenue->source, 'medium' => $revenue->medium, 'campaign' => $revenue->campaign,
                'cost' => 0, 'revenue' => 0,
            ]);

            $rowData['revenue'] = (float) $revenue->total_revenue;
            $reportData->put($key, $rowData);
        }

        return $reportData;
    }


}
