@extends('layouts.lk')

@section('title', 'Подрядчики')
{{-- @section('project-name', $project->name) --}} {{-- Раскомментировать, если это страница проекта --}}

@section('content')
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Подрядчики</h1>
        <a href="{{ route('contractors.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg flex items-center shadow-sm">
            <i class="fas fa-plus mr-2"></i> Добавить подрядчика
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Название</th>
                        <th scope="col" class="px-6 py-3">Канал</th>
                        <th scope="col" class="px-6 py-3">Бюджет</th>
                        <th scope="col" class="px-6 py-3 text-right">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contractors as $contractor)
                        <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                {{ $contractor->name }}
                            </td>
                            <td class="px-6 py-4">{{ $contractor->channel }}</td>
                            <td class="px-6 py-4">{{ number_format($contractor->budget, 2, ',', ' ') }} ₽</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end items-center gap-4">
                                    <a href="{{ route('contractors.edit', $contractor) }}" class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline">Редактировать</a>
                                    <form action="{{ route('contractors.destroy', $contractor) }}" method="POST" onsubmit="return confirm('Вы уверены?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-medium text-red-600 dark:text-red-500 hover:underline">Удалить</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                Подрядчики еще не добавлены.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
