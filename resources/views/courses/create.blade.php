<x-app-layout>
    <x-slot name="header_title">
        Buat Kelas Praktikum
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <form action="{{ route('courses.store') }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Nama Mata Kuliah</label>
                        <input type="text" name="course_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Contoh: Jaringan Syaraf Tiruan" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kelas</label>
                        <input type="text" name="class_group" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Contoh: IK-1" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Semester (Mahasiswa)</label>
                        <input type="number" name="target_semester" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Contoh: 5" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Dosen Pengampu</label>
                        <select name="dosen_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                            <option value="">Pilih Dosen</option>
                            @foreach($dosens as $dosen)
                                <option value="{{ $dosen->id }}">{{ $dosen->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Asisten Laboratorium</label>
                        <select name="aslab_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                            <option value="">Pilih Asisten</option>
                            @foreach($aslabs as $aslab)
                                <option value="{{ $aslab->id }}">{{ $aslab->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" 
                            onclick="this.disabled=true;this.form.submit();" 
                            class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Simpan & Generate Kode
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>