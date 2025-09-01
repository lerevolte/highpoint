@extends('layouts.lk')
@section('title', 'Настройка Битрикс24')
@section('project-name', $project->name)
@section('sidebar')
    @include('projects.partials.sidebar', ['project' => $project, 'active' => 'integrations'])
@endsection
@section('content')
    <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-6">Настройка Битрикс24</h1>
    <div class="max-w-2xl bg-white dark:bg-gray-800 p-8 rounded-lg shadow-md">
        <form method="POST" action="{{ route('projects.integrations.bitrix24.update', [$project, $integration]) }}">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label for="webhook_url" class="block text-lg font-medium text-gray-700 dark:text-gray-300">URL входящего вебхука</label>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mb-2">Это основной URL для получения данных из вашего портала Битрикс24.</p>
                    <input type="url" name="webhook_url" id="webhook_url"
                           value="{{ old('webhook_url', $integration->settings['webhook_url'] ?? '') }}"
                           placeholder="https://your-portal.bitrix24.ru/rest/1/..."
                           class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-300 leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md @error('webhook_url') border-red-500 @enderror">
                    @error('webhook_url')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center gap-4 pt-6 mt-6 border-t dark:border-gray-700">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm">Сохранить настройки</button>
                <a href="{{ route('projects.integrations.index', $project) }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:underline">Отмена</a>
            </div>
        </form>
    </div>
@endsection
