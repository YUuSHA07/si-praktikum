<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
    <h3 class="text-lg font-bold mb-2">Halo, Laboran!</h3>
    <p class="text-gray-600 mb-6">Halaman khusus pengelolaan data laboran.</p>
    
    <hr class="my-4">

    <a href="{{ route('user.import.form') }}" 
       class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
        Ke Halaman Import User
    </a>

    <a href="{{ route('users.index') }}" 
       class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
        Ke Halaman Manajemen User
    </a>
</div>