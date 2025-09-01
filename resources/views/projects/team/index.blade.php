@extends('layouts.lk')

@section('title', 'Управление командой - ' . $project->name)
@section('project-name', $project->name)

@php
    // Helper-функция для определения активной ссылки
    function is_active_route($routeName) {
        return request()->routeIs($routeName) ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white';
    }
@endphp

@section('sidebar')
    {{-- Общий сайдбар для проекта --}}
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
        {{-- ... другие ссылки ... --}}
        <a href="{{ route('projects.edit', $project) }}" class="flex items-center px-4 py-2.5 rounded-md text-sm font-medium {{ is_active_route('projects.edit') }}">
            <i class="fas fa-cog fa-fw w-6 text-center"></i><span class="ml-3">Настройки проекта</span>
        </a>
    </div>
@endsection


@section('content')
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Команда проекта</h1>
        <button type="button" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg flex items-center shadow-sm" data-bs-toggle="modal" data-bs-target="#inviteUserModal">
            <i class="fas fa-user-plus mr-2"></i> Пригласить участника
        </button>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md">
        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($team as $member)
                <li x-data="{ open: false }" class="p-4 sm:p-6">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-4">
                             <img class="h-12 w-12 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode($member->name) }}&background=random" alt="">
                            <div>
                                <p class="text-lg font-medium text-gray-900 dark:text-white">{{ $member->name }}
                                    @if($member->pivot->is_admin)
                                        <span class="ml-2 bg-indigo-100 text-indigo-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">Администратор</span>
                                    @endif
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $member->email }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                             @if(!$member->pivot->is_admin)
                                <form action="{{ route('projects.team.remove', [$project, $member]) }}" method="POST" onsubmit="return confirm('Вы уверены, что хотите удалить участника?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-600" title="Удалить">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                <button @click="open = !open" class="text-gray-500 dark:text-gray-400" title="Настроить права">
                                    <i class="fas fa-chevron-down transition-transform" :class="open && 'rotate-180'"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    @if(!$member->pivot->is_admin)
                        <div x-show="open" x-collapse x-cloak class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <form action="{{ route('projects.team.update-permissions', [$project, $member]) }}" method="POST">
                                @csrf
                                <h4 class="text-md font-semibold mb-3 text-gray-700 dark:text-gray-300">Права доступа</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                                     @foreach($permissions as $group => $groupPermissions)
                                        <div class="space-y-3">
                                            <h6 class="font-semibold text-gray-800 dark:text-gray-200">{{ $group }}</h6>
                                            @foreach($groupPermissions as $permission)
                                                <label class="flex items-center">
                                                    <input type="checkbox" name="permissions[]" value="{{ $permission->slug }}" 
                                                        {{ in_array($permission->slug, json_decode($member->pivot->permissions ?? '[]', true)) ? 'checked' : '' }}
                                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 dark:checked:bg-indigo-600">
                                                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ $permission->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-6">
                                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-sm shadow-sm">
                                        <i class="fas fa-save mr-2"></i> Сохранить права
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
    {{-- Модальное окно для приглашения нужно будет тоже переделать на Tailwind/Alpine, если вы не хотите использовать Bootstrap JS --}}
@endsection
