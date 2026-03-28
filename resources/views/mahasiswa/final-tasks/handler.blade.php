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
    <div id="main_layout_container" class="flex flex-col lg:flex-row gap-8 h-[calc(100vh-200px)] relative">
        
        {{-- PANEL PREVIEW (Kiri) --}}
        <div id="preview_panel" class="lg:w-2/3 bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden flex flex-col relative transition-all duration-300">
            <div id="preview_header" class="p-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/30">
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 bg-indigo-600 text-white text-[9px] font-black rounded-lg uppercase tracking-widest">
                        FINAL
                    </span>
                    <span id="preview_title" class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em]">Laporan Preview</span>
                </div>
                <div id="compare_badge" class="hidden px-4 py-1.5 bg-amber-500 text-white text-[8px] font-black rounded-full uppercase tracking-widest animate-pulse">
                    Mode Perbandingan Aktif
                </div>
            </div>

            <div id="preview_wrapper" class="flex-1 p-6 bg-slate-50 relative">
                <div id="placeholder_screen" class="absolute inset-0 flex flex-col items-center justify-center text-center p-12">
                    <div class="w-16 h-16 bg-white rounded-3xl flex items-center justify-center shadow-sm mb-4">
                        <svg class="w-8 h-8 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em]">Masukkan Link G-Drive untuk Preview</p>
                </div>

                {{-- Konten Utama --}}
                <div id="dynamic_content" class="w-full h-full"></div>
            </div>
        </div>

        {{-- PANEL FORM (Kanan) --}}
        <div id="form_panel" class="lg:w-1/3 flex flex-col gap-6 overflow-y-auto pr-2 custom-scrollbar">
            
            {{-- Box Input --}}
            <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm relative overflow-hidden">
                @php 
                    $isExpired = $finalTask->deadline && now()->gt($finalTask->deadline);
                    $isCompleted = $submission && $submission->is_completed;
                    $isRevision = $submission && (strtoupper($submission->aslab_status) == 'REVISI' || strtoupper($submission->laboran_status) == 'REVISI' || strtoupper($submission->dosen_status) == 'REVISI');
                @endphp

                {{-- Overlay jika selesai --}}
                @if($isCompleted)
                <div class="absolute inset-0 bg-white/80 backdrop-blur-sm z-10 flex flex-col items-center justify-center text-center p-6">
                    <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h4 class="font-black text-gray-800 text-xs uppercase tracking-widest">Selesai & ACC</h4>
                </div>
                @endif

                <h3 class="text-xl font-black text-gray-800 tracking-tight uppercase mb-6">Submission Final</h3>

                <form action="{{ route('final-tasks.submit', $finalTask->id) }}" method="POST" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2.5">Link G-Drive Laporan</label>
                        <input type="url" name="submission_link" id="input_link" required 
                               value="{{ old('submission_link', $submission->submission_link ?? '') }}"
                               oninput="handleLivePreview()"
                               class="block w-full rounded-2xl border-gray-100 text-sm font-bold p-4 bg-gray-50 text-indigo-600 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 transition-all outline-none"
                               placeholder="https://drive.google.com/...">
                    </div>

                    <div>
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2.5">Catatan (Opsional)</label>
                        <textarea name="notes" rows="3" 
                                  class="w-full rounded-2xl border-gray-100 bg-gray-50 text-sm font-medium p-4 focus:ring-4 focus:ring-indigo-50 outline-none resize-none" 
                                  placeholder="Pesan untuk Dosen/Aslab...">{{ old('notes', $submission->notes ?? '') }}</textarea>
                    </div>

                    <button type="submit" class="w-full py-5 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] shadow-xl {{ $isRevision ? 'bg-red-600 shadow-red-100' : 'bg-indigo-600 shadow-indigo-100' }} text-white hover:opacity-90 transition active:scale-95">
                        {{ $submission ? 'Perbarui Laporan Final' : 'Kumpul Laporan Final' }}
                    </button>
                </form>
            </div>

            {{-- Riwayat Revisi --}}
            @if($submission && $submission->histories && $submission->histories->count() > 0)
            <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">
                <h4 class="text-[9px] font-black text-gray-400 uppercase mb-6 tracking-widest flex items-center gap-2">
                    <span class="w-1.5 h-3 bg-indigo-600 rounded-full"></span> Log Perubahan & Feedback
                </h4>
                <div class="space-y-4">
                    @foreach($submission->histories->sortByDesc('created_at') as $index => $history)
                    <div class="p-5 bg-gray-50 rounded-2xl border border-gray-100 group">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-[8px] font-black text-indigo-600 uppercase">Versi #{{ $history->iteration ?? ($submission->histories->count() - $index) }}</span>
                            <button type="button" 
                                    onclick="compareFilesFullscreen('{{ $history->drive_link }}')" 
                                    class="text-[8px] font-black text-slate-400 group-hover:text-indigo-600 uppercase tracking-widest transition flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                Bandingkan
                            </button>
                        </div>
                        <p class="text-[11px] text-gray-600 italic leading-relaxed">"{{ $history->feedback ?? 'Tidak ada catatan.' }}"</p>
                        <div class="mt-2 text-[7px] text-gray-400 font-bold uppercase tracking-widest">
                            {{ $history->created_at->format('d M Y • H:i') }}
                        </div>
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

            if (!historyId) return alert("ID File riwayat tidak ditemukan!");

            enterFullscreen();

            dynamicContent.innerHTML = `
                <div class="grid grid-cols-2 gap-4 h-full p-2 bg-slate-900">
                    <div class="flex flex-col h-full">
                        <div class="flex justify-between p-3 bg-slate-800 rounded-t-2xl border-b border-slate-700">
                            <span class="text-[9px] font-black text-red-400 uppercase tracking-widest">VERSI LAMA (REVISI)</span>
                        </div>
                        <iframe src="https://drive.google.com/file/d/${historyId}/preview" 
                                class="flex-1 w-full border-none bg-white rounded-b-2xl"></iframe>
                    </div>
                    <div class="flex flex-col h-full">
                        <div class="flex justify-between p-3 bg-slate-800 rounded-t-2xl border-b border-slate-700">
                            <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest">DRAFT TERBARU ANDA</span>
                            <button onclick="handleLivePreview()" class="text-[9px] font-black text-white hover:text-red-400 uppercase tracking-widest flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                Tutup
                            </button>
                        </div>
                        <iframe src="https://drive.google.com/file/d/${currentId || ''}/preview" 
                                class="flex-1 w-full border-none bg-white rounded-b-2xl"></iframe>
                    </div>
                </div>
            `;
        }

        function enterFullscreen() {
            previewPanel.classList.add('fixed', 'inset-0', 'z-[100]', 'w-screen', 'h-screen', 'rounded-none');
            previewPanel.classList.remove('lg:w-2/3', 'rounded-[2.5rem]');
            previewWrapper.classList.remove('p-6');
            previewWrapper.classList.add('p-0');
            badge.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function exitFullscreen() {
            previewPanel.classList.remove('fixed', 'inset-0', 'z-[100]', 'w-screen', 'h-screen', 'rounded-none');
            previewPanel.classList.add('lg:w-2/3', 'rounded-[2.5rem]');
            previewWrapper.classList.add('p-6');
            previewWrapper.classList.remove('p-0');
            badge.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        window.onload = () => { if (inputLink.value) handleLivePreview(); };
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
        #dynamic_content { height: 100%; width: 100%; display: flex; flex-direction: column; }
    </style>
</x-app-layout>