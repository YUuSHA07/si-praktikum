{{-- Alert Notifikasi --}}
@if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 text-sm font-bold rounded shadow-sm flex items-center animate-bounce">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
        {{ session('success') }}
    </div>
@endif

{{-- Header & Navigasi --}}
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
    <a href="{{ route('courses.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-black flex items-center transition group">
        <svg class="w-4 h-4 mr-1 transform group-hover:-translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        KEMBALI KE DASHBOARD
    </a>

    @if(in_array(strtoupper(Auth::user()->active_role), ['ASLAB', 'LABORAN', 'DOSEN']))
    <div class="flex gap-3">
        @if(!$course->finalTask)
        <button onclick="document.getElementById('modal-final-task').classList.replace('hidden', 'flex')" class="px-4 py-2 bg-indigo-600 text-white text-[10px] font-black rounded-xl uppercase tracking-widest hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
            + Buat Laprak Final
        </button>
        @endif
        <button onclick="openMeetingModal()" class="px-4 py-2 bg-slate-900 text-white text-[10px] font-black rounded-xl uppercase tracking-widest hover:bg-slate-800 transition">
            + Tambah Pertemuan
        </button>
    </div>
    @endif
</div>