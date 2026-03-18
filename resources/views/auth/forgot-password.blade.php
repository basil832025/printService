<x-guest-layout>
    <div class="mb-5">
        <h1 class="text-xl font-semibold text-white">Відновлення пароля</h1>
    </div>

    <div class="mb-4 text-sm text-slate-300">
        Вкажіть email, і ми надішлемо посилання для скидання пароля.
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Email" class="text-slate-200" />
            <x-text-input id="email" class="block mt-1 w-full bg-slate-950 border-slate-700 text-slate-100 focus:border-cyan-400 focus:ring-cyan-400" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Надіслати посилання
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
