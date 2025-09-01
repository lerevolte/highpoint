@section('sidebar')
    @php
        function is_active_route($routeName) {
            return request()->routeIs($routeName) ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white';
        }
    @endphp
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