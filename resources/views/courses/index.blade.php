<x-app-layout>
    <x-slot name="header_title">
        Daftar Kelas Praktikum
    </x-slot>

    <div class="max-w-[95rem] mx-auto py-8 px-4">
        
        {{-- Form Join Khusus Mahasiswa --}}
        @if(strtoupper(auth()->user()->role) === 'MAHASISWA')
        <div class="mb-8 bg-indigo-900 rounded-[2rem] p-8 text-white shadow-xl shadow-indigo-100 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-20 -mt-20 blur-3xl pointer-events-none"></div>
            <div class="md:flex items-center justify-between relative z-10">
                <div>
                    <h3 class="text-2xl font-black uppercase tracking-tight mb-1">Gabung Kelas Baru</h3>
                    <p class="text-indigo-200 text-[11px] font-bold uppercase tracking-widest">Masukkan kode pendaftaran untuk mulai praktikum.</p>
                </div>
                <form action="{{ route('courses.enroll') }}" method="POST" class="mt-6 md:mt-0 flex gap-3">
                    @csrf
                    <input type="text" name="enrollment_code" 
                        class="rounded-xl border-none bg-white/10 text-white placeholder-indigo-300 focus:ring-2 focus:ring-indigo-400 font-mono uppercase font-black tracking-widest w-full md:w-56 px-5 py-3 backdrop-blur-md" 
                        placeholder="KODE KELAS..." required>
                    <button type="submit" class="bg-indigo-500 hover:bg-indigo-400 text-white px-8 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition shadow-lg active:scale-95">
                        Gabung
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- JIKA TIDAK ADA SEMESTER AKTIF --}}
        @if(!$activeSemester)
        <div class="bg-red-50 rounded-[2.5rem] shadow-sm border border-red-100 p-16 text-center">
            <div class="inline-flex p-6 bg-red-100 rounded-[2rem] text-red-400 mb-4">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h3 class="text-xl font-black text-red-800 uppercase tracking-tight mb-2">Sistem Sedang Ditangguhkan</h3>
            <p class="text-[11px] font-bold text-red-400 uppercase tracking-widest">Tidak ada semester akademik yang sedang aktif saat ini. Harap hubungi administrator.</p>
        </div>
        @else

        {{-- Header Konten --}}
        <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
            <div>
                <h1 class="text-4xl font-black text-gray-800 tracking-tight uppercase leading-none mb-2">Daftar Praktikum</h1>
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 bg-indigo-50 text-indigo-600 text-[9px] font-black rounded-lg uppercase tracking-widest border border-indigo-100">Semester Berjalan</span>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">{{ $activeSemester->name }}</p>
                </div>
            </div>
            
            @if(strtoupper(auth()->user()->role) === 'LABORAN')
            <div class="flex flex-col sm:flex-row items-center gap-4 w-full md:w-auto">
                
                {{-- TOGGLE SWITCH FILTER KELAS (KHUSUS LABORAN) --}}
                @php
                    $currentView = request()->query('view', 'my_classes');
                @endphp
                <div class="flex p-1.5 bg-gray-100/80 rounded-2xl border border-gray-200">
                    <a href="{{ route('courses.index', ['view' => 'my_classes']) }}" 
                       class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $currentView === 'my_classes' ? 'bg-white text-indigo-600 shadow-sm border border-gray-100' : 'text-gray-400 hover:text-gray-600' }}">
                        Kelas Saya
                    </a>
                    <a href="{{ route('courses.index', ['view' => 'all']) }}" 
                       class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $currentView === 'all' ? 'bg-white text-indigo-600 shadow-sm border border-gray-100' : 'text-gray-400 hover:text-gray-600' }}">
                        Semua Kelas
                    </a>
                </div>

                {{-- Tombol Buat Kelas --}}
                <a href="{{ route('courses.create') }}" class="w-full sm:w-auto bg-slate-900 hover:bg-slate-800 text-white px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest transition shadow-lg active:scale-95 flex items-center justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Buat Kelas
                </a>
            </div>
            @endif
        </div>

        {{-- Grid Kartu Kelas --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
            @forelse($courses as $course)
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:border-gray-200 transition-all duration-300 group flex flex-col relative h-full">
                
                {{-- Decorative Blob --}}
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-indigo-50 rounded-full blur-3xl opacity-50 group-hover:opacity-100 transition-opacity"></div>

                <div class="p-8 flex-1 relative z-10">
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-500 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300 border border-indigo-100">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <span class="text-[10px] font-black bg-gray-50 text-gray-500 px-3 py-1.5 rounded-xl border border-gray-100 uppercase tracking-widest shadow-sm">
                            {{ $course->class_group }}
                        </span>
                    </div>

                    <h3 class="text-2xl font-black text-gray-800 leading-tight mb-2 truncate uppercase" title="{{ $course->course_name }}">
                        {{ $course->course_name }}
                    </h3>
                    
                    <div class="space-y-3 border-t border-gray-50 pt-6">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400 text-[8px] font-black border border-gray-100">DS</div>
                            <div class="flex-1 flex justify-between items-center text-[10px] font-bold">
                                <span class="text-gray-400 uppercase tracking-widest">Dosen</span>
                                <span class="text-gray-700 uppercase truncate max-w-[150px] text-right">{{ $course->dosen->name }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400 text-[8px] font-black border border-gray-100">LB</div>
                            <div class="flex-1 flex justify-between items-center text-[10px] font-bold">
                                <span class="text-gray-400 uppercase tracking-widest">Laboran</span>
                                <span class="text-gray-700 uppercase truncate max-w-[150px] text-right">{{ $course->laboran->name ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400 text-[8px] font-black border border-gray-100">AL</div>
                            <div class="flex-1 flex justify-between items-center text-[10px] font-bold">
                                <span class="text-gray-400 uppercase tracking-widest">Aslab</span>
                                <span class="text-gray-700 uppercase truncate max-w-[150px] text-right">{{ $course->aslab->name }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Tampilkan Kode Enrollment HANYA untuk Non-Mahasiswa --}}
                    @if(strtoupper(auth()->user()->role) !== 'MAHASISWA')
                    <div class="mt-8 p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100/50 text-center relative group/code overflow-hidden">
                        <div class="absolute inset-0 bg-indigo-100/0 group-hover/code:bg-indigo-100/50 transition"></div>
                        <p class="text-[8px] text-indigo-400 uppercase font-black tracking-widest relative z-10 mb-1">Enrollment Code</p>
                        <p class="text-xl font-mono font-black text-indigo-600 select-all relative z-10 tracking-widest">{{ $course->enrollment_code }}</p>
                    </div>
                    @endif
                </div>

                <div class="px-8 pb-8 pt-4 mt-auto relative z-10">
                    <a href="{{ route('courses.show', $course->id) }}" class="w-full block text-center bg-gray-50 hover:bg-slate-900 text-gray-500 hover:text-white text-[11px] font-black py-4 rounded-2xl uppercase tracking-widest transition-all border border-gray-100 shadow-sm hover:shadow-xl active:scale-95">
                        Buka Kelas
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-full py-24 bg-white rounded-[3rem] border border-gray-100 flex flex-col items-center justify-center text-center shadow-sm">
                <div class="bg-gray-50 p-6 rounded-[2rem] mb-6">
                    <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-black text-gray-700 uppercase tracking-tight mb-2">Belum Ada Kelas</h3>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest max-w-md">Tidak ada kelas praktikum yang terdaftar atau diikuti pada semester aktif ini.</p>
            </div>
            @endforelse
        </div>
        @endif
    </div>
</x-app-layout>