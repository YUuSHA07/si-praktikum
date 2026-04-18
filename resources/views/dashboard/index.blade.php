{{-- Kita asumsikan Anda menggunakan layout bawaan Laravel Breeze atau layout buatan sendiri --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard ') . auth()->user()->active_role }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- LOGIKA PEMANGGILAN PARTIAL VIEW --}}
            @if(auth()->user()->active_role === 'Dosen')
                @include('dashboard._dosen')
            @elseif(auth()->user()->active_role === 'Mahasiswa')
                @include('dashboard._mahasiswa')
            @elseif(auth()->user()->active_role === 'Aslab')
                @include('dashboard._aslab')
            @elseif(auth()->user()->active_role === 'Laboran')
                @include('dashboard._laboran')
            @endif

        </div>
    </div>
</x-app-layout>