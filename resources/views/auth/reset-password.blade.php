<x-guest-layout>
    <div class="mb-5">
        <h1 class="text-xl font-semibold text-white">Новий пароль</h1>
        <p class="mt-1 text-sm text-slate-400">Встановіть новий пароль для входу.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Email" class="text-slate-200" />
            <x-text-input id="email" class="block mt-1 w-full bg-slate-950 border-slate-700 text-slate-100 focus:border-cyan-400 focus:ring-cyan-400" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Новий пароль" class="text-slate-200" />
            <x-text-input id="password" class="block mt-1 w-full bg-slate-950 border-slate-700 text-slate-100 focus:border-cyan-400 focus:ring-cyan-400" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Підтвердіть пароль" class="text-slate-200" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full bg-slate-950 border-slate-700 text-slate-100 focus:border-cyan-400 focus:ring-cyan-400"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Змінити пароль
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
