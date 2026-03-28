<x-app-layout>
    <x-slot name="header_title">
        Kelola Laprak Final: {{ $finalTask->course->name ?? 'Kelas' }}
    </x-slot>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        
        {{-- PANEL PENGATURAN DEADLINE --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
            <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest mb-4">Pengaturan Batas Waktu</h3>
            <form action="{{ route('final-tasks.update-deadline', $finalTask->id) }}" method="POST" class="flex flex-col sm:flex-row items-end gap-4">
                @csrf
                @method('PUT')
                <div class="w-full sm:w-1/3">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Deadline Saat Ini</label>
                    <input type="datetime-local" name="deadline" 
                           value="{{ $finalTask->deadline ? date('Y-m-d\TH:i', strtotime($finalTask->deadline)) : '' }}" 
                           class="w-full rounded-xl border-gray-200 focus:ring-indigo-600 focus:border-indigo-600 text-sm" required>
                </div>
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-slate-900 text-white text-xs font-bold rounded-xl uppercase tracking-widest hover:bg-slate-800 transition">
                    Simpan Deadline
                </button>
            </form>
            @if($finalTask->deadline && now()->gt($finalTask->deadline))
                <p class="mt-3 text-xs font-bold text-red-500 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Waktu pengumpulan telah ditutup. Mahasiswa tidak dapat mengirim tugas baru kecuali deadline diperpanjang.
                </p>
            @endif
        </div>

        {{-- TABEL SUBMISSIONS --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">NIM & Nama</th>
                        <th class="px-6 py-4 text-center text-[10px] font-black text-gray-500 uppercase tracking-widest">Waktu Kumpul</th>
                        <th class="px-6 py-4 text-center text-[10px] font-black text-gray-500 uppercase tracking-widest">Status Aslab</th>
                        <th class="px-6 py-4 text-center text-[10px] font-black text-gray-500 uppercase tracking-widest">Status Laboran</th>
                        <th class="px-6 py-4 text-center text-[10px] font-black text-gray-500 uppercase tracking-widest">Status Dosen</th>
                        <th class="px-6 py-4 text-center text-[10px] font-black text-gray-500 uppercase tracking-widest">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($finalTask->course->students as $student)
                        @php
                            $sub = $submissions[$student->id] ?? null;
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ $student->name }}</div>
                                <div class="text-[10px] font-mono text-gray-500 mt-1">{{ $student->id }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-xs font-medium text-gray-500">
                                {{ $sub ? $sub->last_upload_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            {{-- Status Aslab --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($sub)
                                    <span class="px-3 py-1 text-[9px] font-black rounded-lg uppercase tracking-widest {{ strtoupper($sub->aslab_status) === 'ACC' ? 'bg-emerald-100 text-emerald-700' : (strtoupper($sub->aslab_status) === 'REVISI' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                        {{ $sub->aslab_status }}
                                    </span>
                                @else
                                    <span class="text-[10px] font-bold text-gray-300">-</span>
                                @endif
                            </td>
                            {{-- Status Laboran --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($sub)
                                    <span class="px-3 py-1 text-[9px] font-black rounded-lg uppercase tracking-widest {{ strtoupper($sub->laboran_status) === 'ACC' ? 'bg-emerald-100 text-emerald-700' : (strtoupper($sub->laboran_status) === 'REVISI' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                        {{ $sub->laboran_status }}
                                    </span>
                                @else
                                    <span class="text-[10px] font-bold text-gray-300">-</span>
                                @endif
                            </td>
                            {{-- Status Dosen --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($sub)
                                    <span class="px-3 py-1 text-[9px] font-black rounded-lg uppercase tracking-widest {{ strtoupper($sub->dosen_status) === 'ACC' ? 'bg-emerald-100 text-emerald-700' : (strtoupper($sub->dosen_status) === 'REVISI' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                        {{ $sub->dosen_status ?? 'PENDING' }}
                                    </span>
                                @else
                                    <span class="text-[10px] font-bold text-gray-300">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($sub)
                                    <a href="{{ route('final-tasks.handler', $sub->id) }}" class="inline-flex items-center px-4 py-1.5 bg-indigo-50 text-indigo-600 text-[10px] font-black rounded-lg hover:bg-indigo-600 hover:text-white transition-colors uppercase tracking-widest">
                                        Periksa
                                    </a>
                                @else
                                    <span class="text-[9px] font-bold text-gray-400 uppercase">Belum Kumpul</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>