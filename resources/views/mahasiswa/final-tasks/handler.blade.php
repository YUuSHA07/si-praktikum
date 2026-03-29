<x-app-layout>
    <x-slot name="header_title">
        Laporan Final: {{ $finalTask->course->course_name }}
    </x-slot>

    {{-- Navigasi Atas --}}
    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('courses.show', $finalTask->course_id) }}" class="text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-indigo-600 transition flex items-center group">
            <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Dashboard
        </a>

        @if($submission)
        <div class="flex items-center gap-4 bg-white px-6 py-2.5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex flex-col border-r border-gray-100 pr-4">
                <span class="text-[7px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Aslab</span>
                <span class="text-[9px] font-black {{ strtoupper($submission->aslab_status) === 'ACC' ? 'text-emerald-500' : 'text-amber-500' }} uppercase leading-none">{{ $submission->aslab_status }}</span>
            </div>
            <div class="flex flex-col border-r border-gray-100 pr-4">
                <span class="text-[7px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Laboran</span>
                <span class="text-[9px] font-black {{ strtoupper($submission->laboran_status) === 'ACC' ? 'text-emerald-500' : 'text-amber-500' }} uppercase leading-none">{{ $submission->laboran_status }}</span>
            </div>
            <div class="flex flex-col">
                <span class="text-[7px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Dosen</span>
                <span class="text-[9px] font-black {{ strtoupper($submission->dosen_status) === 'ACC' ? 'text-emerald-500' : 'text-amber-500' }} uppercase leading-none">{{ $submission->dosen_status }}</span>
            </div>
        </div>
        @endif
    </div>

    {{-- Container Utama --}}
    <div id="main_layout_container" class="flex flex-col lg:flex-row gap-8 h-[calc(100vh-180px)] relative">
        
        {{-- PANEL PREVIEW (Kiri) --}}
        <div id="preview_panel" class="lg:w-2/3 bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden flex flex-col relative transition-all duration-300">
            <div id="preview_header" class="p-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/30">
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 bg-slate-800 text-white text-[9px] font-black rounded-lg uppercase tracking-widest">
                        FINAL
                    </span>
                    <span id="preview_title" class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em]">Document Preview</span>
                </div>
                <div id="compare_badge" class="hidden px-4 py-1.5 bg-amber-500 text-white text-[8px] font-black rounded-full uppercase tracking-widest animate-pulse">
                    Mode Perbandingan Layar Penuh
                </div>
            </div>

            <div id="preview_wrapper" class="flex-1 p-6 bg-slate-50 relative">
                <div id="placeholder_screen" class="absolute inset-0 flex flex-col items-center justify-center text-center p-12">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em]">Menunggu Link G-Drive</p>
                </div>

                {{-- Konten Utama --}}
                <div id="dynamic_content" class="w-full h-full"></div>
            </div>
        </div>

        {{-- PANEL FORM (Kanan) --}}
        <div id="form_panel" class="lg:w-1/3 flex flex-col gap-6 overflow-y-auto pr-2 custom-scrollbar">
            <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">
                @php 
                    // Mengecek apakah ada pihak yang meminta revisi
                    $isRevision = $submission && (strtoupper($submission->aslab_status) == 'REVISI' || strtoupper($submission->laboran_status) == 'REVISI' || strtoupper($submission->dosen_status) == 'REVISI');
                @endphp

                <h3 class="text-xl font-black text-gray-800 tracking-tight uppercase mb-6">Submission</h3>

                <form action="{{ $submission ? route('final-tasks.update', $submission->id) : route('final-tasks.submit', $finalTask->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @if($submission) @method('PUT') @endif
                    <div>
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2.5">Link G-Drive Baru</label>
                        {{-- PERBAIKAN: Menghapus pengecekan $isRevision agar link dari database selalu muncul untuk diedit --}}
                        <input type="url" name="submission_link" id="input_link" required 
                               value="{{ $submission->submission_link ?? '' }}"
                               oninput="handleLivePreview()"
                               class="block w-full rounded-2xl border-gray-100 text-sm font-bold p-4 bg-gray-50 text-indigo-600">
                    </div>
                    <button type="submit" class="w-full py-5 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] shadow-xl {{ $isRevision ? 'bg-red-600' : 'bg-indigo-600' }} text-white">
                        Simpan Perubahan
                    </button>
                </form>
            </div>

            {{-- Riwayat --}}
            @if($submission && $submission->histories && $submission->histories->count() > 0)
            <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">
                <h4 class="text-[9px] font-black text-gray-400 uppercase mb-6 tracking-widest">Log Perubahan</h4>
                <div class="space-y-4">
                    @foreach($submission->histories->sortByDesc('created_at') as $index => $history)
                    <div class="p-5 bg-gray-50 rounded-2xl border border-gray-100">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-[8px] font-black text-red-600 uppercase">Versi #{{ $history->iteration ?? ($submission->histories->count() - $index) }}</span>
                            <button type="button" 
                                    onclick="compareFilesFullscreen('{{ addslashes($history->drive_link) }}')" 
                                    class="text-[8px] font-black text-indigo-600 uppercase tracking-widest hover:underline">
                                Bandingkan
                            </button>
                        </div>
                        <p class="text-[11px] text-gray-600 italic">"{{ $history->feedback ?? 'Tidak ada catatan.' }}"</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <script>
        const dynamicContent = document.getElementById('dynamic_content');
        const placeholder = document.getElementById('placeholder_screen');
        const badge = document.getElementById('compare_badge');
        const inputLink = document.getElementById('input_link');
        const previewPanel = document.getElementById('preview_panel');
        const previewWrapper = document.getElementById('preview_wrapper');

        function extractId(url) {
            if (!url) return null;
            const match = url.match(/(?:\/d\/|id=|document\/d\/)([\w-]+)/);
            return match ? match[1] : null;
        }

        function handleLivePreview() {
            const fileId = extractId(inputLink.value);
            exitFullscreen();

            if (fileId) {
                placeholder.classList.add('hidden');
                dynamicContent.innerHTML = `
                    <iframe src="https://drive.google.com/file/d/${fileId}/preview" 
                            class="w-full h-full rounded-2xl border-none bg-white shadow-sm"></iframe>
                `;
            } else {
                placeholder.classList.remove('hidden');
                dynamicContent.innerHTML = '';
            }
        }

        function compareFilesFullscreen(historyLink) {
            const historyId = extractId(historyLink);
            const currentId = extractId(inputLink.value);

            if (!historyId) return alert("ID File tidak valid!");

            // Masuk Mode Fullscreen (Atas, Bawah, Kiri, Kanan)
            enterFullscreen();

            dynamicContent.innerHTML = `
                <div class="grid grid-cols-2 gap-4 h-full p-2 bg-slate-900">
                    <div class="flex flex-col h-full">
                        <div class="flex justify-between p-2 bg-slate-800 rounded-t-xl border-b border-slate-700">
                            <span class="text-[9px] font-black text-red-400 uppercase tracking-widest">VERSI LAMA (REVISI)</span>
                        </div>
                        <iframe src="https://drive.google.com/file/d/${historyId}/preview" 
                                class="flex-1 w-full border-none bg-white rounded-b-xl"></iframe>
                    </div>
                    <div class="flex flex-col h-full">
                        <div class="flex justify-between p-2 bg-slate-800 rounded-t-xl border-b border-slate-700">
                            <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest">DRAFT BARU</span>
                            <button onclick="handleLivePreview()" class="text-[9px] font-black text-white hover:text-red-400 uppercase">Tutup [X]</button>
                        </div>
                        <iframe src="https://drive.google.com/file/d/${currentId || ''}/preview" 
                                class="flex-1 w-full border-none bg-white rounded-b-xl"></iframe>
                    </div>
                </div>
            `;
        }

        function enterFullscreen() {
            // Paksa preview panel menutupi seluruh viewport (Layar Penuh)
            previewPanel.classList.add('fixed', 'inset-0', 'z-[100]', 'w-screen', 'h-screen', 'rounded-none');
            previewPanel.classList.remove('lg:w-2/3', 'rounded-[2.5rem]');
            
            // Hilangkan padding di wrapper agar iframe mentok ke bawah
            previewWrapper.classList.remove('p-6');
            previewWrapper.classList.add('p-0');
            
            badge.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Matikan scroll body
        }

        function exitFullscreen() {
            previewPanel.classList.remove('fixed', 'inset-0', 'z-[100]', 'w-screen', 'h-screen', 'rounded-none');
            previewPanel.classList.add('lg:w-2/3', 'rounded-[2.5rem]');
            
            previewWrapper.classList.add('p-6');
            previewWrapper.classList.remove('p-0');
            
            badge.classList.add('hidden');
            document.body.style.overflow = 'auto'; // Aktifkan scroll body
        }

        window.onload = () => { if (inputLink.value) handleLivePreview(); };
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 10px; }
        
        /* Memastikan area konten dinamis mengambil 100% tinggi yang tersedia */
        #dynamic_content {
            height: 100%;
            width: 100%;
            display: flex;
            flex-direction: column;
        }
    </style>
</x-app-layout>