@extends('layouts.lk')
@section('title', 'Настройка Яндекс.Метрики')
@section('project-name', $project->name)
@section('sidebar')
    @include('projects.partials.sidebar', ['project' => $project, 'active' => 'integrations'])
@endsection
@section('content')
    <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-6">Настройка Яндекс.Метрики</h1>
    <div class="max-w-2xl bg-white dark:bg-gray-800 p-8 rounded-lg shadow-md">
        <form method="POST" action="{{ route('projects.integrations.yandex-metrika.update', [$project, $integration]) }}">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <label class="block text-lg font-medium text-gray-700 dark:text-gray-300">Выберите счетчики для подключения</label>
                
                <div class="space-y-3">
                    @forelse($counters as $counter)
                        <label class="flex items-center p-4 border dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <input type="checkbox" name="counters[]" value="{{ $counter['id'] }}"
                                   @if(in_array($counter['id'], $selectedCounters)) checked @endif
                                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-3 text-sm">
                                <span class="font-medium text-gray-900 dark:text-white">{{ $counter['name'] }}</span>
                                <span class="text-gray-500 dark:text-gray-400">({{ $counter['site'] }})</span>
                            </span>
                        </label>
                    @empty
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700 p-4 rounded-md" role="alert">
                            <p>Не удалось получить список счетчиков или у вас нет доступа ни к одному счетчику.</p>
                        </div>
                    @endforelse
                </div>
            </div>
            <div class="flex items-center gap-4 pt-6 mt-6 border-t dark:border-gray-700">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm">Сохранить настройки</button>
                <a href="{{ route('projects.integrations.index', $project) }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:underline">Отмена</a>
            </div>
        </form>
    </div>
@endsection