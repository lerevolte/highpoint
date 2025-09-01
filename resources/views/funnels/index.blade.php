@extends('layouts.lk')
@section('title', 'Воронка продаж')
@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Воронка продаж</h1>
        <a href="{{ route('funnels.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg flex items-center shadow-sm">
            <i class="fas fa-plus mr-2"></i> Добавить этап
        </a>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th class="px-6 py-3">Название этапа</th>
                    <th class="px-6 py-3">Позиция</th>
                    <th class="px-6 py-3 text-right">Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stages as $stage)
                <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $stage->title }}</td>
                    <td class="px-6 py-4">{{ $stage->position }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end items-center gap-4">
                            <a href="{{ route('funnels.edit', $stage) }}" class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline">Редактировать</a>
                            <form action="{{ route('funnels.destroy', $stage) }}" method="POST" onsubmit="return confirm('Удалить этап?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="font-medium text-red-600 dark:text-red-500 hover:underline">Удалить</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="px-6 py-10 text-center">Этапы еще не добавлены.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection