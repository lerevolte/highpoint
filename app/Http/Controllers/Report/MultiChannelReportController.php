<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\UnifiedCustomer;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class MultiChannelReportController extends Controller
{
    /**
     * Отображает отчет по мультиканальным последовательностям.
     */
    public function index(Request $request, Project $project)
    {
        $customersQuery = UnifiedCustomer::where('project_id', $project->id);

        if ($request->anyFilled(['start_date', 'end_date', 'utm_source', 'utm_medium', 'utm_campaign'])) {
            $customersQuery->whereHas('identities', function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->whereIn('identity_value', function ($subQuery) use ($request) {
                        $subQuery->select('session_id')->from('visits')->whereNotNull('session_id')->where(fn($v) => $this->applyVisitFilters($v, $request));
                    })->orWhereIn('identity_value', function ($subQuery) use ($request) {
                        $subQuery->select('user_id')->from('visits')->whereNotNull('user_id')->where(fn($v) => $this->applyVisitFilters($v, $request));
                    })->orWhereIn('identity_value', function ($subQuery) use ($request) {
                        $subQuery->select('metrika_client_id')->from('visits')->whereNotNull('metrika_client_id')->where(fn($v) => $this->applyVisitFilters($v, $request));
                    });
                });
            });
        }

        $customers = $customersQuery->with('identities')->latest()->paginate(20);

        $journeys = [];
        foreach ($customers as $customer) {
            $identityValues = $customer->identities->pluck('identity_value');

            $visitsQuery = Visit::where(function ($q) use ($identityValues) {
                $q->whereIn('session_id', $identityValues)
                  ->orWhereIn('user_id', $identityValues)
                  ->orWhereIn('metrika_client_id', $identityValues);
            });

            $visits = $visitsQuery->orderBy('created_at', 'asc')->get();

            if ($visits->isEmpty()) continue;

            $path = $visits->map(fn($visit) => $this->formatVisitSource($visit));
            $journeys[$customer->id] = [
                'path' => $path->implode(' -> '),
                'visits_count' => $visits->count(),
                'first_visit_at' => $visits->first()->created_at,
                'last_visit_at' => $visits->last()->created_at,
            ];
        }

        // Получаем все уникальные источники, каналы и кампании для выпадающих списков
        $sources = Visit::where('project_id', $project->id)->whereNotNull('utm_source')->distinct()->pluck('utm_source');
        $mediums = Visit::where('project_id', $project->id)->whereNotNull('utm_medium')->distinct()->pluck('utm_medium');
        $campaigns = Visit::where('project_id', $project->id)->whereNotNull('utm_campaign')->distinct()->pluck('utm_campaign');

        return view('reports.multi-channel', [
            'project' => $project,
            'customers' => $customers,
            'journeys' => $journeys,
            'sources' => $sources,
            'mediums' => $mediums,
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * Готовит и возвращает данные для диаграммы Сэнки в формате JSON.
     */
    public function sankeyData(Request $request, Project $project)
    {
        $customersQuery = UnifiedCustomer::where('project_id', $project->id);

        if ($request->anyFilled(['start_date', 'end_date', 'utm_source', 'utm_medium', 'utm_campaign'])) {
            $customersQuery->whereHas('identities', function ($query) use ($request) {
                 $query->where(function ($q) use ($request) {
                    $q->whereIn('identity_value', function ($subQuery) use ($request) {
                        $subQuery->select('session_id')->from('visits')->whereNotNull('session_id')->where(fn($v) => $this->applyVisitFilters($v, $request));
                    })->orWhereIn('identity_value', function ($subQuery) use ($request) {
                        $subQuery->select('user_id')->from('visits')->whereNotNull('user_id')->where(fn($v) => $this->applyVisitFilters($v, $request));
                    })->orWhereIn('identity_value', function ($subQuery) use ($request) {
                        $subQuery->select('metrika_client_id')->from('visits')->whereNotNull('metrika_client_id')->where(fn($v) => $this->applyVisitFilters($v, $request));
                    });
                });
            });
        }

        $customers = $customersQuery->with('identities')->get();
        $links = [];

        foreach ($customers as $customer) {
            $identityValues = $customer->identities->pluck('identity_value');
            $visits = Visit::where(function ($q) use ($identityValues) {
                $q->whereIn('session_id', $identityValues)
                  ->orWhereIn('user_id', $identityValues)
                  ->orWhereIn('metrika_client_id', $identityValues);
            })
            ->orderBy('created_at', 'asc')
            ->get();

            if ($visits->count() < 2) {
                continue;
            }

            $path = $visits->map(function ($visit, $index) {
                return ($index + 1) . '. ' . $this->formatVisitSource($visit);
            })->toArray();

            for ($i = 0; $i < count($path) - 1; $i++) {
                $links[] = [
                    'from' => $path[$i],
                    'to' => $path[$i + 1],
                ];
            }
        }

        $aggregatedLinks = collect($links)
            ->groupBy(fn($item) => $item['from'] . ' -> ' . $item['to'])
            ->map(fn($group) => [$group->first()['from'], $group->first()['to'], $group->count()])
            ->values()
            ->toArray();

        return response()->json($aggregatedLinks);
    }

    /**
     * Экспортирует данные отчета в CSV.
     */
    public function exportCsv(Request $request, Project $project)
    {
        $customersQuery = UnifiedCustomer::where('project_id', $project->id);

        if ($request->anyFilled(['start_date', 'end_date', 'utm_source', 'utm_medium', 'utm_campaign'])) {
            $customersQuery->whereHas('identities', function ($query) use ($request) {
                 $query->where(function ($q) use ($request) {
                    $q->whereIn('identity_value', function ($subQuery) use ($request) {
                        $subQuery->select('session_id')->from('visits')->whereNotNull('session_id')->where(fn($v) => $this->applyVisitFilters($v, $request));
                    })->orWhereIn('identity_value', function ($subQuery) use ($request) {
                        $subQuery->select('user_id')->from('visits')->whereNotNull('user_id')->where(fn($v) => $this->applyVisitFilters($v, $request));
                    })->orWhereIn('identity_value', function ($subQuery) use ($request) {
                        $subQuery->select('metrika_client_id')->from('visits')->whereNotNull('metrika_client_id')->where(fn($v) => $this->applyVisitFilters($v, $request));
                    });
                });
            });
        }
        
        $customers = $customersQuery->with('identities')->get();
        
        $dataForCsv = [];
        foreach ($customers as $customer) {
            $identityValues = $customer->identities->pluck('identity_value');
            
            $visits = Visit::where(function ($query) use ($identityValues) {
                $query->whereIn('session_id', $identityValues)
                      ->orWhereIn('user_id', $identityValues)
                      ->orWhereIn('metrika_client_id', $identityValues);
            })
            ->orderBy('created_at', 'asc')
            ->get();

            if ($visits->isEmpty()) continue;

            $path = $visits->map(fn($visit) => $this->formatVisitSource($visit))->implode(' -> ');

            $dataForCsv[] = [
                $customer->id,
                $path,
                $visits->count(),
                $visits->first()->created_at->toDateTimeString(),
                $visits->last()->created_at->toDateTimeString(),
            ];
        }
        
        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=multi-channel-report.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        // --- ИСПРАВЛЕНИЕ: Формируем CSV в памяти, а не потоком ---
        $file = fopen('php://memory', 'w');
        
        fputcsv($file, ['Customer ID', 'Path', 'Visits Count', 'First Visit', 'Last Visit']);
        foreach ($dataForCsv as $row) {
            fputcsv($file, $row);
        }
        
        rewind($file);
        $csvContent = stream_get_contents($file);
        fclose($file);
        // --- КОНЕЦ ИСПРАВЛЕНИЯ ---

        return response($csvContent, 200, $headers);
    }


    /**
     * Вспомогательная функция для применения фильтров к запросу визитов.
     */
    private function applyVisitFilters($query, Request $request)
    {
        return $query
            ->when($request->filled('start_date'), fn($q) => $q->whereDate('created_at', '>=', $request->start_date))
            ->when($request->filled('end_date'), fn($q) => $q->whereDate('created_at', '<=', $request->end_date))
            ->when($request->filled('utm_source'), fn($q) => $q->where('utm_source', $request->utm_source))
            ->when($request->filled('utm_medium'), fn($q) => $q->where('utm_medium', 'like', '%' . $request->utm_medium . '%'))
            ->when($request->filled('utm_campaign'), fn($q) => $q->where('utm_campaign', 'like', '%' . $request->utm_campaign . '%'));
    }
    
    /**
     * Вспомогательная функция для форматирования источника визита.
     */
    private function formatVisitSource(Visit $visit): string
    {
        if ($visit->utm_source) {
            return $visit->utm_source . ' / ' . ($visit->utm_medium ?? 'cpc');
        }
        if ($visit->referrer) {
            $host = parse_url($visit->referrer, PHP_URL_HOST);
            return str_replace('www.', '', $host) . ' / organic';
        }
        return '(direct) / (none)';
    }
}

