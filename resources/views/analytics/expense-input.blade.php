@extends('analytics.layout')

@section('analytics-title', 'Отчеты')

@section('analytics-content')
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Учет расходов</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Форма добавления расхода --}}
            <div class="lg:col-span-1 bg-white shadow-md rounded-lg p-6 h-fit">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Добавить расход</h2>
                <form action="{{ route('analytics.expense-input.store', $project) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700">Дата</label>
                        <input type="date" name="date" id="date" value="{{ date('Y-m-d') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    </div>
                    <div>
                        <label for="source" class="block text-sm font-medium text-gray-700">Источник (utm_source)</label>
                        <input type="text" name="source" id="source" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" placeholder="google">
                    </div>
                    <div>
                        <label for="medium" class="block text-sm font-medium text-gray-700">Канал (utm_medium)</label>
                        <input type="text" name="medium" id="medium" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" placeholder="cpc">
                    </div>
                    <div>
                        <label for="campaign" class="block text-sm font-medium text-gray-700">Кампания (utm_campaign)</label>
                        <input type="text" name="campaign" id="campaign" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" placeholder="summer_sale">
                    </div>
                    <div>
                        <label for="cost" class="block text-sm font-medium text-gray-700">Сумма расхода</label>
                        <input type="number" step="0.01" name="cost" id="cost" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" placeholder="1000.50">
                    </div>
                    <button type="submit" class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Сохранить расход</button>
                </form>
            </div>

            {{-- Таблица с существующими расходами --}}
            <div class="lg:col-span-2 bg-white shadow-md rounded-lg overflow-hidden">
                 <div class="overflow-x-auto">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr class="border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <th class="px-5 py-3">Дата</th>
                                <th class="px-5 py-3">Источник</th>
                                <th class="px-5 py-3">Канал</th>
                                <th class="px-5 py-3">Кампания</th>
                                <th class="px-5 py-3 text-right">Расход</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($costs as $cost)
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-5 py-4 text-sm">{{ $cost->date->format('d.m.Y') }}</td>
                                    <td class="px-5 py-4 text-sm">{{ $cost->source ?? '-' }}</td>
                                    <td class="px-5 py-4 text-sm">{{ $cost->medium ?? '-' }}</td>
                                    <td class="px-5 py-4 text-sm">{{ $cost->campaign ?? '-' }}</td>
                                    <td class="px-5 py-4 text-sm text-right font-semibold">{{ number_format($cost->cost, 2, ',', ' ') }}</td>
                                    <td class="px-5 py-4 text-sm text-center">
                                        <form action="{{ route('analytics.expense-input.destroy', [$project, $cost]) }}" method="POST" onsubmit="return confirm('Вы уверены?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-gray-400 hover:text-red-500">&times;</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-10 text-gray-500">Данные о расходах еще не добавлены.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-4 bg-white border-t">
                    {{ $costs->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection