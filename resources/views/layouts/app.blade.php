<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SI-PRAKTIKUM') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
               class="fixed inset-y-0 left-0 z-50 w-64 bg-indigo-900 text-white transition-transform duration-300 transform lg:translate-x-0 lg:static lg:inset-0 shadow-xl flex flex-col">
            
            <div class="flex items-center justify-center h-16 border-b border-indigo-800 flex-shrink-0">
                <span class="text-xl font-bold tracking-tighter uppercase">SI-<span class="text-indigo-400">Praktikum</span></span>
            </div>

            <nav class="flex-1 mt-4 px-4 space-y-1 overflow-y-auto custom-scrollbar">
                <x-nav-link-sidebar :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Dashboard
                </x-nav-link-sidebar>

                <div class="my-4 border-t border-indigo-800/50"></div>

                @if(auth()->user()->role === 'Laboran')
                    <p class="px-4 text-[10px] font-bold text-indigo-400 uppercase tracking-widest mb-2">Laboran</p>
                    <x-nav-link-sidebar :href="route('users.index')" :active="request()->routeIs('users.index')">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Manajemen User
                    </x-nav-link-sidebar>
                    <x-nav-link-sidebar :href="route('user.import.form')" :active="request()->routeIs('user.import.form')">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        Import User
                    </x-nav-link-sidebar>
                    <x-nav-link-sidebar :href="route('courses.index')" :active="request()->routeIs('courses.index')">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Daftar Kelas
                    </x-nav-link-sidebar>
                    <x-nav-link-sidebar :href="route('courses.create')" :active="request()->routeIs('courses.create')">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        Buat Kelas
                    </x-nav-link-sidebar>
                @elseif(auth()->user()->role === 'Dosen')
                    <p class="px-4 text-[10px] font-bold text-indigo-400 uppercase tracking-widest mb-2">Dosen</p>
                    <x-nav-link-sidebar :href="'#'">Validasi Nilai</x-nav-link-sidebar>
                @elseif(auth()->user()->role === 'Aslab')
                    <p class="px-4 text-[10px] font-bold text-indigo-400 uppercase tracking-widest mb-2">Aslab</p>
                    <x-nav-link-sidebar :href="route('courses.index')" :active="request()->routeIs('courses.index')">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Daftar Kelas
                    </x-nav-link-sidebar>
                @elseif(auth()->user()->role === 'Mahasiswa')
                    <p class="px-4 text-[10px] font-bold text-indigo-400 uppercase tracking-widest mb-2">Mahasiswa</p>
                    <x-nav-link-sidebar :href="route('courses.index')" :active="request()->routeIs('courses.index')">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Daftar Kelas
                    </x-nav-link-sidebar>
                @endif
            </nav>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            
            <header class="flex items-center justify-between px-6 py-3 bg-white border-b border-gray-200 shadow-sm z-10">
                <div class="flex items-center min-w-0 flex-1 mr-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 lg:hidden p-2 hover:bg-gray-100 rounded-md">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <h2 class="ml-4 lg:ml-0 font-semibold text-gray-700 truncate hidden md:block">
                        @yield('header_title', 'Sistem Informasi')
                    </h2>
                </div>

                <div class="relative flex-shrink-0" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center space-x-3 focus:outline-none group p-1.5 hover:bg-gray-50 rounded-full transition max-w-[200px] sm:max-w-[300px]">
                        
                        <div class="text-right hidden sm:block min-w-0 flex-1">
                            <p class="text-sm font-bold text-gray-800 group-hover:text-indigo-600 transition truncate" title="{{ Auth::user()->name }}">
                                {{ Auth::user()->name }}
                            </p>
                            <p class="text-[10px] text-gray-500 font-medium italic uppercase tracking-tighter">{{ Auth::user()->role }}</p>
                        </div>
                        
                        <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-white shadow-sm ring-1 ring-gray-200 flex-shrink-0">
                            @if(Auth::user()->avatar)
                                <img src="{{ asset('storage/' . Auth::user()->avatar) }}" 
                                     alt="{{ Auth::user()->name }}" 
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold uppercase text-sm">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                    </button>

                    <div x-show="open" @click.away="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50">
                        
                        <div class="px-4 py-2 border-b border-gray-50 mb-1 sm:hidden">
                            <p class="text-xs font-bold text-gray-800 break-words">{{ Auth::user()->name }}</p>
                            <p class="text-[10px] text-gray-400">{{ Auth::user()->role }}</p>
                        </div>

                        <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Edit Profil
                        </a>

                        <hr class="my-1 border-gray-50">

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center w-full text-left px-4 py-2 text-sm text-red-600 font-bold hover:bg-red-50 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50/50 p-6 md:p-10">
                <div class="max-w-7xl mx-auto">
                    {{ $slot }}
                </div>
            </main>
        </div>

        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition:opacity
             class="fixed inset-0 z-40 bg-gray-900/60 backdrop-blur-sm lg:hidden">
        </div>
    </div>
</body>
</html>