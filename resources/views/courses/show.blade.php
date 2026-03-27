<x-app-layout>
    <x-slot name="header_title">
        Detail Praktikum: {{ $course->course_name }}
    </x-slot>

    {{-- Alert Notifikasi --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 text-sm font-bold rounded shadow-sm flex items-center animate-bounce">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Tombol Kembali --}}
    <div class="mb-6">
        <a href="{{ route('courses.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-black flex items-center transition group">
            <svg class="w-4 h-4 mr-1 transform group-hover:-translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            KEMBALI KE DASHBOARD
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- SIDEBAR: Informasi Praktikum --}}
        <div class="space-y-6">
            <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100">
                <h3 class="font-black text-gray-800 text-[10px] uppercase tracking-[0.2em] mb-8 border-b pb-4">Informasi Kelas</h3>
                
                <div class="space-y-6">
                    <div class="flex flex-col">
                        <span class="text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1.5">Dosen Pengampu</span>
                        <span class="font-bold text-gray-900 text-sm">{{ $course->dosen->name }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1.5">Laboran</span>
                        <span class="font-bold text-gray-900 text-sm">{{ $course->laboran->name ?? '-' }}</span>
                    </div>
                    <div class="flex flex-col pb-4">
                        <span class="text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1.5">Asisten Laboratorium</span>
                        <span class="font-bold text-indigo-600 text-sm">{{ $course->aslab->name }}</span>
                    </div>
                </div>

                @if(auth()->user()->role !== 'Mahasiswa')
                <div class="mt-8 pt-8 border-t border-gray-50 space-y-3">
                    <a href="{{ route('attendance.report', $course->id) }}" class="w-full flex items-center justify-center gap-2 px-4 py-4 bg-indigo-50 text-indigo-700 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-100 transition border border-indigo-100 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Rekap Presensi
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- MAIN CONTENT: Materi & Pertemuan --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/30">
                    <h3 class="font-black text-gray-800 text-[10px] uppercase tracking-[0.2em]">Materi & Jadwal Pertemuan</h3>
                    
                    {{-- Tombol Tambah Pertemuan (Aslab/Laboran) --}}
                    @if(in_array(auth()->user()->role, ['Aslab', 'Laboran']))
                        <button onclick="openMeetingModal()" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition shadow-xl shadow-indigo-100 active:scale-95">
                            Tambah Pertemuan
                        </button>
                    @endif
                </div>

                <div class="divide-y divide-gray-50">
                    @forelse($course->meetings->sortBy('meeting_number') as $meeting)
                        @php 
                            $sub = $meeting->submissions->where('student_id', auth()->id())->first(); 
                        @endphp
                        
                        <div class="p-8 hover:bg-gray-50/50 transition group">
                            <div class="flex flex-col md:flex-row justify-between gap-6">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-3">
                                        <span class="px-3 py-1 bg-slate-800 text-white text-[9px] font-black rounded-lg uppercase tracking-[0.1em]">
                                            P{{ $meeting->meeting_number }}
                                        </span>
                                        @if(auth()->user()->role === 'Mahasiswa')
                                            @php $attendance = $meeting->attendances->where('student_id', auth()->id())->first(); @endphp
                                            <span class="text-[9px] font-black uppercase tracking-widest {{ $attendance ? 'text-emerald-500' : 'text-gray-300' }}">
                                                {{ $attendance ? '● Hadir' : '○ Belum Presensi' }}
                                            </span>
                                        @endif
                                    </div>
                                    <h4 class="font-black text-gray-800 text-xl leading-tight group-hover:text-indigo-600 transition">{{ $meeting->title }}</h4>
                                    
                                    @if(auth()->user()->role === 'Mahasiswa' && $sub)
                                    <div class="mt-5 space-y-3">
                                        {{-- Baris Status Aslab --}}
                                        <div class="flex items-center gap-3">
                                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-tighter w-20">Asisten:</span>
                                            <span class="px-2 py-0.5 rounded-md text-[9px] font-black {{ $sub->aslab_status == 'ACC' ? 'bg-emerald-50 text-emerald-600' : ($sub->aslab_status == 'REVISI' ? 'bg-red-50 text-red-600 animate-pulse' : 'bg-amber-50 text-amber-600') }}">
                                                {{ $sub->aslab_status }}
                                            </span>
                                            @if($sub->aslab_acc_at && $sub->aslab_status == 'ACC')
                                                <span class="text-[8px] font-bold text-gray-400 uppercase italic">
                                                    ACC pd: {{ \Carbon\Carbon::parse($sub->aslab_acc_at)->translatedFormat('d M Y, H:i') }}
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Baris Status Laboran --}}
                                        <div class="flex items-center gap-3">
                                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-tighter w-20">Laboran:</span>
                                            <span class="px-2 py-0.5 rounded-md text-[9px] font-black {{ $sub->laboran_status == 'ACC' ? 'bg-emerald-50 text-emerald-600' : ($sub->laboran_status == 'REVISI' ? 'bg-red-50 text-red-600 animate-pulse' : 'bg-amber-50 text-amber-600') }}">
                                                {{ $sub->laboran_status }}
                                            </span>
                                            @if($sub->laboran_acc_at && $sub->laboran_status == 'ACC')
                                                <span class="text-[8px] font-bold text-gray-400 uppercase italic">
                                                    ACC pd: {{ \Carbon\Carbon::parse($sub->laboran_acc_at)->translatedFormat('d M Y, H:i') }}
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Badge Final --}}
                                        @if($sub->is_completed)
                                            <div class="pt-2">
                                                <span class="bg-indigo-600 text-white px-3 py-1 rounded-full text-[8px] font-black uppercase tracking-widest shadow-lg shadow-indigo-100 flex items-center w-fit gap-1">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                                    Verified Final
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    @endif
                                </div>

                                {{-- Action Buttons --}}
                                <div class="flex flex-wrap items-center gap-3">
                                    {{-- Panel Aslab/Laboran --}}
                                    @if(in_array(auth()->user()->role, ['Aslab', 'Laboran']))
                                        <a href="{{ route('attendance.index', $meeting->id) }}" class="px-5 py-3 bg-amber-50 text-amber-700 rounded-2xl text-[10px] font-black uppercase tracking-widest border border-amber-100 hover:bg-amber-100 transition shadow-sm">
                                            Presensi
                                        </a>
                                        <a href="{{ route('submissions.index', $meeting->id) }}" class="px-5 py-3 bg-slate-800 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-900 transition flex items-center shadow-lg shadow-slate-200">
                                            Tugas ({{ $meeting->submissions->count() }})
                                        </a>
                                    @endif

                                    {{-- Panel Mahasiswa --}}
                                    @if(auth()->user()->role === 'Mahasiswa')
                                        <a href="{{ route('mahasiswa.submissions.manage', $meeting->id) }}" 
                                           class="px-5 py-3 {{ $sub ? ($sub->aslab_status == 'REVISI' ? 'bg-red-600 hover:bg-red-700 shadow-red-100' : 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-100') : 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-100' }} text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl transition active:scale-95">
                                            {{ $sub ? 'Kelola Tugas' : 'Kumpul Tugas' }}
                                        </a>
                                    @endif
                                    
                                    {{-- Link Modul --}}
                                    @if($meeting->module_drive_link)
                                        <a href="{{ $meeting->module_drive_link }}" target="_blank" class="p-3 bg-gray-50 text-gray-400 rounded-2xl border border-gray-100 hover:text-indigo-600 hover:bg-white transition" title="Download Modul">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-24 text-center">
                            <div class="inline-flex p-6 bg-slate-50 rounded-[2rem] text-slate-300 mb-4">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            </div>
                            <p class="text-gray-400 font-black text-[10px] uppercase tracking-[0.2em]">Belum Ada Data Pertemuan</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH PERTEMUAN (KHUSUS ASLAB/LABORAN) --}}
    @if(in_array(auth()->user()->role, ['Aslab', 'Laboran']))
    <div id="modalMeeting" class="fixed inset-0 bg-slate-900/60 backdrop-blur-md hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md overflow-hidden animate-in zoom-in duration-300">
            <form action="{{ route('meetings.store', $course->id) }}" method="POST">
                @csrf
                <div class="p-10 border-b border-gray-50 bg-gray-50/50">
                    <h3 class="text-2xl font-black text-gray-800 leading-tight tracking-tight">Tambah Pertemuan</h3>
                </div>
                <div class="p-10 space-y-6">
                    <div>
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2.5">Pertemuan Ke-</label>
                        <input type="number" name="meeting_number" required min="1" value="{{ $course->meetings->count() + 1 }}" class="block w-full rounded-2xl border-gray-100 text-sm font-black focus:ring-4 focus:ring-indigo-50 p-4 bg-gray-50 transition">
                    </div>
                    <div>
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2.5">Judul Materi</label>
                        <input type="text" name="title" required placeholder="Contoh: Dasar Dasar PHP" class="block w-full rounded-2xl border-gray-100 text-sm focus:ring-4 focus:ring-indigo-50 p-4 bg-gray-50 transition">
                    </div>
                    <div>
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2.5">Link Modul (G-Drive)</label>
                        <input type="url" name="module_drive_link" placeholder="https://..." class="block w-full rounded-2xl border-gray-100 text-sm focus:ring-4 focus:ring-indigo-50 p-4 bg-gray-50 transition text-indigo-600">
                    </div>
                </div>
                <div class="p-10 pt-0 bg-white flex flex-col gap-4">
                    <button type="submit" class="w-full bg-indigo-600 text-white py-5 rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition active:scale-95">Simpan Materi</button>
                    <button type="button" onclick="closeMeetingModal()" class="w-full text-[9px] font-black text-gray-400 uppercase tracking-widest hover:text-red-500 transition">Batalkan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openMeetingModal() {
            document.getElementById('modalMeeting').classList.replace('hidden', 'flex');
            document.body.style.overflow = 'hidden';
        }
        function closeMeetingModal() {
            document.getElementById('modalMeeting').classList.replace('flex', 'hidden');
            document.body.style.overflow = 'auto';
        }
    </script>
    @endif
</x-app-layout>