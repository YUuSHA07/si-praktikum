<x-app-layout>
    <x-slot name="header_title">
        Detail Kelas: {{ $course->course_name }}
    </x-slot>

    {{-- Navigasi Kembali --}}
    <div class="mb-6">
        <a href="{{ route('courses.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-semibold flex items-center transition">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Kembali ke Daftar Kelas
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Sidebar Informasi Kelas --}}
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                <h3 class="font-bold text-gray-800 text-lg mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Informasi Kelas
                </h3>
                
                <div class="space-y-4 text-sm">
                    <div class="flex justify-between items-center pb-2 border-b border-gray-50">
                        <span class="text-gray-500 font-medium">Grup Kelas</span>
                        <span class="font-bold text-indigo-700 bg-indigo-50 px-2 py-1 rounded text-xs tracking-tighter">{{ $course->class_group }}</span>
                    </div>

                    <div class="flex flex-col pb-2 border-b border-gray-50">
                        <span class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Dosen Pengampu</span>
                        <span class="font-bold text-gray-900">{{ $course->dosen->name }}</span>
                    </div>

                    <div class="flex flex-col pb-2 border-b border-gray-50">
                        <span class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Laboran</span>
                        <span class="font-bold text-gray-900">{{ $course->laboran->name ?? 'N/A' }}</span>
                    </div>

                    <div class="flex flex-col pb-2 border-b border-gray-50">
                        <span class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Asisten Laboratorium</span>
                        <span class="font-bold text-gray-900">{{ $course->aslab->name }}</span>
                    </div>

                    <div class="flex justify-between items-center pt-2">
                        <span class="text-gray-500 font-medium">Total Pertemuan</span>
                        <span class="font-bold text-indigo-600 bg-indigo-50 h-7 w-7 flex items-center justify-center rounded-full text-xs">
                            {{ $course->meetings->count() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Utama: Daftar Pertemuan --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="font-bold text-gray-800 text-lg">Materi & Pertemuan</h3>
                    
                    {{-- Tombol Tambah Hanya untuk Aslab/Laboran --}}
                    @if(in_array(auth()->user()->role, ['Aslab', 'Laboran']))
                        <button onclick="toggleModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-indigo-700 transition shadow-sm flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Tambah Pertemuan
                        </button>
                    @endif
                </div>

                <div class="p-6 space-y-4">
                    @forelse($course->meetings->sortBy('meeting_number') as $meeting)
                        <div class="p-5 border border-gray-100 rounded-xl hover:shadow-md transition bg-white flex flex-col md:flex-row justify-between md:items-center gap-4 border-l-4 border-l-indigo-500">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 text-[10px] font-black rounded uppercase tracking-wider">
                                        Pertemuan {{ $meeting->meeting_number }}
                                    </span>
                                </div>
                                <h4 class="font-bold text-gray-800 text-lg leading-tight">{{ $meeting->title }}</h4>
                                
                                @if($meeting->description)
                                    <p class="text-sm text-gray-500 mt-1 line-clamp-1 italic">"{{ $meeting->description }}"</p>
                                @endif

                                @if($meeting->deadline)
                                <p class="text-[11px] text-red-500 mt-2 flex items-center font-bold">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Deadline: {{ \Carbon\Carbon::parse($meeting->deadline)->translatedFormat('d F Y, H:i') }} WIB
                                </p>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                @if($meeting->module_drive_link)
                                <a href="{{ $meeting->module_drive_link }}" target="_blank" class="flex-1 md:flex-none px-4 py-2 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-bold border border-emerald-200 hover:bg-emerald-100 transition text-center flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                    Modul
                                </a>
                                @endif
                                
                                <button class="flex-1 md:flex-none px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700 transition text-center shadow-sm">
                                    Tugas
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-20">
                            <div class="bg-gray-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                            </div>
                            <p class="text-gray-400 font-medium italic uppercase text-xs tracking-widest">Materi belum tersedia</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Tambah Pertemuan --}}
    @if(in_array(auth()->user()->role, ['Aslab', 'Laboran']))
    <div id="modalMeeting" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-xl font-bold text-gray-800 tracking-tight">Tambah Pertemuan</h3>
                <button onclick="toggleModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form action="{{ route('meetings.store', $course->id) }}" method="POST" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div class="grid grid-cols-4 gap-4">
                        <div class="col-span-1">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Ke-</label>
                            <input type="number" name="meeting_number" min="1" max="16" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="1" required>
                        </div>
                        <div class="col-span-3">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Judul Materi</label>
                            <input type="text" name="title" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Contoh: Pengenalan PHP" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Deskripsi Singkat</label>
                        <textarea name="description" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Isi materi secara garis besar..."></textarea>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Link Modul (Google Drive)</label>
                        <input type="url" name="module_drive_link" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="https://drive.google.com/...">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Batas Waktu Tugas (Deadline)</label>
                        <input type="datetime-local" name="deadline" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <button type="button" onclick="toggleModal()" class="px-4 py-2 text-sm font-bold text-gray-400 hover:text-gray-600 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 transition shadow-lg">
                        Simpan Materi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleModal() {
            const modal = document.getElementById('modalMeeting');
            modal.classList.toggle('hidden');
            modal.classList.toggle('flex');
        }
    </script>
    @endif
</x-app-layout>