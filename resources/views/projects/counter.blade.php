@extends('layouts.lk')

@section('title', 'Код счетчика - ' . $project->name)
@section('project-name', $project->name)

@section('sidebar')
    @php
        function is_active_project_route($active, $target) {
            return $active === $target ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white';
        }
    @endphp
    <div class="p-2 space-y-1 flex flex-col h-full">
        <div>
            <a href="{{ route('projects.show', $project) }}" class="flex items-center px-4 py-2.5 rounded-md text-sm font-medium {{ is_active_project_route($active ?? '', 'dashboard') }}">
                <i class="fas fa-tachometer-alt fa-fw w-6 text-center"></i><span class="ml-3">Панель управления</span>
            </a>
            <a href="{{ route('projects.team.index', $project) }}" class="flex items-center px-4 py-2.5 rounded-md text-sm font-medium {{ is_active_project_route($active ?? '', 'team') }}">
                <i class="fas fa-users fa-fw w-6 text-center"></i><span class="ml-3">Команда проекта</span>
            </a>
            <a href="{{ route('projects.counter', $project) }}" class="flex items-center px-4 py-2.5 rounded-md text-sm font-medium {{ is_active_project_route($active ?? '', 'counter') }}">
                <i class="fas fa-code fa-fw w-6 text-center"></i><span class="ml-3">Код счетчика</span>
            </a>
            <a href="{{ route('projects.integrations.index', $project) }}" class="flex items-center px-4 py-2.5 rounded-md text-sm font-medium {{ is_active_project_route($active ?? '', 'integrations') }}">
                <i class="fas fa-plug fa-fw w-6 text-center"></i><span class="ml-3">Интеграции</span>
            </a>
            {{-- Другие ссылки на разделы проекта --}}
        </div>
        <div class="p-2 mt-auto">
             <a href="{{ route('projects.edit', $project) }}" class="flex items-center px-4 py-2.5 rounded-md text-sm font-medium {{ is_active_project_route($active ?? '', 'settings') }}">
                <i class="fas fa-cog fa-fw w-6 text-center"></i><span class="ml-3">Настройки проекта</span>
            </a>
        </div>
    </div>
@endsection

@section('content')
    <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-6">Код счётчика</h1>

    <div class="max-w-3xl bg-white dark:bg-gray-800 p-8 rounded-lg shadow-md">
        <div class="bg-blue-50 dark:bg-gray-700/50 border-l-4 border-blue-500 text-blue-800 dark:text-blue-300 p-4 rounded-md mb-6" role="alert">
            <h5 class="font-bold"><i class="fas fa-info-circle mr-2"></i>Инструкция по установке</h5>
            <p class="mt-1">Скопируйте и установите этот код на все страницы вашего сайта <strong>{{ $project->domain }}</strong> перед закрывающим тегом <code>&lt;/body&gt;</code>.</p>
        </div>

        <div x-data="{
            copied: false,
            copyToClipboard() {
                const code = this.$refs.code.innerText;
                navigator.clipboard.writeText(code).then(() => {
                    this.copied = true;
                    setTimeout(() => { this.copied = false }, 2000);
                });
            }
        }">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Код счётчика для домена: <code>{{ $project->domain }}</code></label>
            <div class="relative">
                <pre class="bg-gray-900 text-white text-sm p-4 rounded-lg overflow-x-auto"><code x-ref="code">{{ $project->counter_code }}</code></pre>
                <button @click="copyToClipboard" class="absolute top-2 right-2 bg-gray-700 hover:bg-gray-600 text-white text-xs font-semibold py-1 px-3 rounded-md">
                    <span x-show="!copied"><i class="fas fa-copy mr-1"></i> Копировать</span>
                    <span x-show="copied" x-cloak><i class="fas fa-check mr-1"></i> Скопировано!</span>
                </button>
            </div>
        </div>

        <div class="mt-8 border-t dark:border-gray-700 pt-6">
             <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Проверка установки</h2>
             <div id="tracker-test-result">
                {{-- Результат проверки будет загружен сюда через JS --}}
             </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
function testTracker() {
    var resultDiv = document.getElementById('tracker-test-result');
    resultDiv.innerHTML = '<div class="alert alert-info">Тестирование работы счетчика...</div>';
    
    // Проверяем загрузку tracker.js
    if (typeof _sat === 'undefined') {
        resultDiv.innerHTML = '<div class="alert alert-danger">Ошибка: счетчик не загрузился</div>';
        return;
    }
    
    // Проверяем отправку данных
    _sat.trackEvent('test_event', {test: true});
    
    setTimeout(function() {
        fetch('/api/check-visit?project_id={{ $project->id }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.innerHTML = '<div class="alert alert-success">Счетчик работает корректно! Последний визит: ' + new Date(data.visit.created_at).toLocaleString() + '</div>';
                } else {
                    resultDiv.innerHTML = '<div class="alert alert-warning">Данные не получены. Проверьте настройки API.</div>';
                }
            });
    }, 1000);
}

// Запускаем проверку при загрузке страницы
window.addEventListener('load', testTracker);
function copyCode() {
    const code = document.getElementById('counter-code').textContent;
    navigator.clipboard.writeText(code).then(() => {
        const btn = document.querySelector('[data-bs-toggle="tooltip"]');
        const tooltip = bootstrap.Tooltip.getInstance(btn);
        tooltip.setContent({'.tooltip-inner': 'Скопировано!'});
        setTimeout(() => tooltip.hide(), 1000);
    });
}

function checkCounter() {
    const url = document.getElementById('check-url').value.trim();
    const resultDiv = document.getElementById('check-result');
    
    if (!url) {
        alert('Пожалуйста, введите URL страницы для проверки');
        return;
    }
    
    resultDiv.innerHTML = `
        <div class="alert alert-info">
            <div class="d-flex align-items-center">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <span>Проверяем установку счётчика...</span>
            </div>
        </div>
    `;
    resultDiv.classList.remove('d-none');
    
    // Здесь можно добавить реальную проверку через API
    setTimeout(() => {
        resultDiv.innerHTML = `
            <div class="alert alert-success">
                <h6><i class="fas fa-check-circle me-2"></i>Как проверить вручную:</h6>
                <ol>
                    <li>Откройте сайт в браузере</li>
                    <li>Нажмите F12 → вкладка "Сеть" (Network)</li>
                    <li>Обновите страницу (F5)</li>
                    <li>Найдите запросы к <code>your-analytics-domain.com</code></li>
                    <li>Убедитесь, что нет ошибок в консоли</li>
                </ol>
                <p class="mb-0">Если счётчик установлен правильно, вы увидите запросы к нашему серверу аналитики.</p>
            </div>
        `;
    }, 2000);
}
</script>
@endsection