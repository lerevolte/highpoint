@extends('layouts.lk')

@section('title', $project->name)
@section('project-name', $project->name)

@php
    // Helper-функция для определения активной ссылки
    function is_active_route($routeName) {
        return request()->routeIs($routeName) ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white';
    }
@endphp

@section('sidebar')
    <div class="p-2 space-y-1">
        <a href="{{ route('projects.show', $project) }}" class="flex items-center px-4 py-2.5 rounded-md text-sm font-medium {{ is_active_route('projects.show') }}">
            <i class="fas fa-tachometer-alt fa-fw w-6 text-center"></i><span class="ml-3">Панель управления</span>
        </a>
        <a href="{{ route('projects.team.index', $project) }}" class="flex items-center px-4 py-2.5 rounded-md text-sm font-medium {{ is_active_route('projects.team.index') }}">
            <i class="fas fa-users fa-fw w-6 text-center"></i><span class="ml-3">Команда проекта</span>
        </a>
        <a href="{{ route('projects.counter', $project) }}" class="flex items-center px-4 py-2.5 rounded-md text-sm font-medium {{ is_active_route('projects.counter') }}">
            <i class="fas fa-code fa-fw w-6 text-center"></i><span class="ml-3">Код счетчика</span>
        </a>
        <a href="{{-- route('analytics.dashboard', $project) --}}" class="flex items-center px-4 py-2.5 rounded-md text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">
            <i class="fas fa-chart-line fa-fw w-6 text-center"></i><span class="ml-3">Аналитика</span>
        </a>
        {{-- ... другие ссылки ... --}}
        <div class="pt-2 mt-2 border-t border-gray-700">
            <a href="{{ route('projects.edit', $project) }}" class="flex items-center px-4 py-2.5 rounded-md text-sm font-medium {{ is_active_route('projects.edit') }}">
                <i class="fas fa-cog fa-fw w-6 text-center"></i><span class="ml-3">Настройки проекта</span>
            </a>
        </div>
    </div>
@endsection

@section('content')
    <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-6">Панель управления</h1>

    <div class="max-w-2xl bg-white dark:bg-gray-800 rounded-lg shadow-md">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
             <h2 class="text-xl font-semibold">Основная информация</h2>
        </div>
        <div class="p-6">
            <ul class="space-y-4">
                <li class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400"><i class="fas fa-globe fa-fw mr-2"></i> Домен</span>
                    <span class="font-medium">{{ $project->domain }}</span>
                </li>
                <li class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400"><i class="fas fa-money-bill-wave fa-fw mr-2"></i> Валюта</span>
                    <span class="font-medium">{{ $project->currency }}</span>
                </li>
                <li class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400"><i class="fas fa-calendar-alt fa-fw mr-2"></i> Создан</span>
                    <span class="font-medium">{{ $project->created_at->format('d.m.Y') }}</span>
                </li>
            </ul>
        </div>
    </div>
@endsection
