@extends('analytics.layout')

@section('title', 'Воронка продаж')
@section('project-name', $project->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Воронка продаж</h1>

    <div class="max-w-4xl mx-auto">
        @if (empty($funnelData) || $funnelData[0]['count'] === 0)
            <div class="bg-white shadow-md rounded-lg p-8 text-center text-gray-500">
                <p>Нет данных для построения воронки.</p>
                <p class="text-sm mt-2">Убедитесь, что ваш пиксель отслеживает события: page_view, add_to_cart, begin_checkout, purchase.</p>
            </div>
        @else
            <div class="relative space-y-1">
                @php
                    $maxUsers = $funnelData[0]['count'] > 0 ? $funnelData[0]['count'] : 1;
                @endphp

                @foreach ($funnelData as $index => $step)
                    @php
                        // Рассчитываем ширину блока в процентах
                        $widthPercentage = round(($step['count'] / $maxUsers) * 100);
                    @endphp

                    {{-- Блок для каждого шага воронки --}}
                    <div class="relative h-20 flex items-center justify-center transition-all duration-500 ease-in-out mx-auto"
                         style="width: {{ $widthPercentage }}%; min-width: 20%;">
                        
                        {{-- Цветной фон-трапеция --}}
                        <div class="absolute inset-0 bg-indigo-500 opacity-{{ 80 - ($index * 15) }} clip-path-trapezoid"></div>
                        
                        {{-- Текст внутри блока --}}
                        <div class="relative z-10 text-white text-center p-2">
                            <div class="font-bold text-lg">{{ $step['name'] }}</div>
                            <div class="text-2xl font-light">{{ number_format($step['count'], 0, ',', ' ') }}</div>
                        </div>
                    </div>

                    {{-- Блок с информацией о конверсии (между шагами) --}}
                    @if ($index < count($funnelData) - 1)
                        <div class="relative h-16 flex flex-col items-center justify-center text-gray-600">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                            <div class="text-sm">
                                Конверсия: <span class="font-bold text-green-600">{{ $step['conversion_from_previous'] }}%</span>
                            </div>
                            @php
                                $droppedUsers = $step['count'] - $funnelData[$index + 1]['count'];
                            @endphp
                            <div class="text-xs text-red-500" title="Пользователи, не перешедшие на следующий шаг">
                                -{{ number_format($droppedUsers, 0, ',', ' ') }}
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Легенда с итоговой конверсией --}}
            <div class="mt-8 text-center text-gray-700">
                <p>
                    Общая конверсия из первого в последний шаг:
                    <span class="font-bold text-xl text-indigo-700">
                        {{ end($funnelData)['conversion_from_start'] }}%
                    </span>
                </p>
            </div>
        @endif
    </div>
</div>

<style>
    .clip-path-trapezoid {
        clip-path: polygon(10% 0, 90% 0, 100% 100%, 0% 100%);
    }
</style>
@endsection
