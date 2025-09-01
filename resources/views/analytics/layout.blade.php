{{-- analytics/layout.blade.php --}}
@extends('layouts.lk')

@section('title', 'Аналитика - ' . $project->name)
@section('project-name', $project->name)

@section('sidebar')
    <div class="p-2 space-y-1 flex flex-col h-full">
        <div>
            {{-- Группа: Отчеты --}}
            <h3 class="px-4 pt-4 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Отчеты</h3>
            <div class="space-y-1">
                <a href="{{ route('analytics.multi-channel-sequences', $project) }}" class="flex items-center px-4 py-2.5 rounded-md text-sm font-medium {{ request()->routeIs('analytics.multi-channel-sequences') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                    <i class="fas fa-random fa-fw w-6 text-center"></i><span class="ml-3">Мультиканальные пути</span>
                </a>
                 <a href="{{ route('analytics.romi-report', $project) }}" class="flex items-center px-4 py-2.5 rounded-md text-sm font-medium {{ request()->routeIs('analytics.romi-report') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                    <i class="fas fa-calculator fa-fw w-6 text-center"></i><span class="ml-3">Отчет по ROMI</span>
                </a>
                <a href="{{ route('analytics.sales-funnel', $project) }}" class="flex items-center px-4 py-2.5 rounded-md text-sm font-medium {{ request()->routeIs('analytics.sales-funnel') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                    <i class="fas fa-filter fa-fw w-6 text-center"></i><span class="ml-3">Воронка продаж</span>
                </a>
            </div>

            {{-- Группа: Настройки --}}
            <h3 class="px-4 pt-6 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Настройки</h3>
            <div class="space-y-1">
                <a href="{{ route('analytics.expense-input.index', $project) }}" class="flex items-center px-4 py-2.5 rounded-md text-sm font-medium {{ request()->routeIs('analytics.expense-input.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                    <i class="fas fa-wallet fa-fw w-6 text-center"></i><span class="ml-3">Учет расходов</span>
                </a>
                <a href="{{ route('analytics.channel-grouping.index', $project) }}" class="flex items-center px-4 py-2.5 rounded-md text-sm font-medium {{ request()->routeIs('analytics.channel-grouping.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                    <i class="fas fa-sitemap fa-fw w-6 text-center"></i><span class="ml-3">Группировка каналов</span>
                </a>
            </div>
        </div>

        {{-- Ссылка для возврата к управлению проектом --}}
        <div class="p-2 mt-auto">
            <a href="{{ route('projects.show', $project) }}" class="flex items-center px-4 py-2.5 rounded-md text-sm font-medium text-gray-400 hover:bg-gray-700 hover:text-white">
                <i class="fas fa-arrow-left fa-fw w-6 text-center"></i><span class="ml-3">К управлению проектом</span>
            </a>
        </div>
    </div>
@endsection

@section('content')
    {{-- Контент конкретной страницы аналитики будет вставлен сюда --}}
    @yield('analytics-content')
@endsection
