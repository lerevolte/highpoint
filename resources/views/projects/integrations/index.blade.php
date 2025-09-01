@extends('layouts.lk')
@section('title', 'Интеграции - ' . $project->name)
@section('project-name', $project->name)
@section('sidebar')
    @include('projects.partials.sidebar', ['project' => $project, 'active' => 'integrations'])
@endsection
@section('content')
    <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-6">Интеграции</h1>
    <div class="max-w-3xl bg-white dark:bg-gray-800 rounded-lg shadow-md">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
             <h2 class="text-xl font-semibold">Доступные интеграции</h2>
        </div>
        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
            {{-- Яндекс.Метрика --}}
            <li class="p-6 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Яндекс.Метрика</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Анализ посещаемости сайта</p>
                </div>
                <div>
                    @php $yandexIntegration = $integrations->firstWhere('type', 'yandex_metrika'); @endphp
                    @if($yandexIntegration)
                        <div class="flex items-center gap-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i> Подключено
                            </span>
                            <a href="{{ route('projects.integrations.yandex-metrika.edit', [$project, $yandexIntegration]) }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">Настроить</a>
                        </div>
                    @else
                        <a href="{{ route('projects.integrations.yandex-metrika.connect', $project) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-sm">Подключить</a>
                    @endif
                </div>
            </li>
            
            <!-- Интеграция с Битрикс24 -->
            <li class="p-6 flex justify-between items-center">
                 <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <img src="https://www.bitrix24.ru/images/share/24_logo.png" alt="Битрикс24 Лого" class="w-6 h-6 mr-2">
                        Битрикс24
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Сбор данных о лидах и сделках из CRM</p>
                </div>
                <div>
                    @php $bitrix24Integration = $integrations->firstWhere('type', 'bitrix24'); @endphp
                    @if($bitrix24Integration)
                        <div class="flex items-center gap-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i> Подключено
                            </span>
                            <a href="{{ route('projects.integrations.bitrix24.edit', [$project, $bitrix24Integration]) }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">Настроить</a>
                        </div>
                    @else
                        {{-- Кнопка "Подключить" открывает модальное окно --}}
                        <button x-data @click="$dispatch('open-modal', 'bitrix24-modal')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-sm">Подключить</button>
                    @endif
                </div>
            </li>
            
        </ul>
    </div>

    <!-- Модальное окно для подключения Битрикс24 -->
    <div x-data="{ show: false }"
         x-show="show"
         @open-modal.window="if ($event.detail === 'bitrix24-modal') show = true"
         @keydown.escape.window="show = false"
         style="display: none;"
         class="fixed z-10 inset-0 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show" @click="show = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="show" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('projects.integrations.bitrix24.store', $project) }}" method="POST">
                    @csrf
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <img src="https://www.bitrix24.ru/images/share/24_logo.png" alt="Битрикс24 Лого" class="w-6 h-6">
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                    Подключение Битрикс24
                                </h3>
                                <div class="mt-4">
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                        Для интеграции укажите URL входящего вебхука из вашего портала Битрикс24.
                                    </p>
                                    <label for="webhook_url_modal" class="block text-sm font-medium text-gray-700 dark:text-gray-300">URL входящего вебхука</label>
                                    <input type="url" name="webhook_url" id="webhook_url_modal"
                                           placeholder="https://your-portal.bitrix24.ru/rest/1/..."
                                           class="mt-1 shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-300 leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Сохранить
                        </button>
                        <button type="button" @click="show = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-500 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Отмена
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

