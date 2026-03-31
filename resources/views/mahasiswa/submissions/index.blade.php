<x-app-layout>
    <x-slot name="header_title">
        Rekapitulasi Tugas Saya
    </x-slot>

    <div class="mb-8">
        <h2 class="text-3xl font-black text-gray-800 uppercase tracking-tight">Semua Tugas</h2>
        <p class="text-sm text-gray-500 font-medium">Pantau tugas dari seluruh kelas yang Anda ikuti.</p>
    </div>

    {{-- BAR FILTER & PENCARIAN --}}
    <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 mb-8 flex flex-col gap-4">
        <div class="flex flex-col md:flex-row justify-between gap-4 items-center">
            {{-- Search Bar --}}
            <div class="w-full md:w-1/3 relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" id="searchInput" placeholder="Cari Kelas atau Judul Tugas..." class="block w-full pl-10 pr-4 py-3 rounded-2xl border-gray-200 text-xs font-bold focus:ring-indigo-50 focus:border-indigo-600 text-gray-600 transition">
            </div>

            {{-- Checkbox Belum Kumpul --}}
            <div>
                <label class="flex items-center gap-2 cursor-pointer text-[10px] font-black text-gray-500 uppercase tracking-widest hover:text-indigo-600 transition select-none">
                    <input type="checkbox" id="unsubmittedFilter" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 cursor-pointer">
                    Hanya Tampilkan Yang Belum Kumpul
                </label>
            </div>
        </div>

        <div class="flex flex-col md:flex-row justify-between gap-4 items-center border-t border-gray-50 pt-4 mt-2">
            <div class="flex flex-wrap gap-4 w-full md:w-auto">
                {{-- Filter Aslab --}}
                <div class="flex items-center gap-2">
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Aslab:</span>
                    <select id="aslabFilter" class="text-[10px] font-bold rounded-xl border-gray-200 py-1.5 pl-3 pr-8 bg-gray-50 focus:ring-0 text-gray-600 cursor-pointer">
                        <option value="">Semua</option>
                        <option value="PENDING">PENDING</option>
                        <option value="REVISI">REVISI</option>
                        <option value="ACC">ACC</option>
                    </select>
                </div>

                {{-- Filter Laboran --}}
                <div class="flex items-center gap-2">
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Laboran:</span>
                    <select id="laboranFilter" class="text-[10px] font-bold rounded-xl border-gray-200 py-1.5 pl-3 pr-8 bg-gray-50 focus:ring-0 text-gray-600 cursor-pointer">
                        <option value="">Semua</option>
                        <option value="PENDING">PENDING</option>
                        <option value="REVISI">REVISI</option>
                        <option value="ACC">ACC</option>
                    </select>
                </div>

                {{-- Filter Dosen --}}
                <div class="flex items-center gap-2">
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Dosen:</span>
                    <select id="dosenFilter" class="text-[10px] font-bold rounded-xl border-gray-200 py-1.5 pl-3 pr-8 bg-gray-50 focus:ring-0 text-gray-600 cursor-pointer">
                        <option value="">Semua</option>
                        <option value="PENDING">PENDING</option>
                        <option value="REVISI">REVISI</option>
                        <option value="ACC">ACC</option>
                    </select>
                </div>
            </div>

            {{-- Tombol Sort Waktu --}}
            <button type="button" onclick="toggleSort()" class="flex items-center gap-2 px-4 py-2 bg-slate-50 hover:bg-slate-100 rounded-xl transition group w-full md:w-auto justify-center">
                <span class="text-[9px] font-black text-gray-500 uppercase tracking-widest group-hover:text-indigo-600">Urutkan Waktu</span>
                <svg id="sortIcon" class="w-3 h-3 text-gray-400 group-hover:text-indigo-600 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
            </button>
        </div>
    </div>

    {{-- CONTAINER KARTU TUGAS --}}
    <div class="grid grid-cols-1 gap-6" id="tasksContainer">
        @forelse($allTasks as $task)
            @php 
                $sub = $task->submission;
                $isFinal = $task->is_final; 
                $courseName = $task->course->course_name ?? 'N/A';
                $courseId = $task->course->id ?? 0;

                // VARIABEL UNTUK FILTER DATA ATTRIBUTES
                $subStatus = $sub ? 'true' : 'false';
                $aslabStat = $sub ? strtoupper($sub->aslab_status ?? 'PENDING') : 'NONE';
                $laboranStat = $sub ? strtoupper($sub->laboran_status ?? 'PENDING') : 'NONE';
                $dosenStat = ($sub && $isFinal) ? strtoupper($sub->dosen_status ?? 'PENDING') : 'NONE';
                
                // LOGIKA SORTING WAKTU
                if ($sub) {
                    $timestamp = \Carbon\Carbon::parse($sub->last_upload_at)->timestamp;
                } else {
                    $timestamp = $task->deadline ? \Carbon\Carbon::parse($task->deadline)->timestamp : 0;
                }
            @endphp

            <div class="task-card-item bg-white p-6 rounded-[2rem] shadow-sm border {{ $sub ? 'border-gray-100' : 'border-red-100 bg-red-50/10' }} flex flex-col md:flex-row justify-between items-center gap-6 hover:shadow-md transition relative overflow-hidden"
                 data-course="{{ strtolower($courseName) }}"
                 data-title="{{ strtolower($task->title) }}"
                 data-submitted="{{ $subStatus }}"
                 data-aslab="{{ $aslabStat }}"
                 data-laboran="{{ $laboranStat }}"
                 data-dosen="{{ $dosenStat }}"
                 data-time="{{ $timestamp }}">
                
                {{-- Indikator Warna Samping (Merah jika belum kumpul) --}}
                @if(!$sub)
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-red-500"></div>
                @endif

                {{-- Info Tugas --}}
                <div class="flex-1 w-full pl-2">
                    <div class="flex flex-wrap items-center gap-3 mb-2">
                        <span class="px-3 py-1 {{ $isFinal ? 'bg-purple-50 text-purple-600' : 'bg-indigo-50 text-indigo-600' }} text-[9px] font-black rounded-full uppercase tracking-widest">
                            {{ $courseName }}
                        </span>
                        <span class="text-[10px] {{ $sub ? 'text-gray-400' : 'text-red-400' }} font-bold uppercase tracking-tighter">
                            {{ $task->number }}
                        </span>
                    </div>
                    <h3 class="text-lg font-black text-gray-900">
                        {{ $task->title }}
                    </h3>
                    
                    {{-- Tampilkan Info Update / Deadline --}}
                    @if($sub)
                        <p class="text-[10px] text-gray-400 font-bold uppercase mt-1">Update Terakhir: {{ \Carbon\Carbon::parse($sub->last_upload_at)->format('d M Y, H:i') }} WIB</p>
                    @else
                        <p class="text-[10px] text-red-500 font-bold uppercase mt-1">
                            Batas Waktu: {{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('d M Y, H:i') . ' WIB' : 'Belum Diatur' }}
                        </p>
                    @endif
                </div>

                {{-- Status Badges --}}
                <div class="flex flex-wrap gap-4 items-center justify-center md:justify-end w-full md:w-auto">
                    
                    @if($sub)
                        {{-- Jika SUDAH Kumpul --}}
                        
                        {{-- Status Aslab --}}
                        <div class="flex flex-col items-center md:items-end">
                            <span class="block text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Status Aslab</span>
                            <span class="px-3 py-1.5 rounded-xl text-[9px] font-black uppercase shadow-sm border border-transparent {{ strtoupper($sub->aslab_status) == 'ACC' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : (strtoupper($sub->aslab_status) == 'REVISI' ? 'bg-red-50 text-red-600 border-red-100' : 'bg-amber-50 text-amber-600 border-amber-100') }}">
                                {{ $sub->aslab_status ?? 'PENDING' }}
                            </span>
                            @if(strtoupper($sub->aslab_status) === 'ACC' && $sub->aslab_acc_at)
                                <span class="block mt-1.5 text-[8px] font-bold text-gray-400 uppercase tracking-widest">{{ \Carbon\Carbon::parse($sub->aslab_acc_at)->format('d/m/Y') }}</span>
                            @endif
                        </div>

                        {{-- Status Laboran --}}
                        <div class="flex flex-col items-center md:items-end border-l border-gray-100 pl-4">
                            <span class="block text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Status Laboran</span>
                            <span class="px-3 py-1.5 rounded-xl text-[9px] font-black uppercase shadow-sm border border-transparent {{ strtoupper($sub->laboran_status) == 'ACC' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : (strtoupper($sub->laboran_status) == 'REVISI' ? 'bg-red-50 text-red-600 border-red-100' : 'bg-amber-50 text-amber-600 border-amber-100') }}">
                                {{ $sub->laboran_status ?? 'PENDING' }}
                            </span>
                            @if(strtoupper($sub->laboran_status) === 'ACC' && $sub->laboran_acc_at)
                                <span class="block mt-1.5 text-[8px] font-bold text-gray-400 uppercase tracking-widest">{{ \Carbon\Carbon::parse($sub->laboran_acc_at)->format('d/m/Y') }}</span>
                            @endif
                        </div>

                        {{-- Status Dosen (Khusus Final Task) --}}
                        @if($isFinal)
                        <div class="flex flex-col items-center md:items-end border-l border-gray-100 pl-4">
                            <span class="block text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Status Dosen</span>
                            <span class="px-3 py-1.5 rounded-xl text-[9px] font-black uppercase shadow-sm border border-transparent {{ strtoupper($sub->dosen_status) == 'ACC' ? 'bg-purple-50 text-purple-600 border-purple-100' : (strtoupper($sub->dosen_status) == 'REVISI' ? 'bg-pink-50 text-pink-600 border-pink-100' : 'bg-slate-50 text-slate-500 border-slate-100') }}">
                                {{ $sub->dosen_status ?? 'PENDING' }}
                            </span>
                            @if(strtoupper($sub->dosen_status) === 'ACC' && $sub->dosen_acc_at)
                                <span class="block mt-1.5 text-[8px] font-bold text-gray-400 uppercase tracking-widest">{{ \Carbon\Carbon::parse($sub->dosen_acc_at)->format('d/m/Y') }}</span>
                            @endif
                        </div>
                        @endif

                        {{-- Tombol Buka File --}}
                        <div class="ml-2">
                            <a href="{{ $sub->submission_link }}" target="_blank" title="Buka G-Drive" class="p-4 bg-slate-50 text-slate-400 rounded-2xl hover:bg-slate-100 transition group block">
                                <svg class="w-5 h-5 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                        </div>
                    @else
                        {{-- Jika BELUM Kumpul --}}
                        <div class="text-center">
                            <span class="px-5 py-3 rounded-xl text-[10px] font-black uppercase bg-red-100 text-red-600 border border-red-200 shadow-sm flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Belum Mengumpul
                            </span>
                        </div>
                    @endif

                    {{-- Tombol Detail Kelas selalu ada --}}
                    <div class="ml-2">
                        <a href="{{ route('courses.show', $courseId) }}" class="px-6 py-4 {{ $isFinal ? 'bg-purple-600 shadow-purple-100 hover:bg-purple-700' : 'bg-indigo-600 shadow-indigo-100 hover:bg-indigo-700' }} text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-lg transition block text-center">
                            Detail Kelas
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-gray-50 rounded-[3rem] p-20 text-center border-2 border-dashed border-gray-200">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-400 uppercase tracking-widest">Belum ada tugas tersedia</h3>
                <p class="text-sm text-gray-400 mt-2">Tugas dari kelas yang Anda ikuti akan muncul di sini.</p>
            </div>
        @endforelse

        {{-- Empty State (Hidden by default, shown via JS if no results match filter) --}}
        <div id="emptyState" class="hidden bg-gray-50 rounded-[3rem] p-16 text-center border-2 border-dashed border-gray-200">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Pencarian / Filter tidak menemukan hasil</p>
        </div>
    </div>

    {{-- SCRIPT JAVASCRIPT UNTUK FILTER & SORTING (KARTU) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const unsubmittedFilter = document.getElementById('unsubmittedFilter');
            const aslabFilter = document.getElementById('aslabFilter');
            const laboranFilter = document.getElementById('laboranFilter');
            const dosenFilter = document.getElementById('dosenFilter');
            const tasksContainer = document.getElementById('tasksContainer');
            // Ambil array card saja (Abaikan empty state div)
            const cards = Array.from(document.querySelectorAll('.task-card-item'));
            const emptyState = document.getElementById('emptyState');
            
            let sortDesc = true; // Default Sort State (Terbaru)

            // 1. FUNGSI FILTER UTAMA
            function applyFilters() {
                const query = searchInput.value.toLowerCase();
                const onlyUnsubmitted = unsubmittedFilter.checked;
                const aslab = aslabFilter.value;
                const laboran = laboranFilter.value;
                const dosen = dosenFilter.value;
                
                let visibleCount = 0;

                cards.forEach(card => {
                    const course = card.getAttribute('data-course');
                    const title = card.getAttribute('data-title');
                    const submitted = card.getAttribute('data-submitted') === 'true';
                    const aslabStat = card.getAttribute('data-aslab');
                    const laboranStat = card.getAttribute('data-laboran');
                    const dosenStat = card.getAttribute('data-dosen');

                    // Pengecekan Pencarian (Berdasarkan nama course atau title tugas)
                    const matchSearch = course.includes(query) || title.includes(query);
                    
                    // Pengecekan Checkbox
                    const matchUnsubmitted = !onlyUnsubmitted || (onlyUnsubmitted && !submitted);
                    
                    // Pengecekan Dropdown (Abaikan jika filter kosong ATAU jika tugas itu tidak relevan dengan filternya misal Dosen pada tugas biasa)
                    const matchAslab = aslab === "" || aslabStat === aslab;
                    const matchLaboran = laboran === "" || laboranStat === laboran;
                    const matchDosen = dosen === "" || dosenStat === dosen || dosenStat === "NONE"; 

                    if (matchSearch && matchUnsubmitted && matchAslab && matchLaboran && matchDosen) {
                        card.style.display = '';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Show or Hide Empty State
                if(cards.length > 0) {
                    emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
                }
            }

            // 2. FUNGSI SORTING KARTU
            window.toggleSort = function() {
                sortDesc = !sortDesc;
                const icon = document.getElementById('sortIcon');
                
                // Animasi Panah
                if(sortDesc) {
                    icon.style.transform = 'rotate(0deg)'; // Terbaru Atas
                } else {
                    icon.style.transform = 'rotate(180deg)'; // Terlama Atas
                }

                // Sort Array Card
                const sortedCards = cards.sort((a, b) => {
                    const timeA = parseInt(a.getAttribute('data-time'));
                    const timeB = parseInt(b.getAttribute('data-time'));
                    return sortDesc ? timeB - timeA : timeA - timeB;
                });

                // Hapus semua kartu dari container (sisakan empty state)
                cards.forEach(card => card.remove());

                // Masukkan kembali kartu yang sudah disortir sebelum empty state
                sortedCards.forEach(card => {
                    tasksContainer.insertBefore(card, emptyState);
                });
            };

            // 3. EVENT LISTENERS
            searchInput.addEventListener('input', applyFilters);
            
            // Saat checkbox diklik, reset filter status
            unsubmittedFilter.addEventListener('change', function() {
                if (this.checked) {
                    aslabFilter.value = "";
                    laboranFilter.value = "";
                    dosenFilter.value = "";
                }
                applyFilters();
            });

            aslabFilter.addEventListener('change', applyFilters);
            laboranFilter.addEventListener('change', applyFilters);
            dosenFilter.addEventListener('change', applyFilters);
        });
    </script>
</x-app-layout>