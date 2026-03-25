<x-app-layout>
    <x-slot name="header_title">
        Detail Praktikum: {{ $course->course_name }}
    </x-slot>

    {{-- Alert Notifikasi --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 text-sm font-bold rounded shadow-sm flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6">
        <a href="{{ route('courses.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-bold flex items-center transition group">
            <svg class="w-4 h-4 mr-1 transform group-hover:-translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Kembali ke Dashboard
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- SIDEBAR: Informasi Praktikum --}}
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                <h3 class="font-black text-gray-800 text-sm uppercase tracking-widest mb-6 border-b pb-2">Informasi Kelas</h3>
                
                <div class="space-y-5">
                    <div class="flex flex-col">
                        <span class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Dosen Pengampu</span>
                        <span class="font-bold text-gray-900 text-sm">{{ $course->dosen->name }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Laboran</span>
                        <span class="font-bold text-gray-900 text-sm">{{ $course->laboran->name ?? '-' }}</span>
                    </div>
                    <div class="flex flex-col pb-4">
                        <span class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Asisten Laboratorium</span>
                        <span class="font-bold text-indigo-600 text-sm">{{ $course->aslab->name }}</span>
                    </div>
                </div>

                @if(auth()->user()->role !== 'Mahasiswa')
                <div class="mt-6 pt-6 border-t border-gray-100 space-y-3">
                    <a href="{{ route('attendance.report', $course->id) }}" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-indigo-50 text-indigo-700 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-indigo-100 transition border border-indigo-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Rekap Presensi
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- MAIN CONTENT: Materi & Pertemuan --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/30">
                    <h3 class="font-black text-gray-800 text-sm uppercase tracking-widest">Materi & Jadwal Pertemuan</h3>
                    @if(in_array(auth()->user()->role, ['Aslab', 'Laboran']))
                        <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                            Tambah Pertemuan
                        </button>
                    @endif
                </div>

                <div class="divide-y divide-gray-50">
                    @forelse($course->meetings->sortBy('meeting_number') as $meeting)
                        @php 
                            // Cari data submission mahasiswa untuk pertemuan ini
                            $sub = $meeting->submissions->where('student_id', auth()->id())->first(); 
                        @endphp
                        <div class="p-6 hover:bg-gray-50/50 transition">
                            <div class="flex flex-col md:flex-row justify-between gap-6">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="px-2 py-0.5 bg-indigo-600 text-white text-[9px] font-black rounded uppercase tracking-widest">
                                            P{{ $meeting->meeting_number }}
                                        </span>
                                        @if(auth()->user()->role === 'Mahasiswa')
                                            @php $attendance = $meeting->attendances->where('student_id', auth()->id())->first(); @endphp
                                            <span class="text-[9px] font-black uppercase tracking-widest {{ $attendance ? 'text-emerald-500' : 'text-gray-400' }}">
                                                {{ $attendance ? '● Hadir' : '○ Belum Presensi' }}
                                            </span>
                                        @endif
                                    </div>
                                    <h4 class="font-bold text-gray-900 text-lg leading-tight">{{ $meeting->title }}</h4>
                                    
                                    @if(auth()->user()->role === 'Mahasiswa' && $sub)
                                    <div class="mt-4 flex flex-wrap gap-4 items-center">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[8px] font-black text-gray-400 uppercase tracking-tighter">Aslab:</span>
                                            <span class="text-[9px] font-bold {{ $sub->aslab_status == 'ACC' ? 'text-emerald-600' : 'text-amber-600' }}">{{ $sub->aslab_status }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[8px] font-black text-gray-400 uppercase tracking-tighter">Laboran:</span>
                                            <span class="text-[9px] font-bold {{ $sub->laboran_status == 'ACC' ? 'text-emerald-600' : 'text-amber-600' }}">{{ $sub->laboran_status }}</span>
                                        </div>
                                        @if($sub->is_completed)
                                            <span class="bg-emerald-500 text-white px-2 py-0.5 rounded text-[8px] font-black uppercase">Verified ACC</span>
                                        @endif
                                    </div>
                                    @endif
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    {{-- Kontrol Khusus Staf --}}
                                    @if(in_array(auth()->user()->role, ['Aslab', 'Laboran']))
                                        <a href="{{ route('attendance.index', $meeting->id) }}" class="px-4 py-2 bg-amber-50 text-amber-700 rounded-lg text-[10px] font-black uppercase tracking-widest border border-amber-200 hover:bg-amber-100 transition text-center">
                                            Presensi
                                        </a>
                                        <a href="{{ route('submissions.index', $meeting->id) }}" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-slate-900 transition flex items-center text-center">
                                            Tugas ({{ $meeting->submissions->count() }})
                                        </a>
                                    @endif

                                    {{-- Kontrol Khusus Mahasiswa --}}
                                    @if(auth()->user()->role === 'Mahasiswa')
                                        @if($sub)
                                            {{-- TOMBOL LIHAT (Tampil jika sudah mengumpul) --}}
                                            <a href="{{ $sub->submission_link }}" target="_blank" class="px-4 py-2 bg-white text-gray-700 rounded-lg text-[10px] font-black uppercase tracking-widest border border-gray-200 hover:bg-gray-50 transition flex items-center text-center">
                                                Lihat Tugas
                                            </a>
                                            {{-- TOMBOL EDIT (Warna Emerald jika sudah mengumpul) --}}
                                            <button onclick="openSubmissionModal('{{ $meeting->id }}', '{{ $meeting->title }}', '{{ $sub->id }}', '{{ $sub->submission_link }}', '{{ addslashes($sub->notes) }}')" 
                                                class="px-4 py-2 bg-emerald-600 shadow-lg shadow-emerald-100 text-white rounded-lg text-[10px] font-black uppercase tracking-widest transition hover:opacity-90 text-center">
                                                Edit Tugas
                                            </button>
                                        @else
                                            {{-- TOMBOL KUMPUL (Warna Indigo jika belum mengumpul) --}}
                                            <button onclick="openSubmissionModal('{{ $meeting->id }}', '{{ $meeting->title }}')" 
                                                class="px-4 py-2 bg-indigo-600 shadow-lg shadow-indigo-100 text-white rounded-lg text-[10px] font-black uppercase tracking-widest transition hover:opacity-90 text-center">
                                                Kumpul Tugas
                                            </button>
                                        @endif
                                    @endif
                                    
                                    @if($meeting->module_drive_link)
                                        <a href="{{ $meeting->module_drive_link }}" target="_blank" class="px-4 py-2 bg-white text-gray-700 rounded-lg text-[10px] font-black uppercase tracking-widest border border-gray-200 hover:bg-gray-50 transition flex items-center text-center">
                                            Modul
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-20 text-center">
                            <p class="text-gray-400 font-bold text-sm uppercase tracking-widest">Belum ada data pertemuan</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: Pengumpulan Tugas Mahasiswa --}}
    <div id="modalSubmission" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
            <form id="formSubmission" method="POST">
                @csrf
                <div id="methodField"></div> {{-- Placeholder untuk @method('PUT') --}}
                
                <div class="p-8 border-b border-gray-100 bg-gray-50/50">
                    <h3 id="modalTitle" class="text-xl font-black text-gray-800 leading-tight">Kumpul Tugas</h3>
                    <p class="text-xs text-gray-500 mt-2 font-medium">Gunakan link Google Drive yang sudah diatur aksesnya menjadi <span class="text-indigo-600 font-bold">"Siapa saja dengan link"</span>.</p>
                </div>
                <div class="p-8 space-y-5">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Link G-Drive / GitHub</label>
                        <input type="url" name="submission_link" id="input_link" required
                               class="block w-full rounded-xl border-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent p-3 bg-gray-50" 
                               placeholder="https://drive.google.com/...">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Pesan Untuk Asisten (Opsional)</label>
                        <textarea name="notes" id="input_notes" rows="3" 
                                  class="block w-full rounded-xl border-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent p-3 bg-gray-50" 
                                  placeholder="Tulis pesan jika ada kendala pengerjaan..."></textarea>
                    </div>
                </div>
                <div class="p-8 bg-gray-50 border-t border-gray-100 flex flex-col gap-3">
                    <button type="submit" id="btnSubmit" class="w-full bg-indigo-600 text-white py-4 rounded-xl text-xs font-black uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition">
                        Kirim Sekarang
                    </button>
                    <button type="button" onclick="closeModal()" class="w-full text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-gray-600 transition">
                        Nanti Saja
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openSubmissionModal(meetingId, title, subId = null, link = '', notes = '') {
            const form = document.getElementById('formSubmission');
            const methodField = document.getElementById('methodField');
            const modalTitle = document.getElementById('modalTitle');
            const btnSubmit = document.getElementById('btnSubmit');
            const inputLink = document.getElementById('input_link');
            const inputNotes = document.getElementById('input_notes');

            // Reset dan Isi data (Pre-fill)
            inputLink.value = link;
            inputNotes.value = (notes !== 'null' && notes !== '') ? notes : '';

            if (subId) {
                // JIKA MODE EDIT
                modalTitle.innerText = 'Edit Tugas: ' + title;
                btnSubmit.innerText = 'Simpan Perubahan';
                btnSubmit.className = "w-full bg-emerald-600 text-white py-4 rounded-xl text-xs font-black uppercase tracking-widest shadow-xl shadow-emerald-100 hover:bg-emerald-700 transition";
                
                // Route update
                form.action = '/submissions/' + subId; 
                methodField.innerHTML = '<input type="hidden" name="_method" value="PUT">';
            } else {
                // JIKA MODE KUMPUL BARU
                modalTitle.innerText = 'Kumpul Tugas: ' + title;
                btnSubmit.innerText = 'Kirim Sekarang';
                btnSubmit.className = "w-full bg-indigo-600 text-white py-4 rounded-xl text-xs font-black uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition";
                
                // Route store
                form.action = '/meetings/' + meetingId + '/submit';
                methodField.innerHTML = '';
            }

            document.getElementById('modalSubmission').classList.replace('hidden', 'flex');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('modalSubmission').classList.replace('flex', 'hidden');
            document.body.style.overflow = 'auto';
        }
    </script>
</x-app-layout>