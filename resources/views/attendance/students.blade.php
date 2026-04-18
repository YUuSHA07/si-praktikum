<x-app-layout>
    <x-slot name="header_title">Daftar Peserta: {{ $course->course_name }}</x-slot>

    <div class="max-w-[95rem] mx-auto py-0 px-4">
        
        {{-- Alert Notifikasi --}}
        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 text-sm font-bold rounded shadow-sm flex items-center animate-bounce">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm font-bold rounded shadow-sm flex items-center animate-bounce">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- HEADER SECTION --}}
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <a href="{{ route('courses.show', $course->id) }}" 
                   class="inline-flex items-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] hover:text-indigo-600 transition group mb-3">
                    <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali ke Detail Kelas
                </a>
                <h2 class="text-3xl font-black text-gray-800 tracking-tight uppercase leading-none">Daftar Peserta</h2>
                <p class="text-[11px] font-bold text-gray-400 mt-2 uppercase tracking-widest">{{ $course->course_name }} ({{ $course->class_group }})</p>
            </div>
            
            <div class="bg-white px-6 py-4 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="p-3 bg-indigo-50 text-indigo-500 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Mahasiswa</p>
                    <p class="text-2xl font-black text-gray-800 leading-none">{{ $students->count() }} <span class="text-sm text-gray-400">Orang</span></p>
                </div>
            </div>
        </div>

        {{-- TABEL MAHASISWA --}}
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/50 text-[10px] uppercase font-black text-gray-400 border-b border-gray-100">
                        <tr>
                            <th class="px-8 py-6 tracking-widest whitespace-nowrap">No</th>
                            <th class="px-8 py-6 tracking-widest whitespace-nowrap">Mahasiswa</th>
                            <th class="px-8 py-6 tracking-widest whitespace-nowrap">NIM</th>
                            
                            {{-- Akses Aksi Hanya Untuk Dosen/Laboran/Aslab --}}
                            @if(in_array(strtoupper(auth()->user()->role), ['ASLAB', 'LABORAN', 'DOSEN']))
                                <th class="px-8 py-6 tracking-widest text-center uppercase whitespace-nowrap">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($students as $index => $student)
                            <tr class="hover:bg-gray-50/50 transition-all group">
                                <td class="px-8 py-5 whitespace-nowrap text-gray-500 font-bold text-sm">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center overflow-hidden border border-gray-100">
                                            @if($student->avatar)
                                                <img src="{{ asset('storage/' . $student->avatar) }}" class="w-full h-full object-cover">
                                            @else
                                                <span class="text-slate-400 font-black text-[10px] uppercase">{{ strtoupper(substr($student->name, 0, 2)) }}</span>
                                            @endif
                                        </div>
                                        <span class="text-gray-800 font-black text-sm uppercase tracking-tight">{{ $student->name }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap">
                                    <span class="text-indigo-600 font-mono font-bold">{{ $student->id }}</span>
                                </td>
                                
                                @if(in_array(strtoupper(auth()->user()->role), ['ASLAB', 'LABORAN', 'DOSEN']))
                                <td class="px-8 py-5 text-center whitespace-nowrap">
                                    <form action="{{ route('courses.remove-student', [$course->id, $student->id]) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                onclick="return confirm('Apakah Anda yakin ingin MENGELUARKAN {{ $student->name }} dari kelas ini? Semua data terkait mahasiswa ini di kelas ini mungkin terpengaruh.')" 
                                                class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded-xl text-[9px] font-black uppercase tracking-widest transition-all shadow-sm">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6"></path></svg>
                                            Keluarkan
                                        </button>
                                    </form>
                                </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-16 text-center">
                                    <div class="inline-flex p-6 bg-slate-50 rounded-[2rem] text-slate-300 mb-4">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                    </div>
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Belum ada mahasiswa yang bergabung di kelas ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>