@extends('layouts.lk')

@section('title', 'Редактирование проекта')
@section('project-name', $project->name)

@section('sidebar')
    <div class="p-2">
        <a href="{{ route('projects.show', $project) }}" class="flex items-center px-4 py-2.5 rounded-md text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">
            <i class="fas fa-arrow-left fa-fw w-6 text-center"></i>
            <span class="ml-3">Назад к проекту</span>
        </a>
    </div>
@endsection

@section('content')
    <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-6">Редактирование проекта</h1>

    <div class="max-w-2xl bg-white dark:bg-gray-800 p-8 rounded-lg shadow-md">
        <form method="POST" action="{{ route('projects.update', $project) }}" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Название проекта</label>
                <input type="text" name="name" id="name" value="{{ old('name', $project->name) }}" required 
                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="domain" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Домен проекта</label>
                <input type="text" name="domain" id="domain" value="{{ old('domain', $project->domain) }}" required
                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white @error('domain') border-red-500 @enderror">
                @error('domain')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Валюта</label>
                <select id="currency" name="currency" required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white @error('currency') border-red-500 @enderror">
                    <option value="RUB" {{ old('currency', $project->currency) == 'RUB' ? 'selected' : '' }}>RUB - Российский рубль</option>
                    <option value="USD" {{ old('currency', $project->currency) == 'USD' ? 'selected' : '' }}>USD - Доллар США</option>
                    <option value="EUR" {{ old('currency', $project->currency) == 'EUR' ? 'selected' : '' }}>EUR - Евро</option>
                </select>
                 @error('currency')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm">
                    Сохранить изменения
                </button>
                <a href="{{ route('projects.show', $project) }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:underline">Отмена</a>
            </div>
        </form>
    </div>
@endsection
