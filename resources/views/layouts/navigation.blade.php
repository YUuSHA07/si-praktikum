{{-- 
    Inisialisasi Navigasi dengan Alpine.js
    x-data="{ open: false }": Mengatur status menu mobile (terbuka atau tertutup).
    shadow-sm: Memberikan bayangan tipis agar navbar terlihat terpisah dari konten.
--}}
<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 shadow-sm">
    
    {{-- Container Utama: Membatasi lebar konten navigasi agar rapi di tengah --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            
            {{-- BAGIAN KIRI: Logo dan Menu Link --}}
            <div class="flex">
                {{-- Logo Aplikasi --}}
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                {{-- Navigasi Desktop: hidden sm:flex berarti hanya tampil di layar komputer (sm ke atas) --}}
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </div>
            </div>

            {{-- BAGIAN KANAN: Dropdown Profil (Hanya tampil di Desktop) --}}
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    {{-- Pemicu Dropdown (Tombol Nama + Foto) --}}
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            
                            {{-- LOGIKA TAMPILAN FOTO PROFIL (AVATAR) --}}
                            <div class="me-2">
                                @if(Auth::user()->avatar)
                                    {{-- Jika user sudah mengunggah foto, ambil dari storage --}}
                                    {{-- h-11 w-11: Ukuran sekitar 44px. object-cover: Gambar dipotong pas di lingkaran (tidak gepeng) --}}
                                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" 
                                         alt="{{ Auth::user()->name }}" 
                                         class="h-11 w-11 rounded-full object-cover border border-gray-200">
                                @else
                                    {{-- Jika tidak ada foto, tampilkan lingkaran abu-abu dengan icon orang --}}
                                    <div class="h-11 w-11 rounded-full bg-gray-200 flex items-center justify-center text-gray-400 border border-gray-100">
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            {{-- Nama User --}}
                            <div>{{ Auth::user()->name }}</div>

                            {{-- Icon Panah Bawah --}}
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    {{-- Konten Dropdown --}}
                    <x-slot name="content">
                        {{-- Link ke Edit Profile --}}
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        {{-- Form Logout: Menggunakan metode POST demi keamanan data --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            {{-- TOMBOL HAMBURGER (Hanya muncul di Mobile) --}}
            <div class="-me-2 flex items-center sm:hidden">
                {{-- @click: Merubah status 'open' menjadi kebalikannya saat ditekan --}}
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        {{-- Icon Menu Garis 3: Tampil jika 'open' adalah false --}}
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        {{-- Icon Silang (Tutup): Tampil jika 'open' adalah true --}}
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- MENU MOBILE: Muncul ketika tombol hamburger ditekan --}}
    {{-- :class: Menentukan tampilan berdasarkan status 'open' --}}
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden shadow-inner bg-gray-50">
        {{-- Link Dashboard Versi Mobile --}}
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        {{-- INFO AKUN (Mobile): Bagian bawah menu mobile --}}
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4 flex items-center">
                
                {{-- AVATAR MOBILE --}}
                <div class="shrink-0 me-3">
                    @if(Auth::user()->avatar)
                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" 
                             alt="{{ Auth::user()->name }}" 
                             class="h-10 w-10 rounded-full object-cover border-2 border-white shadow-sm">
                    @else
                        {{-- Placeholder Default Mobile --}}
                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-400">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    @endif
                </div>

                {{-- Teks Info (Nama & Email) --}}
                <div>
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            {{-- Link Pengaturan dan Logout (Mobile) --}}
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>