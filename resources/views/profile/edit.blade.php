@extends('layouts.lk')

@section('title', 'Мой профиль')

@section('sidebar')
    <div class="p-2 space-y-1">
        <a href="{{ route('projects.index') }}" class="flex items-center px-4 py-2.5 rounded-md text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">
            <i class="fas fa-project-diagram fa-fw w-6 text-center"></i>
            <span class="ml-3">Мои проекты</span>
        </a>
        <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2.5 rounded-md text-sm font-medium bg-gray-700 text-white">
            <i class="fas fa-user-cog fa-fw w-6 text-center"></i>
            <span class="ml-3">Мой профиль</span>
        </a>
    </div>
@endsection

@section('content')
    <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-6">Настройки профиля</h1>
    
    <div class="space-y-6">
        {{-- Update Profile Information --}}
        <div class="max-w-2xl bg-white dark:bg-gray-800 p-8 rounded-lg shadow-md">
            @include('profile.partials.update-profile-information-form')
        </div>

        {{-- Update Password --}}
        <div class="max-w-2xl bg-white dark:bg-gray-800 p-8 rounded-lg shadow-md">
            @include('profile.partials.update-password-form')
        </div>

        {{-- Delete User --}}
        <div class="max-w-2xl bg-white dark:bg-gray-800 p-8 rounded-lg shadow-md">
             @include('profile.partials.delete-user-form')
        </div>
    </div>
@endsection
