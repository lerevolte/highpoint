@extends('layouts.lk')

@section('title', 'Редактировать подрядчика')

@section('content')
    <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-6">Редактировать подрядчика</h1>

    <div class="max-w-2xl bg-white dark:bg-gray-800 p-8 rounded-lg shadow-md">
        <form method="POST" action="{{ route('contractors.update', $contractor) }}" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Название</label>
                <input type="text" name="name" id="name" value="{{ old('name', $contractor->name) }}" required 
                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
            </div>
            
            <div>
                <label for="channel" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Канал</label>
                <input type="text" name="channel" id="channel" value="{{ old('channel', $contractor->channel) }}" required
                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label for="budget" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Бюджет, ₽</label>
                <input type="number" name="budget" id="budget" step="0.01" value="{{ old('budget', $contractor->budget) }}" required
                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm">
                    Обновить
                </button>
                <a href="{{ route('contractors.index') }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:underline">Отмена</a>
            </div>
        </form>
    </div>
@endsection
