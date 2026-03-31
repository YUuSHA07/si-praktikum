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

    {{-- Header & Navigasi --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <a href="{{ route('courses.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-black flex items-center transition group">
            <svg class="w-4 h-4 mr-1 transform group-hover:-translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            KEMBALI KE DASHBOARD
        </a>

        @if(in_array(strtoupper(Auth::user()->role), ['ASLAB', 'LABORAN', 'DOSEN']))
        <div class="flex gap-3">
            @if(!$course->finalTask)
            <button onclick="document.getElementById('modal-final-task').classList.replace('hidden', 'flex')" class="px-4 py-2 bg-indigo-600 text-white text-[10px] font-black rounded-xl uppercase tracking-widest hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                + Buat Laprak Final
            </button>
            @endif
            <button onclick="openMeetingModal()" class="px-4 py-2 bg-slate-900 text-white text-[10px] font-black rounded-xl uppercase tracking-widest hover:bg-slate-800 transition">
                + Tambah Pertemuan
            </button>
        </div>
        @endif
    </div>

    {{-- SECTION: HERO BANNER LAPRAK FINAL (Highlight Utama) --}}
    @if($course->finalTask)
    <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 rounded-[2rem] shadow-xl shadow-indigo-100 p-8 mb-8 relative overflow-hidden text-white">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-20 -mt-20 blur-3xl"></div>
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative z-10">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-3">
                    <span class="px-3 py-1 bg-white/20 backdrop-blur-md text-white text-[9px] font-black rounded-lg uppercase tracking-widest border border-white/30">TUGAS PUNCAK</span>
                    <h3 class="text-2xl font-black uppercase tracking-tight">Laporan Praktikum Final</h3>
                </div>
                <p class="text-indigo-100 text-sm font-medium max-w-2xl leading-relaxed">{{ $course->finalTask->description }}</p>
                
                @if($course->finalTask->deadline)
                    <div class="mt-4 flex items-center gap-2 text-[10px] font-black uppercase tracking-widest {{ now()->gt($course->finalTask->deadline) ? 'text-red-300 font-extrabold' : 'text-indigo-200' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Batas Waktu: {{ \Carbon\Carbon::parse($course->finalTask->deadline)->format('d M Y, H:i') }} 
                        {{ now()->gt($course->finalTask->deadline) ? '(PENGUMPULAN DITUTUP)' : '' }}
                    </div>
                @endif
            </div>

            <div class="w-full md:w-auto">
                @if(strtoupper(Auth::user()->role) === 'MAHASISWA')
                    <a href="{{ route('mahasiswa.final-tasks.manage', $course->finalTask->id) }}" class="block w-full md:w-auto px-8 py-4 bg-white text-indigo-600 text-[11px] font-black rounded-2xl uppercase tracking-widest hover:bg-indigo-50 transition shadow-lg text-center active:scale-95">
                        Kelola Laprak Final
                    </a>
                @else
                    <a href="{{ route('final-tasks.index', $course->finalTask->id) }}" class="block w-full md:w-auto px-8 py-4 bg-slate-900 text-white text-[11px] font-black rounded-2xl uppercase tracking-widest hover:bg-slate-800 transition shadow-lg text-center border border-white/10 active:scale-95">
                        Review Pengumpulan
                    </a>
                @endif
            </div>
        </div>

        {{-- STATUS FINAL TASK DI HERO BANNER --}}
        @if(strtoupper(auth()->user()->role) === 'MAHASISWA')
            @php 
                $finalSub = \App\Models\Submission::where('final_task_id', $course->finalTask->id)
                                ->where('student_id', auth()->id())
                                ->where('is_final', true)
                                ->first(); 
                $isPastDeadline = $course->finalTask->deadline && now()->gt(\Carbon\Carbon::parse($course->finalTask->deadline));
                $isCompleted = $finalSub && strtoupper($finalSub->aslab_status) == 'ACC' && strtoupper($finalSub->laboran_status) == 'ACC' && strtoupper($finalSub->dosen_status) == 'ACC';
            @endphp
            
            <div class="mt-6 border-t border-white/10 pt-5 space-y-2.5 relative z-10">
                @if($finalSub)
                    {{-- Aslab Status --}}
                    <div class="flex items-center gap-3 text-[9px] font-black uppercase tracking-tighter">
                        <span class="text-indigo-300 w-24 text-[8px]">Status Aslab:</span>
                        <span class="px-2 py-0.5 rounded-md {{ $finalSub->aslab_status == 'ACC' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : ($finalSub->aslab_status == 'REVISI' ? 'bg-red-500/20 text-red-300 border border-red-500/30 animate-pulse' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30') }}">
                            {{ $finalSub->aslab_status }}
                        </span>
                        @if($finalSub->aslab_status == 'ACC' && $finalSub->aslab_acc_at)
                            <span class="text-indigo-300 text-[8px] border-l border-indigo-400/50 pl-3">{{ \Carbon\Carbon::parse($finalSub->aslab_acc_at)->format('d M Y') }}</span>
                        @endif
                    </div>
                    {{-- Laboran Status --}}
                    <div class="flex items-center gap-3 text-[9px] font-black uppercase tracking-tighter">
                        <span class="text-indigo-300 w-24 text-[8px]">Status Laboran:</span>
                        <span class="px-2 py-0.5 rounded-md {{ $finalSub->laboran_status == 'ACC' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : ($finalSub->laboran_status == 'REVISI' ? 'bg-red-500/20 text-red-300 border border-red-500/30 animate-pulse' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30') }}">
                            {{ $finalSub->laboran_status }}
                        </span>
                        @if($finalSub->laboran_status == 'ACC' && $finalSub->laboran_acc_at)
                            <span class="text-indigo-300 text-[8px] border-l border-indigo-400/50 pl-3">{{ \Carbon\Carbon::parse($finalSub->laboran_acc_at)->format('d M Y') }}</span>
                        @endif
                    </div>
                    {{-- Dosen Status --}}
                    <div class="flex items-center gap-3 text-[9px] font-black uppercase tracking-tighter">
                        <span class="text-indigo-300 w-24 text-[8px]">Status Dosen:</span>
                        <span class="px-2 py-0.5 rounded-md {{ $finalSub->dosen_status == 'ACC' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : ($finalSub->dosen_status == 'REVISI' ? 'bg-red-500/20 text-red-300 border border-red-500/30 animate-pulse' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30') }}">
                            {{ $finalSub->dosen_status }}
                        </span>
                        @if($finalSub->dosen_status == 'ACC' && $finalSub->dosen_acc_at)
                            <span class="text-indigo-300 text-[8px] border-l border-indigo-400/50 pl-3">{{ \Carbon\Carbon::parse($finalSub->dosen_acc_at)->format('d M Y') }}</span>
                        @endif
                    </div>

                    @if($isPastDeadline && !$isCompleted)
                        <div class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 bg-red-500/20 text-red-200 text-[9px] font-black rounded-lg uppercase tracking-widest border border-red-500/30 backdrop-blur-sm">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Lewat Deadline & Belum ACC Sepenuhnya
                        </div>
                    @endif
                @else
                    @if($isPastDeadline)
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-500/20 text-red-200 text-[9px] font-black rounded-lg uppercase tracking-widest border border-red-500/30 backdrop-blur-sm">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Waktu Habis (Belum Mengumpulkan)
                        </div>
                    @else
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-500/20 text-amber-200 text-[9px] font-black rounded-lg uppercase tracking-widest border border-amber-500/30 backdrop-blur-sm">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Belum Mengumpulkan Laporan Final
                        </div>
                    @endif
                @endif
            </div>
        @endif
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- SIDEBAR: Informasi Praktikum --}}
        <div class="space-y-6">
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
                <h3 class="font-black text-gray-800 text-[10px] uppercase tracking-[0.2em] mb-8 border-b pb-4">Informasi Kelas</h3>
                
                <div class="space-y-8">
                    {{-- DOSEN --}}
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-slate-100 flex-shrink-0 flex items-center justify-center overflow-hidden shadow-inner border border-gray-100">
                            @if($course->dosen->avatar)
                                <img src="{{ asset('storage/' . $course->dosen->avatar) }}" alt="{{ $course->dosen->name }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-slate-400 font-black text-xs uppercase">{{ strtoupper(substr($course->dosen->name, 0, 2)) }}</span>
                            @endif
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">Dosen Pengampu</span>
                            <span class="font-black text-gray-900 text-sm leading-tight uppercase">{{ $course->dosen->name }}</span>
                            <span class="text-[10px] text-indigo-500 font-mono font-bold tracking-tighter italic mt-0.5">NIP: {{ $course->dosen->id }}</span>
                        </div>
                    </div>

                    {{-- LABORAN --}}
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-slate-100 flex-shrink-0 flex items-center justify-center overflow-hidden shadow-inner border border-gray-100">
                            @if($course->laboran && $course->laboran->avatar)
                                <img src="{{ asset('storage/' . $course->laboran->avatar) }}" alt="{{ $course->laboran->name }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-slate-400 font-black text-xs uppercase">
                                    {{ $course->laboran ? strtoupper(substr($course->laboran->name, 0, 2)) : 'LB' }}
                                </span>
                            @endif
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">Laboran</span>
                            <span class="font-black text-gray-900 text-sm leading-tight uppercase">{{ $course->laboran->name ?? '-' }}</span>
                        </div>
                    </div>

                    {{-- ASLAB --}}
                    <div class="flex items-center gap-5 pb-2">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-50 flex-shrink-0 flex items-center justify-center overflow-hidden shadow-inner border border-indigo-100">
                            @if($course->aslab->avatar)
                                <img src="{{ asset('storage/' . $course->aslab->avatar) }}" alt="{{ $course->aslab->name }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-indigo-400 font-black text-xs uppercase">{{ strtoupper(substr($course->aslab->name, 0, 2)) }}</span>
                            @endif
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">Asisten Laboratorium</span>
                            <span class="font-black text-indigo-600 text-sm leading-tight uppercase">{{ $course->aslab->name }}</span>
                        </div>
                    </div>
                </div>

                @if(strtoupper(auth()->user()->role) !== 'MAHASISWA')
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
                    <h3 class="font-black text-gray-800 text-[10px] uppercase tracking-[0.2em]">Daftar Tugas & Pertemuan</h3>
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
                                        
                                        @if(strtoupper(auth()->user()->role) === 'MAHASISWA')
                                            {{-- PERBAIKAN LOGIKA PRESENSI AKURAT --}}
                                            @php 
                                                $attendance = $meeting->attendances->where('student_id', auth()->id())->first(); 
                                                $anyAbsen = $meeting->attendances->count() > 0; // Cek apakah aslab sudah mulai absen siapapun
                                                
                                                if ($attendance) {
                                                    $attStatus = strtoupper($attendance->status);
                                                    $attConfig = match($attStatus) {
                                                        'HADIR'          => ['color' => 'text-emerald-500', 'label' => '● Hadir'],
                                                        'IZIN', 'SAKIT'  => ['color' => 'text-amber-500', 'label' => '● ' . ucfirst(strtolower($attStatus))],
                                                        'ALPA', 'ALPHA'  => ['color' => 'text-red-500', 'label' => '● Alpa'],
                                                        default          => ['color' => 'text-gray-300', 'label' => '○ Belum Presensi'],
                                                    };
                                                } else {
                                                    // Jika record tidak ada, cek apakah aslab sudah proses absen kawan lain
                                                    if ($anyAbsen) {
                                                        $attConfig = ['color' => 'text-red-500', 'label' => '● Alpa'];
                                                    } else {
                                                        $attConfig = ['color' => 'text-gray-300', 'label' => '○ Belum Presensi'];
                                                    }
                                                }
                                            @endphp
                                            <span class="text-[9px] font-black uppercase tracking-widest {{ $attConfig['color'] }}">
                                                {{ $attConfig['label'] }}
                                            </span>
                                        @endif
                                    </div>
                                    <h4 class="font-black text-gray-800 text-xl leading-tight group-hover:text-indigo-600 transition">{{ $meeting->title }}</h4>
                                    
                                    @if(strtoupper(auth()->user()->role) === 'MAHASISWA')
                                        @php 
                                            $isPastDeadline = $meeting->deadline && now()->gt(\Carbon\Carbon::parse($meeting->deadline));
                                            $isCompleted = $sub && strtoupper($sub->aslab_status) == 'ACC' && strtoupper($sub->laboran_status) == 'ACC';
                                        @endphp
                                        
                                        <div class="mt-5 space-y-2.5">
                                            @if($sub)
                                                <div class="flex items-center gap-3 text-[9px] font-black uppercase tracking-tighter">
                                                    <span class="text-gray-400 w-24 text-[8px]">Status Aslab:</span>
                                                    <span class="px-2 py-0.5 rounded-md {{ $sub->aslab_status == 'ACC' ? 'bg-emerald-50 text-emerald-600' : ($sub->aslab_status == 'REVISI' ? 'bg-red-50 text-red-600 animate-pulse' : 'bg-amber-50 text-amber-600') }}">
                                                        {{ $sub->aslab_status }}
                                                    </span>
                                                    @if($sub->aslab_status == 'ACC' && $sub->aslab_acc_at)
                                                        <span class="text-gray-400 text-[8px] border-l border-gray-200 pl-3">{{ \Carbon\Carbon::parse($sub->aslab_acc_at)->format('d M Y') }}</span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-3 text-[9px] font-black uppercase tracking-tighter">
                                                    <span class="text-gray-400 w-24 text-[8px]">Status Laboran:</span>
                                                    <span class="px-2 py-0.5 rounded-md {{ $sub->laboran_status == 'ACC' ? 'bg-emerald-50 text-emerald-600' : ($sub->laboran_status == 'REVISI' ? 'bg-red-50 text-red-600 animate-pulse' : 'bg-amber-50 text-amber-600') }}">
                                                        {{ $sub->laboran_status }}
                                                    </span>
                                                    @if($sub->laboran_status == 'ACC' && $sub->laboran_acc_at)
                                                        <span class="text-gray-400 text-[8px] border-l border-gray-200 pl-3">{{ \Carbon\Carbon::parse($sub->laboran_acc_at)->format('d M Y') }}</span>
                                                    @endif
                                                </div>

                                                @if($isPastDeadline && !$isCompleted)
                                                    <div class="mt-2 inline-flex items-center gap-2 px-3 py-1.5 bg-red-50 text-red-600 text-[9px] font-black rounded-lg uppercase tracking-widest border border-red-100">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                        Lewat Deadline & Belum ACC Sepenuhnya
                                                    </div>
                                                @endif
                                            @else
                                                @if($isPastDeadline)
                                                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-50 text-red-600 text-[9px] font-black rounded-lg uppercase tracking-widest border border-red-100">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                        Waktu Habis (Belum Mengumpulkan)
                                                    </div>
                                                @else
                                                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-50 text-amber-600 text-[9px] font-black rounded-lg uppercase tracking-widest border border-amber-100">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                        Belum Mengumpulkan Tugas
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <div class="flex flex-wrap items-center gap-3">
                                    @if(in_array(strtoupper(auth()->user()->role), ['DOSEN', 'ASLAB', 'LABORAN']))
                                        <a href="{{ route('attendance.index', $meeting->id) }}" class="px-5 py-3 bg-amber-50 text-amber-700 rounded-2xl text-[10px] font-black uppercase tracking-widest border border-amber-100 hover:bg-amber-100 transition shadow-sm text-center min-w-[100px]">
                                            Presensi
                                        </a>
                                        <a href="{{ route('submissions.index', $meeting->id) }}" class="px-5 py-3 bg-slate-800 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-900 transition flex items-center shadow-lg shadow-slate-200">
                                            Tugas ({{ $meeting->submissions->count() }})
                                        </a>
                                    @endif

                                    @if(strtoupper(auth()->user()->role) === 'MAHASISWA')
                                        <a href="{{ route('mahasiswa.submissions.manage', $meeting->id) }}" 
                                           class="px-5 py-3 {{ $sub ? ($sub->aslab_status == 'REVISI' ? 'bg-red-600 hover:bg-red-700' : 'bg-emerald-600 hover:bg-emerald-700') : 'bg-indigo-600 hover:bg-indigo-700' }} text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl transition active:scale-95 text-center min-w-[120px]">
                                            {{ $sub ? 'Kelola Tugas' : 'Kumpul Tugas' }}
                                        </a>
                                    @endif
                                    
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

    {{-- MODALS SECTION --}}
    @if(in_array(strtoupper(auth()->user()->role), ['DOSEN', 'ASLAB', 'LABORAN']))
    {{-- Modal Tambah Pertemuan --}}
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
                    <button type="submit" class="w-full bg-indigo-600 text-white py-5 rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition">Simpan Materi</button>
                    <button type="button" onclick="closeMeetingModal()" class="w-full text-[9px] font-black text-gray-400 uppercase tracking-widest hover:text-red-500 transition">Batalkan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Buat Laprak Final --}}
    <div id="modal-final-task" class="fixed inset-0 bg-slate-900/60 backdrop-blur-md hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-lg overflow-hidden">
            <form action="{{ route('final-tasks.store', $course->id) }}" method="POST">
                @csrf
                <div class="p-10 border-b border-gray-50 bg-indigo-50/30">
                    <h3 class="text-2xl font-black text-gray-800 uppercase tracking-tight">Setup Laprak Final</h3>
                    <p class="text-[9px] font-bold text-indigo-400 uppercase tracking-widest mt-1">Laporan Akhir Praktikum Semester</p>
                </div>
                <div class="p-10 space-y-6">
                    <div>
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2.5">Instruksi / Deskripsi Tugas</label>
                        <textarea name="description" rows="4" class="w-full rounded-2xl border-gray-100 bg-gray-50 text-sm p-4 focus:ring-4 focus:ring-indigo-50 outline-none resize-none" placeholder="Tuliskan instruksi pengerjaan di sini..." required></textarea>
                    </div>
                    <div>
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2.5">Batas Waktu (Deadline)</label>
                        <input type="datetime-local" name="deadline" class="w-full rounded-2xl border-gray-100 bg-gray-50 text-sm p-4 focus:ring-4 focus:ring-indigo-50 outline-none">
                    </div>
                </div>
                <div class="p-10 pt-0 bg-white flex flex-col sm:flex-row gap-4">
                    <button type="submit" class="flex-1 bg-indigo-600 text-white py-5 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition">Simpan & Publikasi</button>
                    <button type="button" onclick="document.getElementById('modal-final-task').classList.replace('flex', 'hidden')" class="flex-1 text-[9px] font-black text-gray-400 uppercase tracking-widest hover:text-red-500 transition">Batal</button>
                </div>
            </form>
        </div>
    </div>
    @endif

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
</x-app-layout>