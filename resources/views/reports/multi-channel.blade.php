@extends('analytics.layout')

@section('title', 'Мультиканальные последовательности')
@section('project-name', $project->name)



@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            Отчет: Мультиканальные последовательности
        </h1>
        <span class="text-lg text-gray-600">Проект: {{ $project->name }}</span>
    </div>

    <div class="bg-white shadow-md rounded-lg p-4 mb-8">
        <form id="filters-form" action="{{ route('analytics.multi-channel-sequences', $project) }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Дата начала</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">Дата окончания</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="utm_source" class="block text-sm font-medium text-gray-700">Источник</label>
                    <input type="text" name="utm_source" id="utm_source" value="{{ request('utm_source') }}" placeholder="google" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="utm_medium" class="block text-sm font-medium text-gray-700">Канал</label>
                    <input type="text" name="utm_medium" id="utm_medium" value="{{ request('utm_medium') }}" placeholder="cpc" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="utm_campaign" class="block text-sm font-medium text-gray-700">Кампания</label>
                    <input type="text" name="utm_campaign" id="utm_campaign" value="{{ request('utm_campaign') }}" placeholder="summer_sale" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
            </div>
            <div class="flex items-center justify-end space-x-3 pt-2">
                <a id="export-link" href="#" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Экспорт в CSV</a>
                <a href="{{ route('analytics.multi-channel-sequences', $project) }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Сбросить</a>
                <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">Применить</button>
            </div>
        </form>
    </div>


    {{-- Контейнер для диаграммы Сэнки --}}
    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <div class="flex items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-700">Визуализация путей клиентов</h2>
            {{-- Иконка помощи, которая открывает модальное окно --}}
            <button data-kb-slug="sankey-diagram-explained" class="kb-trigger ml-3 text-blue-500 hover:text-blue-700 font-bold text-lg">[?]</button>
        </div>
        <div id="sankey_chart_container" style="width: 100%; height: 500px;">
            <div class="flex items-center justify-center h-full">
                <p class="text-gray-500">Загрузка диаграммы...</p>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr class="border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        <th class="px-5 py-3">ID Клиента</th>
                        <th class="px-5 py-3">Путь клиента (Каналы)</th>
                        <th class="px-5 py-3 text-center">Кол-во визитов</th>
                        <th class="px-5 py-3">Первый визит</th>
                        <th class="px-5 py-3">Последний визит</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        @if(isset($journeys[$customer->id]))
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="px-5 py-5 text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap">#{{ $customer->id }}</p>
                                    @if($customer->crm_user_id)
                                        <p class="text-gray-600 whitespace-no-wrap text-xs">CRM: {{ $customer->crm_user_id }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-5 text-sm">
                                    <p class="text-gray-800 whitespace-no-wrap font-medium">{{ $journeys[$customer->id]['path'] }}</p>
                                </td>
                                <td class="px-5 py-5 text-sm text-center">
                                    <span class="inline-block bg-blue-100 text-blue-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded-full">
                                        {{ $journeys[$customer->id]['visits_count'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-5 text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap">{{ $journeys[$customer->id]['first_visit_at']->format('d.m.Y H:i') }}</p>
                                </td>
                                <td class="px-5 py-5 text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap">{{ $journeys[$customer->id]['last_visit_at']->format('d.m.Y H:i') }}</p>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-10 text-gray-500">
                                <p class="text-lg">Данные о путях клиентов еще не собраны.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-5 py-5 bg-white border-t flex flex-col xs:flex-row items-center xs:justify-between">
            {{ $customers->links() }}
        </div>
    </div>
</div>
<div id="kb-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-6xl mx-4 flex flex-col max-h-[90vh]">
        <div class="p-6 border-b flex justify-between items-center flex-shrink-0">
            <h3 id="kb-modal-title" class="text-2xl font-semibold">Загрузка...</h3>
            <button id="kb-modal-close" class="text-gray-500 hover:text-gray-800 text-3xl">&times;</button>
        </div>
        <div id="kb-modal-content" class="p-6 overflow-y-auto">
            <p>Пожалуйста, подождите...</p>
        </div>
    </div>
</div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    google.charts.load('current', {'packages':['sankey']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        const chartContainer = document.getElementById('sankey_chart_container');

        const queryParams = new URLSearchParams(window.location.search).toString();
        const dataUrl = `{{ route('analytics.multi-channel-sequences.sankey', $project) }}?${queryParams}`;

        
        // Запрашиваем данные с нашего нового эндпоинта
        fetch(dataUrl)
            .then(response => response.json())
            .then(data => {
                if (data.length === 0) {
                    chartContainer.innerHTML = '<div class="flex items-center justify-center h-full"><p class="text-gray-500">Недостаточно данных для построения диаграммы.</p></div>';
                    return;
                }

                const dataTable = new google.visualization.DataTable();
                dataTable.addColumn('string', 'From');
                dataTable.addColumn('string', 'To');
                dataTable.addColumn('number', 'Weight');
                dataTable.addRows(data);

                const options = {
                    width: '100%',
                    height: 500,
                    sankey: {
                        node: {
                            colors: ['#a6cee3', '#1f78b4', '#b2df8a', '#33a02c', '#fb9a99', '#e31a1c', '#fdbf6f', '#ff7f00', '#cab2d6', '#6a3d9a'],
                            label: {
                                fontName: 'Arial',
                                fontSize: 14,
                            }
                        },
                        link: {
                            colorMode: 'gradient',
                            colors: ['#a6cee3', '#1f78b4', '#b2df8a', '#33a02c']
                        }
                    }
                };

                const chart = new google.visualization.Sankey(chartContainer);
                chart.draw(dataTable, options);
            })
            .catch(error => {
                console.error('Error fetching Sankey data:', error);
                chartContainer.innerHTML = '<div class="flex items-center justify-center h-full"><p class="text-red-500">Не удалось загрузить данные для диаграммы.</p></div>';
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('kb-modal');
        const modalTitle = document.getElementById('kb-modal-title');
        const modalContent = document.getElementById('kb-modal-content');
        const closeModalBtn = document.getElementById('kb-modal-close');
        const triggerButtons = document.querySelectorAll('.kb-trigger');

        triggerButtons.forEach(button => {
            button.addEventListener('click', function () {
                const slug = this.dataset.kbSlug;
                if (!slug) return;

                // Показываем модальное окно с состоянием загрузки
                modalTitle.textContent = 'Загрузка...';
                modalContent.innerHTML = '<p>Пожалуйста, подождите...</p>';
                modal.classList.remove('hidden');
                modal.classList.add('flex');

                // Запрашиваем статью с сервера
                fetch(`{{ route('kb.fetch') }}?slug=${slug}`)
                    .then(response => response.json())
                    .then(data => {
                        modalTitle.textContent = data.title;
                        modalContent.innerHTML = data.content;
                    })
                    .catch(error => {
                        console.error('Error fetching KB article:', error);
                        modalTitle.textContent = 'Ошибка';
                        modalContent.innerHTML = '<p>Не удалось загрузить справочный материал.</p>';
                    });
            });
        });

        // Функция для закрытия модального окна
        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        closeModalBtn.addEventListener('click', closeModal);

        // Закрытие по клику на фон
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        // Закрытие по нажатию на Escape
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
        
        const form = document.getElementById('filters-form');
        const exportLink = document.getElementById('export-link');
        const baseUrl = "{{ route('analytics.multi-channel-sequences.export', $project) }}";

        function updateExportLink() {
            const formData = new FormData(form);
            // Удаляем пустые значения, чтобы ссылка была чище
            for (let [key, value] of [...formData.entries()]) {
                if (value === '') {
                    formData.delete(key);
                }
            }
            const params = new URLSearchParams(formData).toString();
            exportLink.href = `${baseUrl}?${params}`;
        }

        // Обновляем ссылку при загрузке страницы и при изменении любого поля формы
        updateExportLink();
        form.addEventListener('input', updateExportLink);
    });
</script>
@endsection

