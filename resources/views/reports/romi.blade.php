@extends('analytics.layout')

@section('title', 'Отчет ROMI')
@section('project-name', $project->name)



@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Отчет по ROMI</h1>

    {{-- Форма фильтрации --}}
    <div class="bg-white shadow-md rounded-lg p-4 mb-8">
        <form id="filters-form" action="{{ route('analytics.romi-report', $project) }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Дата начала</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date', now()->subMonth()->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">Дата окончания</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date', now()->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
            </div>
            <div class="flex space-x-2">
                <a id="export-link" href="#" class="w-full inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Экспорт</a>
                <a href="{{ route('analytics.romi-report', $project) }}" class="w-full inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Сбросить</a>
                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">Применить</button>
            </div>
        </form>
    </div>

    {{-- Контейнер для графика --}}
    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Визуализация: Расходы vs Доходы</h2>
        <div id="romi_chart_container" style="width: 100%; height: 400px;">
            <div class="flex items-center justify-center h-full text-gray-500">Загрузка графика...</div>
        </div>
    </div>

    {{-- Таблица с отчетом --}}
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr class="border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        <th class="px-5 py-3">Канал / Источник / Кампания</th>
                        <th class="px-5 py-3 text-right">Расходы</th>
                        <th class="px-5 py-3 text-right">Доходы</th>
                        <th class="px-5 py-3 text-right">Прибыль</th>
                        <th class="px-5 py-3 text-right">ROMI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData as $row)
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="px-5 py-4 text-sm">
                                <p class="font-semibold">{{ $row['source'] ?? '(не указан)' }}</p>
                                <p class="text-xs text-gray-600">{{ $row['medium'] ?? '(не указан)' }}</p>
                                <p class="text-xs text-gray-500">{{ $row['campaign'] ?? '(не указана)' }}</p>
                            </td>
                            <td class="px-5 py-4 text-sm text-right">{{ number_format($row['cost'], 2, ',', ' ') }}</td>
                            <td class="px-5 py-4 text-sm text-right">{{ number_format($row['revenue'], 2, ',', ' ') }}</td>
                            <td class="px-5 py-4 text-sm text-right font-semibold {{ $row['profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($row['profit'], 2, ',', ' ') }}
                            </td>
                            <td class="px-5 py-4 text-sm text-right font-bold {{ $row['romi'] === null ? '' : ($row['romi'] >= 0 ? 'text-green-600' : 'text-red-600') }}">
                                {{ $row['romi'] === null ? '∞' : number_format($row['romi'], 2, ',', ' ') . '%' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-10 text-gray-500">Нет данных для построения отчета за выбранный период.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        const chartContainer = document.getElementById('romi_chart_container');
        const queryParams = new URLSearchParams(window.location.search).toString();
        const dataUrl = `{{ route('analytics.romi-report.chart-data', $project) }}?${queryParams}`;

        fetch(dataUrl)
            .then(response => response.json())
            .then(chartData => {
                if (chartData.length <= 1) { // Проверяем, есть ли данные кроме заголовков
                    chartContainer.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">Нет данных для построения графика.</div>';
                    return;
                }

                const data = google.visualization.arrayToDataTable(chartData);

                const options = {
                    title: 'Сравнение расходов и доходов по каналам',
                    chartArea: {width: '80%', height: '80%'},
                    hAxis: { title: 'Канал', slantedText: true, slantedTextAngle: 30 },
                    vAxis: { title: 'Сумма', minValue: 0 },
                    legend: { position: 'top' },
                    colors: ['#ef4444', '#22c55e'] // Красный для расходов, зеленый для доходов
                };

                const chart = new google.visualization.ColumnChart(chartContainer);
                chart.draw(data, options);
            })
            .catch(error => {
                console.error('Error fetching chart data:', error);
                chartContainer.innerHTML = '<div class="flex items-center justify-center h-full text-red-500">Не удалось загрузить данные для графика.</div>';
            });
    }
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('filters-form');
        const exportLink = document.getElementById('export-link');
        const baseUrl = "{{ route('analytics.romi-report.export', $project) }}";

        function updateExportLink() {
            const formData = new FormData(form);
            for (let [key, value] of [...formData.entries()]) {
                if (value === '') {
                    formData.delete(key);
                }
            }
            const params = new URLSearchParams(formData).toString();
            exportLink.href = `${baseUrl}?${params}`;
        }

        updateExportLink();
        form.addEventListener('input', updateExportLink);
    });
</script>
@endsection

