<!DOCTYPE html>
<html lang="ru" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Личный кабинет')</title>

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class', // Включаем темную тему
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    @yield('styles')
</head>
<body class="h-full">
<div class="flex h-full">
    @hasSection('sidebar')
        <aside class="w-64 flex-shrink-0 bg-gray-800 text-white flex flex-col">
            <div class="h-16 flex items-center justify-center text-xl font-bold border-b border-gray-700">
                <a href="{{ route('projects.index') }}">Аналитика</a>
            </div>
            <nav class="flex-1 overflow-y-auto">
                @yield('sidebar')
            </nav>
        </aside>
    @endif

    <div class="flex-1 flex flex-col">
        <header class="bg-white shadow-sm h-16 flex items-center justify-between px-6 border-b border-gray-200">
            <div class="flex items-center">
                <i class="fas fa-project-diagram text-gray-500 mr-2"></i>
                <span class="text-lg font-semibold text-gray-700">
                    @hasSection('project-name')
                        @yield('project-name')
                    @else
                        Мои проекты
                    @endif
                </span>
                @php $userProjects = auth()->user()->projects; @endphp
                @if($userProjects->count() > 0)
                    <div x-data="{ open: false }" class="relative ml-4">
                        <button @click="open = !open" class="text-sm text-blue-600 hover:underline">
                            Сменить проект <i class="fas fa-chevron-down fa-xs ml-1"></i>
                        </button>
                        <div x-show="open" @click.away="open = false"
                             class="absolute mt-2 w-64 bg-white rounded-md shadow-lg z-10"
                             x-transition>
                            @foreach ($userProjects as $project)
                                <a href="{{ route('projects.show', $project) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ $project->name }}</a>
                            @endforeach
                            <div class="border-t border-gray-200"></div>
                            <a href="{{ route('projects.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-list mr-2"></i>Все проекты</a>
                            <a href="{{ route('projects.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-plus mr-2"></i>Создать новый</a>
                        </div>
                    </div>
                @endif
            </div>

            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center space-x-2">
                    <span class="text-gray-700">{{ Auth::user()->name }}</span>
                    <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}" alt="">
                </button>
                <div x-show="open" @click.away="open = false"
                     class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10"
                     x-transition>
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Профиль</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Выход</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="flex-1 p-6 overflow-y-auto">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
             @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>


@yield('scripts')
</body>
</html>