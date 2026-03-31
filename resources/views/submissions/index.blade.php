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

            {{-- PANEL PENGATURAN DEADLINE --}}
            @if(in_array(strtoupper(auth()->user()->role), ['ASLAB', 'LABORAN', 'DOSEN']))
            <div class="w-full md:w-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <form action="{{ route('meetings.update-deadline', $meeting->id) }}" method="POST" class="flex flex-col sm:flex-row items-end gap-3">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">Batas Waktu</label>
                        <input type="datetime-local" name="deadline" 
                               value="{{ $meeting->deadline ? date('Y-m-d\TH:i', strtotime($meeting->deadline)) : '' }}" 
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
            $totalStudents = $meeting->course->students->count();
            $submitted = count($submissions);
            $laboranAcc = collect($submissions)->filter(fn($s) => strtoupper($s->laboran_status) === 'ACC')->count();
            $aslabAcc = collect($submissions)->filter(fn($s) => strtoupper($s->aslab_status) === 'ACC')->count();
        @endphp

        {{-- Grid diubah menjadi 3 kolom agar proporsional --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm border-l-4 border-l-emerald-500 hover:shadow-md transition">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Sudah Kumpul</p>
                <h4 class="text-3xl font-black text-emerald-600">{{ $submitted }} <span class="text-lg text-gray-300">/ {{ $totalStudents }}</span></h4>
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

        {{-- TABEL UTAMA DENGAN FILTER --}}
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            
            {{-- BAR FILTER PENCARIAN & CHECKBOX --}}
            <div class="p-6 border-b border-gray-50 flex flex-col sm:flex-row justify-between gap-4 items-center bg-gray-50/30">
                <div class="w-full sm:w-1/3 relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" id="searchInput" placeholder="Cari Nama atau NIM..." class="block w-full pl-10 pr-4 py-3 rounded-2xl border-gray-200 text-xs font-bold focus:ring-indigo-50 focus:border-indigo-600 text-gray-600 transition">
                </div>
                <div>
                    <label class="flex items-center gap-2 cursor-pointer text-[10px] font-black text-gray-500 uppercase tracking-widest hover:text-indigo-600 transition select-none">
                        <input type="checkbox" id="unsubmittedFilter" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 cursor-pointer">
                        Hanya Tampilkan Yang Belum Kumpul
                    </label>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="submissionsTable">
                    <thead class="bg-white text-[10px] uppercase font-black text-gray-400 border-b border-gray-100">
                        <tr>
                            <th class="px-8 py-4 tracking-widest whitespace-nowrap">Mahasiswa</th>
                            
                            {{-- SORTING WAKTU KUMPUL --}}
                            <th class="px-8 py-4 tracking-widest text-center whitespace-nowrap cursor-pointer hover:bg-gray-50 transition group select-none" onclick="toggleSort()" title="Klik untuk mengurutkan">
                                <div class="flex items-center justify-center gap-1">
                                    Waktu Kumpul
                                    <svg id="sortIcon" class="w-3 h-3 text-gray-300 group-hover:text-indigo-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                                </div>
                            </th>
                            
                            <th class="px-8 py-4 tracking-widest text-center whitespace-nowrap">Riwayat</th>
                            
                            {{-- FILTER STATUS ASLAB --}}
                            <th class="px-4 py-4 tracking-widest text-center whitespace-nowrap">
                                <div class="flex flex-col items-center gap-2">
                                    <span>Status Aslab</span>
                                    <select id="aslabFilter" class="text-[9px] font-bold rounded-lg border-gray-200 py-1 pl-2 pr-6 bg-gray-50 focus:ring-0 text-gray-500 cursor-pointer">
                                        <option value="">Semua Filter</option>
                                        <option value="PENDING">PENDING</option>
                                        <option value="REVISI">REVISI</option>
                                        <option value="ACC">ACC</option>
                                    </select>
                                </div>
                            </th>
                            
                            {{-- FILTER STATUS LABORAN --}}
                            <th class="px-4 py-4 tracking-widest text-center whitespace-nowrap">
                                <div class="flex flex-col items-center gap-2">
                                    <span>Status Laboran</span>
                                    <select id="laboranFilter" class="text-[9px] font-bold rounded-lg border-gray-200 py-1 pl-2 pr-6 bg-gray-50 focus:ring-0 text-gray-500 cursor-pointer">
                                        <option value="">Semua Filter</option>
                                        <option value="PENDING">PENDING</option>
                                        <option value="REVISI">REVISI</option>
                                        <option value="ACC">ACC</option>
                                    </select>
                                </div>
                            </th>
                            
                            <th class="px-8 py-4 tracking-widest text-center uppercase whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50" id="tableBody">
                        @foreach($meeting->course->students as $student)
                            @php 
                                $sub = $submissions[$student->id] ?? null; 
                                // Setup Data Attribute Values for JS Filtering
                                $subStatus = $sub ? 'true' : 'false';
                                $aslabStat = $sub ? strtoupper($sub->aslab_status) : 'NONE';
                                $laboranStat = $sub ? strtoupper($sub->laboran_status) : 'NONE';
                                $timestamp = $sub ? \Carbon\Carbon::parse($sub->last_upload_at)->timestamp : 0;
                            @endphp
                            
                            {{-- TR DIINJEKSI DATA ATTRIBUTES --}}
                            <tr class="hover:bg-gray-50/50 transition-all group table-row-item" 
                                data-name="{{ strtolower($student->name) }}" 
                                data-nim="{{ strtolower($student->id) }}"
                                data-submitted="{{ $subStatus }}"
                                data-aslab="{{ $aslabStat }}"
                                data-laboran="{{ $laboranStat }}"
                                data-time="{{ $timestamp }}">
                                
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
                                            $colorAslab = match($aslabStat) {
                                                'ACC'    => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                                'REVISI' => 'bg-red-50 text-red-600 border-red-100',
                                                default  => 'bg-amber-50 text-amber-600 border-amber-100',
                                            };
                                        @endphp
                                        <div class="flex flex-col items-center gap-1.5">
                                            <span class="whitespace-nowrap px-3 py-1.5 {{ $colorAslab }} rounded-xl text-[9px] font-black uppercase border shadow-sm">{{ $aslabStat }}</span>
                                            @if($aslabStat === 'ACC' && $sub->aslab_acc_at)
                                                <span class="whitespace-nowrap text-[8px] font-bold text-gray-400 uppercase tracking-widest">{{ \Carbon\Carbon::parse($sub->aslab_acc_at)->format('d/m/Y') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                {{-- Status Laboran --}}
                                <td class="px-4 py-5 text-center whitespace-nowrap">
                                    @if($sub)
                                        @php
                                            $colorLab = match($laboranStat) {
                                                'ACC'    => 'bg-blue-50 text-blue-600 border-blue-100',
                                                'REVISI' => 'bg-orange-50 text-orange-600 border-orange-100',
                                                default  => 'bg-slate-50 text-slate-400 border-slate-100',
                                            };
                                        @endphp
                                        <div class="flex flex-col items-center gap-1.5">
                                            <span class="whitespace-nowrap px-3 py-1.5 {{ $colorLab }} rounded-xl text-[9px] font-black uppercase border shadow-sm">{{ $laboranStat }}</span>
                                            @if($laboranStat === 'ACC' && $sub->laboran_acc_at)
                                                <span class="whitespace-nowrap text-[8px] font-bold text-gray-400 uppercase tracking-widest">{{ \Carbon\Carbon::parse($sub->laboran_acc_at)->format('d/m/Y') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                {{-- AKSI --}}
                                <td class="px-8 py-5 text-center whitespace-nowrap">
                                    @if($sub)
                                        @if(strtoupper(auth()->user()->role) === 'DOSEN')
                                            <a href="{{ $sub->submission_link }}" target="_blank" class="whitespace-nowrap inline-block bg-emerald-600 text-white px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase hover:bg-emerald-700 shadow-xl shadow-emerald-100 active:scale-95 transition-all tracking-widest">
                                                Lihat File
                                            </a>
                                        @else
                                            <a href="{{ route('submissions.handler', $sub->id) }}" class="whitespace-nowrap inline-block bg-indigo-600 text-white px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase hover:bg-indigo-700 shadow-xl shadow-indigo-100 active:scale-95 transition-all tracking-widest">
                                                Review File
                                            </a>
                                        @endif
                                    @else
                                        <span class="whitespace-nowrap text-gray-300 text-[10px] font-black italic tracking-widest opacity-50 block text-center">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Empty State (Hidden by default, shown via JS if no results) --}}
                <div id="emptyState" class="hidden p-16 text-center">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Pencarian / Filter tidak menemukan hasil</p>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPT JAVASCRIPT UNTUK FILTER & SORTING --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const unsubmittedFilter = document.getElementById('unsubmittedFilter');
            const aslabFilter = document.getElementById('aslabFilter');
            const laboranFilter = document.getElementById('laboranFilter');
            const tableBody = document.getElementById('tableBody');
            const rows = Array.from(document.querySelectorAll('.table-row-item'));
            const emptyState = document.getElementById('emptyState');
            
            let sortDesc = true; // Default Sort State

            // 1. FUNGSI FILTER UTAMA
            function applyFilters() {
                const query = searchInput.value.toLowerCase();
                const onlyUnsubmitted = unsubmittedFilter.checked;
                const aslab = aslabFilter.value;
                const laboran = laboranFilter.value;
                
                let visibleCount = 0;

                rows.forEach(row => {
                    const name = row.getAttribute('data-name');
                    const nim = row.getAttribute('data-nim');
                    const submitted = row.getAttribute('data-submitted') === 'true';
                    const aslabStat = row.getAttribute('data-aslab');
                    const laboranStat = row.getAttribute('data-laboran');

                    // Check Logic
                    const matchSearch = name.includes(query) || nim.includes(query);
                    const matchUnsubmitted = !onlyUnsubmitted || (onlyUnsubmitted && !submitted);
                    const matchAslab = aslab === "" || aslabStat === aslab;
                    const matchLaboran = laboran === "" || laboranStat === laboran;

                    if (matchSearch && matchUnsubmitted && matchAslab && matchLaboran) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Show or Hide Empty State
                emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
            }

            // 2. FUNGSI SORTING
            window.toggleSort = function() {
                sortDesc = !sortDesc;
                const icon = document.getElementById('sortIcon');
                
                // Animasi Panah
                if(sortDesc) {
                    icon.style.transform = 'rotate(0deg)'; // Terbaru Atas
                } else {
                    icon.style.transform = 'rotate(180deg)'; // Terlama Atas
                }

                // Sort Array Node
                const sortedRows = rows.sort((a, b) => {
                    const timeA = parseInt(a.getAttribute('data-time'));
                    const timeB = parseInt(b.getAttribute('data-time'));
                    return sortDesc ? timeB - timeA : timeA - timeB;
                });

                // Render ulang baris ke dalam tbody
                tableBody.innerHTML = '';
                sortedRows.forEach(row => tableBody.appendChild(row));
            };

            // 3. EVENT LISTENERS
            searchInput.addEventListener('input', applyFilters);
            
            // Perbaikan: Saat checkbox diklik, reset filter status
            unsubmittedFilter.addEventListener('change', function() {
                if (this.checked) {
                    aslabFilter.value = "";
                    laboranFilter.value = "";
                }
                applyFilters();
            });

            aslabFilter.addEventListener('change', applyFilters);
            laboranFilter.addEventListener('change', applyFilters);
        });
    </script>
</x-app-layout>