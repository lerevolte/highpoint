@extends('analytics.layout')

@section('analytics-title', 'Отчеты')

@section('analytics-content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Группы источников</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        {{-- Колонка с существующими группами --}}
        <div class="md:col-span-2 space-y-6">
            @forelse ($groups as $group)
                <div class="bg-white shadow-md rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-gray-700">{{ $group->name }}</h2>
                        <form action="{{ route('analytics.channel-grouping.destroy', [$project, $group]) }}" method="POST" onsubmit="return confirm('Вы уверены?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 text-sm">Удалить группу</button>
                        </form>
                    </div>

                    {{-- Список правил в группе --}}
                    <ul class="space-y-2 mb-4">
                        @forelse ($group->rules as $rule)
                            <li class="flex justify-between items-center bg-gray-50 p-2 rounded">
                                <span>{{ $rule->source }} / {{ $rule->medium }}</span>
                                <form action="{{ route('analytics.channel-grouping.rules.remove', [$project, $rule]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-500 text-xl">&times;</button>
                                </form>
                            </li>
                        @empty
                            <li class="text-gray-500 text-sm">В этой группе пока нет источников.</li>
                        @endforelse
                    </ul>

                    {{-- Форма добавления нового правила --}}
                    <form action="{{ route('analytics.channel-grouping.rules.add', [$project, $group]) }}" method="POST" class="flex items-center space-x-2">
                        @csrf
                        <select name="source_pair" class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                            <option disabled selected>-- Выберите источник --</option>
                            @foreach ($availableSources as $source)
                                <option value="{{ $source->source }}|{{ $source->medium }}">{{ $source->source }} / {{ $source->medium }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Добавить</button>
                    </form>
                </div>
            @empty
                <div class="bg-white shadow-md rounded-lg p-6 text-center text-gray-500">
                    <p>У вас еще нет ни одной группы источников.</p>
                </div>
            @endforelse
        </div>

        {{-- Колонка для создания новой группы --}}
        <div class="bg-white shadow-md rounded-lg p-6 h-fit">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Создать новую группу</h2>
            <form action="{{ route('analytics.channel-grouping.store', $project) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Название группы</label>
                    <input type="text" name="name" id="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" placeholder="Например, Платный трафик">
                </div>
                <button type="submit" class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Создать</button>
            </form>
        </div>
    </div>
</div>
@endsection