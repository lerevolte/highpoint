@extends('layouts.lk')

@section('title', 'Мои проекты')

@section('sidebar')
    <a href="{{ route('projects.index') }}" class="flex items-center px-4 py-3 text-gray-300 bg-gray-700">
        <i class="fas fa-project-diagram w-6 text-center"></i>
        <span class="ml-3">Мои проекты</span>
    </a>
    <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700">
        <i class="fas fa-user-cog w-6 text-center"></i>
        <span class="ml-3">Мой профиль</span>
    </a>
@endsection

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Мои проекты</h1>
        <a href="{{ route('projects.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center">
            <i class="fas fa-plus mr-2"></i> Создать проект
        </a>
    </div>

    @if($projects->isEmpty())
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4" role="alert">
            <p>У вас пока нет проектов. Создайте свой первый проект.</p>
        </div>
    @else
        @if(!$adminProjects->isEmpty())
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Администрируемые проекты</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($adminProjects as $project)
                    <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 flex flex-col">
                        <div class="p-6 flex-grow">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $project->name }}</h3>
                            <p class="text-sm text-gray-500 mt-2"><i class="fas fa-globe mr-2"></i>{{ $project->domain }}</p>
                            <p class="text-sm text-gray-500"><i class="fas fa-money-bill-wave mr-2"></i>{{ $project->currency }}</p>
                            <span class="mt-2 inline-block bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">Администратор</span>
                        </div>
                        <div class="bg-gray-50 px-6 py-4 flex justify-between rounded-b-lg">
                            <a href="{{ route('projects.show', $project) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">Управление</a>
                            <a href="{{ route('projects.edit', $project) }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">Настройки</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
        
        <h2 class="text-xl font-semibold text-gray-700 mt-8 mb-4">Проекты, где я участник</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
             @foreach($projects as $project)
                @unless($project->pivot->is_admin)
                     <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 flex flex-col">
                        <div class="p-6 flex-grow">
                             <h3 class="text-lg font-semibold text-gray-900">{{ $project->name }}</h3>
                             <p class="text-sm text-gray-500 mt-2"><i class="fas fa-globe mr-2"></i>{{ $project->domain }}</p>
                             <p class="text-sm text-gray-500"><i class="fas fa-money-bill-wave mr-2"></i>{{ $project->currency }}</p>
                        </div>
                        <div class="bg-gray-50 px-6 py-4 flex justify-start rounded-b-lg">
                             <a href="{{ route('projects.show', $project) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">Перейти к проекту <i class="fas fa-arrow-right ml-1"></i></a>
                        </div>
                    </div>
                @endunless
             @endforeach
        </div>
    @endif
@endsection