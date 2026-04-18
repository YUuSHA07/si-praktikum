<x-app-layout>
    <x-slot name="header_title">
        Detail Praktikum: {{ $course->course_name }}
    </x-slot>

    {{-- Alert & Navigasi Atas --}}
    @include('courses.partials.header')

    {{-- Banner Laprak Final --}}
    @include('courses.partials.final-task')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Sidebar Informasi Kelas --}}
        @include('courses.partials.sidebar')

        {{-- Daftar Materi & Pertemuan --}}
        @include('courses.partials.meeting-list')
    </div>

    {{-- Modal & Script --}}
    @include('courses.partials.modals')
</x-app-layout>

{{-- script lainnya yang berisi kode dari halaman ini berada pada folder partials --}}
