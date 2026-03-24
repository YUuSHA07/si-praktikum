@extends('layouts.app')

@section('header_title', 'Daftar Kelas Praktikum')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <p class="text-sm text-gray-600">Semester Aktif: <span class="font-bold text-indigo-600">{{ $activeSemester->name }}</span></p>
    <a href="{{ route('courses.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
        + Buat Kelas Baru
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($courses as $course)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition">
        <div class="p-5">
            <div class="flex justify-between items-start">
                <h3 class="text-lg font-bold text-gray-800">{{ $course->course_name }}</h3>
                <span class="bg-indigo-100 text-indigo-700 text-[10px] px-2 py-1 rounded-full font-bold uppercase">{{ $course->class_group }}</span>
            </div>
            <p class="text-xs text-gray-500 mt-1">Semester {{ $course->target_semester }}</p>
            
            <div class="mt-4 space-y-2">
                <div class="flex items-center text-sm text-gray-600">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    {{ $course->dosen->name }}
                </div>
                <div class="flex items-center text-sm text-gray-600">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    Aslab: {{ $course->aslab->name }}
                </div>
            </div>

            <div class="mt-5 p-3 bg-gray-50 rounded-lg border border-dashed border-gray-300 text-center">
                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Kode Enrollment</p>
                <p class="text-2xl font-mono font-black text-indigo-600 tracking-widest">{{ $course->enrollment_code }}</p>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full bg-white p-10 rounded-xl border border-dashed border-gray-300 text-center">
        <p class="text-gray-500 italic">Belum ada kelas yang terdaftar.</p>
    </div>
    @endforelse
</div>
@endsection