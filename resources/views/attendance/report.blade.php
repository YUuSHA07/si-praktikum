<x-app-layout>
    <x-slot name="header_title">
        Rekap Presensi: {{ $course->course_name }}
    </x-slot>

    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('courses.show', $course->id) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-semibold flex items-center transition">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Kembali ke Detail Kelas
        </a>
        <button onclick="window.print()" class="bg-gray-800 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-gray-900 transition flex items-center shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 00-2 2h2m2 4h10a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2z"></path></svg>
            Cetak Laporan
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-[10px] uppercase tracking-widest text-gray-400 font-black border-b border-gray-100">
                        <th class="px-6 py-4">Mahasiswa</th>
                        <th class="px-6 py-4 text-center">H</th>
                        <th class="px-6 py-4 text-center">S</th>
                        <th class="px-6 py-4 text-center">I</th>
                        <th class="px-6 py-4 text-center">A</th>
                        <th class="px-6 py-4 text-center">Persentase</th>
                        <th class="px-6 py-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($report as $data)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-800 text-sm">{{ $data->name }}</span>
                                <span class="text-[10px] text-gray-400 font-mono">{{ $data->id }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center text-sm font-semibold text-emerald-600">{{ $data->hadir }}</td>
                        <td class="px-6 py-4 text-center text-sm font-semibold text-blue-600">{{ $data->sakit }}</td>
                        <td class="px-6 py-4 text-center text-sm font-semibold text-amber-600">{{ $data->izin }}</td>
                        <td class="px-6 py-4 text-center text-sm font-semibold text-red-600">{{ $data->alpha }}</td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-16 bg-gray-100 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full {{ $data->percentage >= 75 ? 'bg-emerald-500' : 'bg-red-500' }}" style="width: {{ $data->percentage }}%"></div>
                                </div>
                                <span class="text-[11px] font-bold text-gray-700">{{ $data->percentage }}%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($data->percentage >= 75)
                                <span class="px-2 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-black rounded uppercase">Aman</span>
                            @else
                                <span class="px-2 py-1 bg-red-50 text-red-700 text-[10px] font-black rounded uppercase">Peringatan</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>