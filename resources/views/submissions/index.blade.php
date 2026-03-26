<x-app-layout>
    <x-slot name="header_title">Review: {{ $meeting->title }}</x-slot>

    <div class="max-w-[95rem] mx-auto py-8 px-4">
        
        {{-- STATS OVERVIEW --}}
        @php
            $totalStudents = $meeting->course->students->count();
            $submitted = count($submissions);
            $accCount = collect($submissions)->where('aslab_status', 'ACC')->count();
            $revisiCount = collect($submissions)->where('aslab_status', 'Revisi')->count();
            $belumMengumpul = $totalStudents - $submitted;
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Mahasiswa</p>
                <h4 class="text-2xl font-black text-gray-800">{{ $totalStudents }}</h4>
            </div>
            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm border-l-4 border-l-red-500">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Belum Mengumpul</p>
                <h4 class="text-2xl font-black text-red-600">{{ $belumMengumpul }}</h4>
            </div>
            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm border-l-4 border-l-amber-500">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Revisi / Pending</p>
                <h4 class="text-2xl font-black text-amber-600">{{ $revisiCount + ($submitted - ($accCount + $revisiCount)) }}</h4>
            </div>
            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm border-l-4 border-l-emerald-500">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Selesai (ACC)</p>
                <h4 class="text-2xl font-black text-emerald-600">{{ $accCount }}</h4>
            </div>
        </div>

        {{-- TABEL UTAMA --}}
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50/50 text-[10px] uppercase font-black text-gray-400 border-b border-gray-100">
                    <tr>
                        <th class="px-8 py-5">Mahasiswa</th>
                        <th class="px-8 py-5">Waktu Pengumpulan</th>
                        <th class="px-8 py-5 text-center">Riwayat</th>
                        <th class="px-8 py-5 text-center">Status</th>
                        <th class="px-8 py-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($meeting->course->students as $student)
                        @php $sub = $submissions[$student->id] ?? null; @endphp
                        <tr class="hover:bg-gray-50/50 transition-all group">
                            <td class="px-8 py-5 text-sm">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 font-black text-[10px]">
                                        {{ strtoupper(substr($student->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <span class="text-gray-800 font-black block leading-none mb-1">{{ $student->name }}</span>
                                        <span class="text-[10px] text-gray-400 font-mono italic">NIM: {{ $student->id }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                @if($sub)
                                    <div class="flex flex-col text-[11px] font-black">
                                        <span class="text-gray-700">{{ $sub->last_upload_at->format('d M Y') }}</span>
                                        <span class="text-gray-400 uppercase tracking-widest">{{ $sub->last_upload_at->format('H:i') }} WIB</span>
                                    </div>
                                @else
                                    <span class="text-[10px] text-gray-300 font-black italic">—</span>
                                @endif
                            </td>
                            <td class="px-8 py-5 text-center">
                                @if($sub && $sub->histories->count() > 0)
                                    <span class="px-2 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[9px] font-black uppercase border border-indigo-100">
                                        {{ $sub->histories->count() }}x Revisi
                                    </span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-8 py-5 text-center">
                                @if(!$sub)
                                    <span class="px-4 py-2 bg-gray-50 text-gray-400 rounded-2xl text-[9px] font-black uppercase border border-dashed border-gray-200">Belum Kumpul</span>
                                @else
                                    @php
                                        // Gunakan strtoupper untuk keamanan perbandingan string
                                        $currentStatus = strtoupper($sub->aslab_status);
                                        $statusClass = match($currentStatus) {
                                            'ACC'    => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                            'REVISI' => 'bg-red-50 text-red-600 border-red-100',
                                            default  => 'bg-amber-50 text-amber-600 border-amber-100',
                                        };
                                    @endphp
                                    <span class="px-4 py-2 {{ $statusClass }} rounded-2xl text-[9px] font-black uppercase border shadow-sm">
                                        {{ $sub->aslab_status }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-8 py-5 text-right">
                                @if($sub)
                                    <button onclick="openReviewModal('{{ $sub->id }}', '{{ $sub->submission_link }}', '{{ $student->name }}', '{{ $sub->notes }}', {{ $sub->histories->toJson() }})" 
                                        class="bg-indigo-600 text-white px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 shadow-xl shadow-indigo-100 active:scale-95 transition-all">
                                        Review
                                    </button>
                                @else
                                    <button disabled class="bg-gray-100 text-gray-300 px-6 py-3 rounded-2xl text-[10px] font-black uppercase cursor-not-allowed italic">Menunggu</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL REVIEW TRIPLE PANE (SPLIT VIEW) --}}
    <div id="modalReview" class="fixed inset-0 bg-slate-900/95 backdrop-blur-md hidden z-50 p-4 md:p-6 flex flex-col items-center">
        <div class="bg-white rounded-[3rem] w-full max-w-[98rem] h-full flex flex-col overflow-hidden shadow-2xl">
            
            {{-- Header Modal --}}
            <div class="p-6 border-b flex justify-between items-center bg-gray-50/50">
                <div class="flex items-center gap-4">
                    <div class="bg-indigo-600 p-3 rounded-2xl text-white shadow-lg shadow-indigo-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div>
                        <h3 id="reviewStudentName" class="text-xl font-black text-gray-800 tracking-tight">Review Laporan</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em] mt-0.5">Sistem Perbandingan Revisi Aktif</p>
                    </div>
                </div>
                <button onclick="closeReviewModal()" class="p-3 bg-white text-gray-400 rounded-full hover:bg-red-50 hover:text-red-500 transition shadow-sm border border-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="flex-1 flex flex-col md:flex-row overflow-hidden">
                {{-- KIRI: Container Preview (Single atau Split) --}}
                <div id="previewWrapper" class="flex-[3] bg-slate-200 relative overflow-hidden transition-all duration-500">
                    {{-- Iframe di-render via JS --}}
                </div>

                {{-- KANAN: Panel Sidebar Review --}}
                <div class="flex-1 bg-white p-8 overflow-y-auto flex flex-col border-l border-gray-100">
                    {{-- Pesan Mahasiswa --}}
                    <div id="studentNotesWrapper" class="mb-10 hidden">
                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 flex items-center">
                            <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full mr-2"></span> Pesan Mahasiswa
                        </h4>
                        <div class="p-5 bg-indigo-50 border border-indigo-100 rounded-[1.5rem] relative">
                            <p id="studentNotes" class="text-xs text-indigo-900 italic leading-relaxed font-bold"></p>
                        </div>
                    </div>

                    {{-- Riwayat Revisi --}}
                    <div class="mb-10">
                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-5 flex items-center">
                            <span class="w-1.5 h-1.5 bg-slate-400 rounded-full mr-2"></span> Riwayat Revisi
                        </h4>
                        <div id="historyList" class="space-y-3">
                            {{-- Diisi JS --}}
                        </div>
                    </div>

                    {{-- Aksi --}}
                    <div class="mt-auto pt-8 border-t border-gray-50">
                        <a id="commentLink" href="#" target="_blank" class="w-full flex items-center justify-center py-5 bg-amber-500 text-white rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest mb-4 hover:bg-amber-600 transition shadow-xl shadow-amber-100">
                            Buka G-Drive & Komentari
                        </a>
                        <form id="formApprove" method="POST" class="grid grid-cols-2 gap-4">
                            @csrf
                            {{-- Value diganti jadi REVISI (Kapital) --}}
                            <button type="submit" name="status" value="REVISI" class="py-5 bg-red-50 text-red-600 rounded-[1.5rem] font-black text-[10px] uppercase hover:bg-red-100 transition border border-red-100">Revisi</button>
                            <button type="submit" name="status" value="ACC" class="py-5 bg-emerald-600 text-white rounded-[1.5rem] font-black text-[10px] uppercase hover:bg-emerald-700 shadow-xl shadow-emerald-100 transition">Terima (ACC)</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- State Storage --}}
    <input type="hidden" id="currentFileUrl">

    <script>
        const previewWrapper = document.getElementById('previewWrapper');
        const currentFileStorage = document.getElementById('currentFileUrl');

        function openReviewModal(subId, driveUrl, studentName, notes, histories) {
            // Setup Info & URL
            document.getElementById('reviewStudentName').innerText = studentName;
            document.getElementById('formApprove').action = `/submissions/${subId}/approve`;
            
            const previewUrl = driveUrl.split('?')[0].replace('/view', '/preview');
            const editUrl = driveUrl.split('?')[0].replace('/view', '/edit');
            
            document.getElementById('commentLink').href = editUrl;
            currentFileStorage.value = previewUrl;

            // 1. Tampilkan Pesan
            const notesWrapper = document.getElementById('studentNotesWrapper');
            if (notes && notes !== '' && notes !== 'null') {
                notesWrapper.classList.remove('hidden');
                document.getElementById('studentNotes').innerText = `"${notes}"`;
            } else {
                notesWrapper.classList.add('hidden');
            }

            // 2. Render History dengan tombol Bandingkan
            const historyContainer = document.getElementById('historyList');
            historyContainer.innerHTML = '';
            
            if (histories.length > 0) {
                histories.forEach((h) => {
                    const hUrl = h.drive_link.split('?')[0].replace('/view', '/preview');
                    historyContainer.innerHTML += `
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100 group transition-all hover:border-indigo-200">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-black text-indigo-600 uppercase tracking-tighter">Versi ${h.iteration}</span>
                                <span class="text-[9px] text-gray-400 font-bold">${new Date(h.created_at).toLocaleDateString('id-ID')}</span>
                            </div>
                            <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-all transform translate-x-2 group-hover:translate-x-0">
                                <button onclick="compareRevision('${hUrl}')" class="px-3 py-1.5 bg-amber-100 text-amber-700 rounded-lg text-[9px] font-black uppercase">Bandingkan</button>
                                <a href="${h.drive_link}" target="_blank" class="px-3 py-1.5 bg-white text-blue-600 rounded-lg text-[9px] font-black border border-blue-50 uppercase">Lihat</a>
                            </div>
                        </div>
                    `;
                });
            } else {
                historyContainer.innerHTML = '<p class="text-[10px] text-gray-300 italic text-center py-4">Tidak ada riwayat revisi.</p>';
            }

            // 3. Load Preview Utama
            resetToSingleView();
            
            document.getElementById('modalReview').classList.replace('hidden', 'flex');
            document.body.style.overflow = 'hidden';
        }

        function compareRevision(historyUrl) {
            const currentUrl = currentFileStorage.value;
            previewWrapper.innerHTML = `
                <div class="flex h-full w-full divide-x-4 divide-white">
                    {{-- Sisi Kiri: Terbaru --}}
                    <div class="flex-1 relative bg-slate-100 animate-in fade-in duration-500">
                        <div class="absolute top-4 left-4 bg-indigo-600 text-[9px] text-white px-3 py-1.5 rounded-xl font-black z-10 uppercase tracking-widest shadow-lg">File Terbaru (Current)</div>
                        <iframe src="${currentUrl}" class="w-full h-full border-none"></iframe>
                    </div>
                    {{-- Sisi Kanan: Versi Lama --}}
                    <div class="flex-1 relative bg-slate-100 animate-in slide-in-from-right duration-500">
                        <div class="absolute top-4 left-4 bg-amber-500 text-[9px] text-white px-3 py-1.5 rounded-xl font-black z-10 uppercase tracking-widest shadow-lg">Versi Lama (History)</div>
                        <button onclick="resetToSingleView()" class="absolute top-4 right-4 bg-white/90 p-2 rounded-full shadow-lg hover:text-red-500 z-20 transition active:scale-90">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                        <iframe src="${historyUrl}" class="w-full h-full border-none"></iframe>
                    </div>
                </div>
            `;
        }

        function resetToSingleView() {
            const currentUrl = currentFileStorage.value;
            previewWrapper.innerHTML = `<iframe id="mainDriveFrame" src="${currentUrl}" class="w-full h-full border-none animate-in fade-in duration-300"></iframe>`;
        }

        function closeReviewModal() {
            document.getElementById('modalReview').classList.replace('flex', 'hidden');
            previewWrapper.innerHTML = '';
            document.body.style.overflow = 'auto';
        }
    </script>
</x-app-layout>