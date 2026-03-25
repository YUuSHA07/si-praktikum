<x-app-layout>
    <x-slot name="header_title">
        Rekapitulasi Tugas Saya
    </x-slot>

    <div class="mb-8">
        <h2 class="text-2xl font-black text-gray-800">Semua Tugas</h2>
        <p class="text-sm text-gray-500 font-medium">Pantau status ACC dan riwayat pengumpulan Anda di sini.</p>
    </div>

    <div class="grid grid-cols-1 gap-6">
        @forelse($submissions as $sub)
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 flex flex-col md:flex-row justify-between items-center gap-6 hover:shadow-md transition">
                
                {{-- Info Praktikum --}}
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="px-3 py-1 bg-indigo-50 text-indigo-600 text-[9px] font-black rounded-full uppercase tracking-widest">
                            {{ $sub->meeting->course->course_name }}
                        </span>
                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">
                            Pertemuan {{ $sub->meeting->meeting_number }}
                        </span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $sub->meeting->title }}</h3>
                    <p class="text-[10px] text-gray-400 font-bold uppercase mt-1">Update Terakhir: {{ $sub->last_upload_at->format('d M Y, H:i') }} WIB</p>
                </div>

                {{-- Status Badges --}}
                <div class="flex flex-wrap gap-4 items-center justify-center md:justify-end w-full md:w-auto">
                    <div class="text-center md:text-right">
                        <span class="block text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">Status Aslab</span>
                        <span class="px-4 py-2 rounded-xl text-[10px] font-black uppercase {{ $sub->aslab_status == 'ACC' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">
                            {{ $sub->aslab_status }}
                        </span>
                    </div>

                    <div class="text-center md:text-right border-l border-gray-100 pl-4">
                        <span class="block text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">Status Laboran</span>
                        <span class="px-4 py-2 rounded-xl text-[10px] font-black uppercase {{ $sub->laboran_status == 'ACC' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">
                            {{ $sub->laboran_status }}
                        </span>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex gap-2 ml-4">
                        <a href="{{ $sub->submission_link }}" target="_blank" class="p-4 bg-slate-50 text-slate-400 rounded-2xl hover:bg-slate-100 transition group">
                            <svg class="w-5 h-5 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        </a>
                        <a href="{{ route('courses.show', $sub->meeting->course_id) }}" class="px-6 py-4 bg-indigo-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition">
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
                <h3 class="text-lg font-bold text-gray-400 uppercase tracking-widest">Belum ada tugas yang dikumpul</h3>
                <p class="text-sm text-gray-400 mt-2">Tugas yang Anda kirim akan muncul secara otomatis di sini.</p>
            </div>
        @endforelse
    </div>
</x-app-layout>