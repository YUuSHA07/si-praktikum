<x-app-layout>
    <x-slot name="header_title">
        Daftar Kelas Praktikum
    </x-slot>

    {{-- Form Join Khusus Mahasiswa --}}
    @if(auth()->user()->role === 'Mahasiswa')
    <div class="mb-8 bg-indigo-900 rounded-xl p-6 text-white shadow-lg">
        <div class="md:flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold">Gabung Kelas Baru</h3>
                <p class="text-indigo-200 text-sm">Masukkan kode pendaftaran untuk mulai praktikum.</p>
            </div>
            <form action="{{ route('courses.enroll') }}" method="POST" class="mt-4 md:mt-0 flex gap-2">
                @csrf
                <input type="text" name="enrollment_code" 
                    class="rounded-lg border-none text-gray-900 focus:ring-2 focus:ring-indigo-400 font-mono uppercase w-32 md:w-48" 
                    placeholder="CONTOH: ABC123" required>
                <button type="submit" class="bg-indigo-500 hover:bg-indigo-400 px-6 py-2 rounded-lg font-bold transition">
                    Join
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- Header Konten --}}
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Kelas Saya</h1>
            <p class="text-sm text-gray-500">Semester: <span class="text-indigo-600 font-semibold">{{ $activeSemester->name }}</span></p>
        </div>
        
        @if(auth()->user()->role === 'Laboran')
        <a href="{{ route('courses.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition flex items-center shadow-md">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Buat Kelas
        </a>
        @endif
    </div>

    {{-- Grid Kartu Kelas --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($courses as $course)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition group flex flex-col">
            <div class="p-6 flex-1">
                <div class="flex justify-between items-start mb-4">
                    <div class="bg-indigo-50 p-2 rounded-lg group-hover:bg-indigo-100 transition">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <span class="text-[10px] font-black bg-gray-100 text-gray-600 px-2 py-1 rounded border border-gray-200 uppercase tracking-tighter">
                        {{ $course->class_group }}
                    </span>
                </div>

                <h3 class="text-lg font-bold text-gray-800 leading-tight mb-2 truncate" title="{{ $course->course_name }}">
                    {{ $course->course_name }}
                </h3>
                
                <div class="space-y-2 border-t border-gray-50 pt-4 mt-4">
                    <div class="flex items-center text-[11px] text-gray-500">
                        <span class="w-16 font-medium uppercase tracking-tighter">Dosen</span>
                        <span class="truncate font-bold text-gray-700">: {{ $course->dosen->name }}</span>
                    </div>
                    {{-- Menampilkan Nama Laboran --}}
                    <div class="flex items-center text-[11px] text-gray-500">
                        <span class="w-16 font-medium uppercase tracking-tighter">Laboran</span>
                        <span class="truncate font-bold text-gray-700">: {{ $course->laboran->name ?? '-' }}</span>
                    </div>
                    <div class="flex items-center text-[11px] text-gray-500">
                        <span class="w-16 font-medium uppercase tracking-tighter">Aslab</span>
                        <span class="truncate font-bold text-gray-700">: {{ $course->aslab->name }}</span>
                    </div>
                </div>

                {{-- Tampilkan Kode Enrollment HANYA untuk Non-Mahasiswa --}}
                @if(auth()->user()->role !== 'Mahasiswa')
                <div class="mt-6 p-3 bg-indigo-50 rounded-lg border border-indigo-100 text-center">
                    <p class="text-[10px] text-indigo-400 uppercase font-black tracking-widest">Enrollment Code</p>
                    <p class="text-xl font-mono font-black text-indigo-700 select-all">{{ $course->enrollment_code }}</p>
                </div>
                @endif
            </div>

            <div class="px-6 pb-6 mt-auto">
                <a href="{{ route('courses.show', $course->id) }}" class="w-full block text-center bg-gray-50 hover:bg-indigo-600 hover:text-white text-gray-700 font-bold py-2.5 rounded-lg transition border border-gray-100 shadow-sm hover:shadow-md">
                    Buka Kelas
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full py-20 bg-white rounded-2xl border-2 border-dashed border-gray-200 flex flex-col items-center justify-center text-center">
            <div class="bg-gray-50 p-4 rounded-full mb-4">
                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-gray-500 font-medium italic">Belum ada kelas yang terdaftar atau diikuti.</p>
        </div>
        @endforelse
    </div>
</x-app-layout>