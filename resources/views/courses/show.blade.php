<x-app-layout>
    <x-slot name="header_title">
        Detail Kelas: {{ $course->course_name }}
    </x-slot>

    <div class="mb-6">
        <a href="{{ route('courses.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-semibold flex items-center transition">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Kembali ke Daftar Kelas
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Sidebar Informasi --}}
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                <h3 class="font-bold text-gray-800 text-lg mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Info Praktikum
                </h3>
                
                <div class="space-y-4 text-sm">
                    <div class="flex flex-col pb-2 border-b border-gray-50">
                        <span class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Dosen</span>
                        <span class="font-bold text-gray-900">{{ $course->dosen->name }}</span>
                    </div>
                    <div class="flex flex-col pb-2 border-b border-gray-50">
                        <span class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Laboran</span>
                        <span class="font-bold text-gray-900">{{ $course->laboran->name ?? '-' }}</span>
                    </div>
                    <div class="flex flex-col pb-2 border-b border-gray-50">
                        <span class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Asisten</span>
                        <span class="font-bold text-gray-900">{{ $course->aslab->name }}</span>
                    </div>
                </div>

                {{-- Link Rekap Presensi Khusus Non-Mahasiswa --}}
                @if(Auth::user()->role !== 'Mahasiswa')
                <div class="mt-6 pt-4 border-t border-gray-100">
                    <a href="{{ route('attendance.report', $course->id) }}" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-bold hover:bg-indigo-100 transition border border-indigo-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Rekapitulasi Presensi
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- Daftar Pertemuan --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="font-bold text-gray-800 text-lg">Materi & Pertemuan</h3>
                    @if(in_array(Auth::user()->role, ['Aslab', 'Laboran']))
                        <button onclick="toggleModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-indigo-700 transition flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Tambah Pertemuan
                        </button>
                    @endif
                </div>

                <div class="p-6 space-y-4">
                    @forelse($course->meetings->sortBy('meeting_number') as $meeting)
                        <div class="p-5 border border-gray-100 rounded-xl hover:shadow-md transition bg-white flex flex-col md:flex-row justify-between md:items-center gap-4 border-l-4 border-l-indigo-500">
                            <div class="flex-1">
                                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 text-[10px] font-black rounded uppercase tracking-wider">
                                    Pertemuan {{ $meeting->meeting_number }}
                                </span>
                                <h4 class="font-bold text-gray-800 text-lg leading-tight mt-1">{{ $meeting->title }}</h4>
                                
                                {{-- Menampilkan Status Presensi jika user adalah Mahasiswa --}}
                                @if(Auth::user()->role === 'Mahasiswa')
                                    @php $attendance = $meeting->attendances->first(); @endphp
                                    <div class="mt-2">
                                        @if($attendance)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase 
                                                {{ $attendance->status == 'Hadir' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                                {{ $attendance->status == 'Sakit' ? 'bg-blue-100 text-blue-700' : '' }}
                                                {{ $attendance->status == 'Izin' ? 'bg-amber-100 text-amber-700' : '' }}
                                                {{ $attendance->status == 'Alpha' ? 'bg-red-100 text-red-700' : '' }}">
                                                Status: {{ $attendance->status }}
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded text-[10px] font-bold uppercase">
                                                Belum Diabsen
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                {{-- Tombol Absensi untuk Aslab --}}
                                @if(in_array(Auth::user()->role, ['Aslab', 'Laboran']))
                                    <a href="{{ route('attendance.index', $meeting->id) }}" class="px-4 py-2 bg-amber-50 text-amber-700 rounded-lg text-xs font-bold border border-amber-200 hover:bg-amber-100 transition">
                                        Absensi
                                    </a>
                                @endif
                                
                                @if($meeting->module_drive_link)
                                    <a href="{{ $meeting->module_drive_link }}" target="_blank" class="px-4 py-2 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-bold border border-emerald-200 hover:bg-emerald-100 transition">
                                        Modul
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-gray-400 italic py-10">Belum ada materi praktikum.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>