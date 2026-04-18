<x-app-layout>
    <x-slot name="header">Manajemen User</x-slot>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- PANEL FILTER & SEARCH --}}
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <form action="{{ route('users.index') }}" method="GET" class="space-y-4">
                    
                    <div class="flex flex-col md:flex-row gap-4 items-end">
                        {{-- Search Input --}}
                        <div class="flex-1 w-full">
                            <label class="block mb-2 text-sm font-medium text-gray-700">Cari Pengguna</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 20 20"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/></svg>
                                </div>
                                <input type="text" name="search" value="{{ request('search') }}" 
                                       class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full ps-10 p-2.5" 
                                       placeholder="ID, Nama, atau Email...">
                            </div>
                        </div>

                        {{-- Limit Select --}}
                        <div class="w-full md:w-32">
                            <label class="block mb-2 text-sm font-medium text-gray-700">Tampilkan</label>
                            <select name="limit" class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                                <option value="10" {{ request('limit') == '10' ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('limit') == '25' ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('limit') == '50' ? 'selected' : '' }}>50</option>
                                <option value="all" {{ request('limit') == 'all' ? 'selected' : '' }}>Semua</option>
                            </select>
                        </div>

                        <button type="submit" class="w-full md:w-auto bg-indigo-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
                            Terapkan Filter
                        </button>
                    </div>

                    {{-- CHECKBOX ROLES --}}
                    <div class="pt-4 border-t border-gray-100">
                        <span class="block mb-2 text-sm font-semibold text-gray-700">Filter Role:</span>
                        <div class="flex flex-wrap gap-6">
                            @foreach(['Mahasiswa', 'Dosen', 'Laboran', 'Aslab'] as $roleName)
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="roles[]" value="{{ $roleName }}" 
                                           {{ in_array($roleName, $selectedRoles) ? 'checked' : '' }}
                                           class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500">
                                    <span class="ms-2 text-sm text-gray-700">{{ $roleName }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </form>
            </div>

            {{-- TABEL SINGLE (SATU UNTUK SEMUA ROLE) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4">Avatar</th>
                                <th class="px-6 py-4 text-nowrap">ID / NIM</th>
                                <th class="px-6 py-4">Nama Pengguna</th>
                                <th class="px-6 py-4">Email</th>
                                <th class="px-6 py-4 text-center">Role</th>
                                <th class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($users as $user)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        @if($user->avatar)
                                            <img src="{{ asset('storage/'.$user->avatar) }}" class="w-10 h-10 rounded-full object-cover shadow-sm border border-gray-100">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-[10px] text-indigo-400 font-bold border border-indigo-100">USER</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 font-mono font-medium text-gray-900">{{ $user->id }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-800">{{ $user->name }}</td>
                                    <td class="px-6 py-4">{{ $user->email }}</td>
                                    <td class="px-6 py-4 text-center text-nowrap">
                                        {{-- Badge Role --}}
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold 
                                            {{ $user->role == 'Mahasiswa' ? 'bg-blue-100 text-blue-700' : 
                                               ($user->role == 'Dosen' ? 'bg-green-100 text-green-700' : 
                                               ($user->role == 'Laboran' ? 'bg-purple-100 text-purple-700' : 'bg-orange-100 text-orange-700')) }}">
                                            {{ $user->role }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <form action="{{ route('users.reset-password', $user->id) }}" method="POST" onsubmit="return confirm('Reset password ke ID default?')">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="text-red-600 hover:bg-red-50 px-3 py-1.5 rounded-md text-xs font-bold border border-red-100 transition">
                                                Reset Password
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 italic">
                                        Tidak ada data user yang sesuai dengan filter Anda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                @if(request('limit') !== 'all' && $users instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="p-4 border-t border-gray-100 bg-gray-50">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>