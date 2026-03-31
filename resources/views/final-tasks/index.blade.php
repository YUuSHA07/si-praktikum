<x-app-layout>
    <x-slot name="header_title">Kelola Laprak Final: {{ $finalTask->course->course_name ?? $finalTask->course->name }}</x-slot>

    <div class="max-w-[95rem] mx-auto py-8 px-4">
        
        {{-- TOP NAVIGATION & HEADER --}}
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <a href="{{ route('courses.show', $finalTask->course_id) }}" 
                   class="inline-flex items-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] hover:text-indigo-600 transition group mb-3">
                    <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali ke Dashboard Course
                </a>
                <h2 class="text-3xl font-black text-gray-800 tracking-tight uppercase">Kelola Laprak Final</h2>
                <p class="text-[11px] font-bold text-gray-400 mt-1 uppercase tracking-widest">{{ $finalTask->course->course_name ?? $finalTask->course->name }}</p>
            </div>
            
            {{-- PANEL PENGATURAN DEADLINE --}}
            @if(in_array(strtoupper(auth()->user()->role), ['ASLAB', 'LABORAN', 'DOSEN']))
            <div class="w-full md:w-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <form action="{{ route('final-tasks.update-deadline', $finalTask->id) }}" method="POST" class="flex flex-col sm:flex-row items-end gap-3">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">Batas Waktu</label>
                        <input type="datetime-local" name="deadline" 
                               value="{{ $finalTask->deadline ? date('Y-m-d\TH:i', strtotime($finalTask->deadline)) : '' }}" 
                               class="rounded-xl border-gray-200 focus:ring-indigo-600 focus:border-indigo-600 text-xs font-bold text-gray-600" required>
                    </div>
                    <button type="submit" class="px-5 py-2.5 bg-slate-900 text-white text-[10px] font-black rounded-xl uppercase tracking-widest hover:bg-slate-800 transition active:scale-95">
                        Simpan
                    </button>
                </form>
            </div>
            @endif
        </div>

        {{-- STATS OVERVIEW --}}
        @php
            $totalStudents = $finalTask->course->students->count();
            $submitted = count($submissions);
            $dosenAcc = collect($submissions)->filter(fn($s) => strtoupper($s->dosen_status) === 'ACC')->count();
            $laboranAcc = collect($submissions)->filter(fn($s) => strtoupper($s->laboran_status) === 'ACC')->count();
            $aslabAcc = collect($submissions)->filter(fn($s) => strtoupper($s->aslab_status) === 'ACC')->count();
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm border-l-4 border-l-emerald-500 hover:shadow-md transition">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Sudah Kumpul</p>
                <h4 class="text-3xl font-black text-emerald-600">{{ $submitted }} <span class="text-lg text-gray-300">/ {{ $totalStudents }}</span></h4>
            </div>
            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm border-l-4 border-l-amber-500 hover:shadow-md transition">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Verifikasi Aslab</p>
                <h4 class="text-3xl font-black text-amber-600">{{ $aslabAcc }} <span class="text-lg text-gray-300">/ {{ $submitted }}</span></h4>
            </div>
            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm border-l-4 border-l-indigo-500 hover:shadow-md transition">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Verifikasi Laboran</p>
                <h4 class="text-3xl font-black text-indigo-600">{{ $laboranAcc }} <span class="text-lg text-gray-300">/ {{ $submitted }}</span></h4>
            </div>
            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm border-l-4 border-l-blue-500 hover:shadow-md transition">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">ACC Dosen (FINAL)</p>
                <h4 class="text-3xl font-black text-blue-600">{{ $dosenAcc }} <span class="text-lg text-gray-300">/ {{ $submitted }}</span></h4>
            </div>
        </div>

        {{-- TABEL UTAMA --}}
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/50 text-[10px] uppercase font-black text-gray-400 border-b border-gray-100">
                        <tr>
                            <th class="px-8 py-6 tracking-widest whitespace-nowrap">Mahasiswa</th>
                            <th class="px-8 py-6 tracking-widest text-center whitespace-nowrap">Waktu Kumpul</th>
                            <th class="px-8 py-6 tracking-widest text-center whitespace-nowrap">Riwayat</th>
                            <th class="px-4 py-6 tracking-widest text-center whitespace-nowrap">Status Aslab</th>
                            <th class="px-4 py-6 tracking-widest text-center whitespace-nowrap">Status Laboran</th>
                            <th class="px-4 py-6 tracking-widest text-center whitespace-nowrap">Status Dosen</th>
                            {{-- DIUBAH: text-center untuk Header AKSI --}}
                            <th class="px-8 py-6 tracking-widest text-center uppercase whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($finalTask->course->students as $student)
                            @php $sub = $submissions[$student->id] ?? null; @endphp
                            <tr class="hover:bg-gray-50/50 transition-all group">
                                <td class="px-8 py-5 whitespace-nowrap">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center overflow-hidden shadow-inner border border-gray-50">
                                            @if($student->avatar)
                                                <img src="{{ asset('storage/' . $student->avatar) }}" alt="{{ $student->name }}" class="w-full h-full object-cover">
                                            @else
                                                <span class="text-slate-400 font-black text-xs uppercase">{{ strtoupper(substr($student->name, 0, 2)) }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="text-gray-800 font-black block leading-none mb-1 text-sm uppercase">{{ $student->name }}</span>
                                            <span class="text-[10px] text-gray-400 font-mono italic tracking-tighter">{{ $student->id }}</span>
                                        </div>
                                    </div>
                                </td>
                                
                                {{-- Waktu Kumpul --}}
                                <td class="px-8 py-5 text-center whitespace-nowrap">
                                    @if($sub)
                                        <div class="inline-flex flex-col text-[11px] font-black leading-tight text-center">
                                            <span class="text-gray-700 uppercase">{{ \Carbon\Carbon::parse($sub->last_upload_at)->timezone('Asia/Jakarta')->format('d M Y') }}</span>
                                            <span class="text-indigo-400 uppercase tracking-widest">{{ \Carbon\Carbon::parse($sub->last_upload_at)->timezone('Asia/Jakarta')->format('H:i') }} WIB</span>
                                        </div>
                                    @else
                                        <span class="text-[10px] text-gray-300 font-black uppercase tracking-widest italic">Belum Mengumpul</span>
                                    @endif
                                </td>

                                {{-- Riwayat --}}
                                <td class="px-8 py-5 text-center whitespace-nowrap">
                                    @if($sub && $sub->histories->count() > 0)
                                        <span class="inline-block whitespace-nowrap px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-black shadow-sm border border-indigo-100">
                                            {{ $sub->histories->count() }}x Revisi
                                        </span>
                                    @else
                                        <span class="text-gray-200">—</span>
                                    @endif
                                </td>
                                
                                {{-- Status Aslab --}}
                                <td class="px-4 py-5 text-center whitespace-nowrap">
                                    @if($sub)
                                        @php
                                            $sAslab = strtoupper($sub->aslab_status ?? 'PENDING');
                                            $colorAslab = match($sAslab) {
                                                'ACC'    => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                                'REVISI' => 'bg-red-50 text-red-600 border-red-100',
                                                default  => 'bg-amber-50 text-amber-600 border-amber-100',
                                            };
                                        @endphp
                                        <div class="flex flex-col items-center gap-1.5">
                                            <span class="whitespace-nowrap px-3 py-1.5 {{ $colorAslab }} rounded-xl text-[9px] font-black uppercase border shadow-sm">{{ $sAslab }}</span>
                                            @if($sAslab === 'ACC' && $sub->aslab_acc_at)
                                                <span class="whitespace-nowrap text-[8px] font-bold text-gray-400 uppercase tracking-widest">{{ \Carbon\Carbon::parse($sub->aslab_acc_at)->format('d/m/Y') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                {{-- Status Laboran --}}
                                <td class="px-4 py-5 text-center whitespace-nowrap">
                                    @if($sub)
                                        @php
                                            $sLab = strtoupper($sub->laboran_status ?? 'PENDING');
                                            $colorLab = match($sLab) {
                                                'ACC'    => 'bg-blue-50 text-blue-600 border-blue-100',
                                                'REVISI' => 'bg-orange-50 text-orange-600 border-orange-100',
                                                default  => 'bg-slate-50 text-slate-400 border-slate-100',
                                            };
                                        @endphp
                                        <div class="flex flex-col items-center gap-1.5">
                                            <span class="whitespace-nowrap px-3 py-1.5 {{ $colorLab }} rounded-xl text-[9px] font-black uppercase border shadow-sm">{{ $sLab }}</span>
                                            @if($sLab === 'ACC' && $sub->laboran_acc_at)
                                                <span class="whitespace-nowrap text-[8px] font-bold text-gray-400 uppercase tracking-widest">{{ \Carbon\Carbon::parse($sub->laboran_acc_at)->format('d/m/Y') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                {{-- Status Dosen --}}
                                <td class="px-4 py-5 text-center whitespace-nowrap">
                                    @if($sub)
                                        @php
                                            $sDosen = strtoupper($sub->dosen_status ?? 'PENDING');
                                            $colorDosen = match($sDosen) {
                                                'ACC'    => 'bg-purple-50 text-purple-600 border-purple-100',
                                                'REVISI' => 'bg-pink-50 text-pink-600 border-pink-100',
                                                default  => 'bg-slate-50 text-slate-400 border-slate-100',
                                            };
                                        @endphp
                                        <div class="flex flex-col items-center gap-1.5">
                                            <span class="whitespace-nowrap px-3 py-1.5 {{ $colorDosen }} rounded-xl text-[9px] font-black uppercase border shadow-sm">{{ $sDosen }}</span>
                                            @if($sDosen === 'ACC' && $sub->dosen_acc_at)
                                                <span class="whitespace-nowrap text-[8px] font-bold text-gray-400 uppercase tracking-widest">{{ \Carbon\Carbon::parse($sub->dosen_acc_at)->format('d/m/Y') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                {{-- AKSI --}}
                                {{-- DIUBAH: text-center untuk isi kolom AKSI --}}
                                <td class="px-8 py-5 text-center whitespace-nowrap">
                                    @if($sub)
                                        @if(strtoupper(auth()->user()->role) === 'DOSEN')
                                            <a href="{{ $sub->submission_link }}" 
                                               target="_blank"
                                               class="whitespace-nowrap inline-block bg-emerald-600 text-white px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase hover:bg-emerald-700 shadow-xl shadow-emerald-100 active:scale-95 transition-all tracking-widest">
                                                Lihat File
                                            </a>
                                        @else
                                            <a href="{{ route('final-tasks.handler', $sub->id) }}" 
                                               class="whitespace-nowrap inline-block bg-indigo-600 text-white px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase hover:bg-indigo-700 shadow-xl shadow-indigo-100 active:scale-95 transition-all tracking-widest">
                                                Review File
                                            </a>
                                        @endif
                                    @else
                                        {{-- DIUBAH: Tambah block dan text-center agar tulisan N/A rapi di tengah --}}
                                        <span class="whitespace-nowrap text-gray-300 text-[10px] font-black italic tracking-widest opacity-50 block text-center">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>