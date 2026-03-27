<x-app-layout>
    <x-slot name="header_title">Monitoring: {{ $meeting->title }}</x-slot>

    <div class="max-w-[95rem] mx-auto py-8 px-4">
        
        {{-- TOP NAVIGATION & HEADER --}}
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <a href="{{ route('courses.show', $meeting->course_id) }}" 
                   class="inline-flex items-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] hover:text-indigo-600 transition group mb-3">
                    <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali ke Dashboard Course
                </a>
                <h2 class="text-3xl font-black text-gray-800 tracking-tight uppercase">Monitoring Presensi & Tugas</h2>
                <p class="text-[11px] font-bold text-gray-400 mt-1 uppercase tracking-widest">{{ $meeting->course->course_name }} — Pertemuan #{{ $meeting->meeting_number }}</p>
            </div>
        </div>

        {{-- STATS OVERVIEW --}}
        @php
            $totalStudents = $meeting->course->students->count();
            $submitted = count($submissions);
            $laboranAcc = collect($submissions)->filter(fn($s) => $s->laboran_status === 'ACC')->count();
            $aslabAcc = collect($submissions)->filter(fn($s) => $s->aslab_status === 'ACC')->count();
            $belumMengumpul = $totalStudents - $submitted;
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-md transition">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Total Mahasiswa</p>
                <h4 class="text-3xl font-black text-gray-800">{{ $totalStudents }}</h4>
            </div>
            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm border-l-4 border-l-red-500 hover:shadow-md transition">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Belum Kumpul</p>
                <h4 class="text-3xl font-black text-red-600">{{ $belumMengumpul }}</h4>
            </div>
            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm border-l-4 border-l-amber-500 hover:shadow-md transition">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Review Aslab (ACC)</p>
                <h4 class="text-3xl font-black text-amber-600">{{ $aslabAcc }} <span class="text-lg text-gray-300">/ {{ $submitted }}</span></h4>
            </div>
            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm border-l-4 border-l-blue-500 hover:shadow-md transition">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Review Laboran (FINAL)</p>
                <h4 class="text-3xl font-black text-blue-600">{{ $laboranAcc }} <span class="text-lg text-gray-300">/ {{ $submitted }}</span></h4>
            </div>
        </div>

        {{-- TABEL UTAMA --}}
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/50 text-[10px] uppercase font-black text-gray-400 border-b border-gray-100">
                        <tr>
                            <th class="px-8 py-6 tracking-widest">Mahasiswa</th>
                            <th class="px-8 py-6 tracking-widest text-center">Waktu Kumpul</th>
                            <th class="px-8 py-6 tracking-widest text-center">Riwayat</th>
                            <th class="px-8 py-6 tracking-widest text-center">Status Aslab</th>
                            <th class="px-8 py-6 tracking-widest text-center">Status Laboran</th>
                            <th class="px-8 py-6 tracking-widest text-right uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($meeting->course->students as $student)
                            @php $sub = $submissions[$student->id] ?? null; @endphp
                            <tr class="hover:bg-gray-50/50 transition-all group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        {{-- AVATAR DENGAN FALLBACK --}}
                                        <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center overflow-hidden shadow-inner border border-gray-50">
                                            @if($student->avatar)
                                                <img src="{{ asset('storage/' . $student->avatar) }}" 
                                                     alt="{{ $student->name }}" 
                                                     class="w-full h-full object-cover">
                                            @else
                                                <span class="text-slate-400 font-black text-xs uppercase">
                                                    {{ strtoupper(substr($student->name, 0, 2)) }}
                                                </span>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="text-gray-800 font-black block leading-none mb-1 text-sm uppercase">{{ $student->name }}</span>
                                            <span class="text-[10px] text-gray-400 font-mono italic tracking-tighter">{{ $student->id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    @if($sub)
                                        <div class="inline-flex flex-col text-[11px] font-black leading-tight text-center">
                                            <span class="text-gray-700 uppercase">{{ $sub->last_upload_at->timezone('Asia/Jakarta')->format('d M Y') }}</span>
                                            <span class="text-indigo-400 uppercase tracking-widest">{{ $sub->last_upload_at->timezone('Asia/Jakarta')->format('H:i') }} WIB</span>
                                        </div>
                                    @else
                                        <span class="text-[10px] text-gray-300 font-black uppercase tracking-widest italic">Belum Mengumpul</span>
                                    @endif
                                </td>
                                <td class="px-8 py-5 text-center">
                                    @if($sub && $sub->histories->count() > 0)
                                        <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-black shadow-sm border border-indigo-100">
                                            {{ $sub->histories->count() }}x Revisi
                                        </span>
                                    @else
                                        <span class="text-gray-200">—</span>
                                    @endif
                                </td>
                                
                                {{-- Status Aslab --}}
                                <td class="px-8 py-5 text-center">
                                    @if($sub)
                                        @php
                                            $sAslab = $sub->aslab_status;
                                            $colorAslab = match($sAslab) {
                                                'ACC'    => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                                'REVISI' => 'bg-red-50 text-red-600 border-red-100',
                                                default  => 'bg-amber-50 text-amber-600 border-amber-100',
                                            };
                                        @endphp
                                        <div class="flex flex-col items-center gap-2">
                                            <span class="px-4 py-2 {{ $colorAslab }} rounded-xl text-[9px] font-black uppercase border shadow-sm">
                                                {{ $sAslab ?? 'Waiting' }}
                                            </span>
                                            @if($sAslab === 'ACC' && $sub->aslab_acc_at)
                                                <span class="text-[8px] font-bold text-gray-400 uppercase tracking-tighter">
                                                    {{ $sub->aslab_acc_at->timezone('Asia/Jakarta')->format('d/m/y H:i') }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                {{-- Status Laboran --}}
                                <td class="px-8 py-5 text-center">
                                    @if($sub)
                                        @php
                                            $sLab = $sub->laboran_status ?? 'PENDING';
                                            $colorLab = match($sLab) {
                                                'ACC'    => 'bg-blue-50 text-blue-600 border-blue-100',
                                                'REVISI' => 'bg-orange-50 text-orange-600 border-orange-100',
                                                default  => 'bg-slate-50 text-slate-400 border-slate-100',
                                            };
                                        @endphp
                                        <div class="flex flex-col items-center gap-2">
                                            <span class="px-4 py-2 {{ $colorLab }} rounded-xl text-[9px] font-black uppercase border shadow-sm">
                                                {{ $sLab }}
                                            </span>
                                            @if($sLab === 'ACC' && $sub->laboran_acc_at)
                                                <span class="text-[8px] font-bold text-gray-400 uppercase tracking-tighter">
                                                    {{ $sub->laboran_acc_at->timezone('Asia/Jakarta')->format('d/m/y H:i') }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                <td class="px-8 py-5 text-right">
                                    @if($sub)
                                        <a href="{{ route('submissions.handler', $sub->id) }}" 
                                           class="inline-block bg-indigo-600 text-white px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase hover:bg-indigo-700 shadow-xl shadow-indigo-100 active:scale-95 transition-all tracking-widest">
                                            Review File
                                        </a>
                                    @else
                                        <button disabled class="text-gray-300 text-[10px] font-black italic tracking-widest opacity-50">N/A</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 10px; }
    </style>
</x-app-layout>