<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Keamanan Akun') }}
        </h2>
    </x-slot>

    <div class="min-h-[70vh] flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            
            <div class="p-4">
                <p class="mb-6 text-sm text-gray-600 text-center">
                    Halo <strong>{{ Auth::user()->name }}</strong>, silakan ganti password default Anda untuk keamanan akun.
                </p>

                <form method="POST" action="{{ route('first.login.update') }}">
                    @csrf

                    <div>
                        <x-input-label for="password" :value="__('Password Baru')" />
                        <x-text-input id="password" class="block mt-1 w-full" 
                                        type="password" 
                                        name="password" 
                                        required 
                                        autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="password_confirmation" :value="__('Konfirmasi Password Baru')" />
                        <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                        type="password"
                                        name="password_confirmation" 
                                        required />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <div class="mt-6">
                        <x-primary-button class="w-full justify-center">
                            {{ __('Perbarui & Masuk Dashboard') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>