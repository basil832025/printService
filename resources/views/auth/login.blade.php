<x-guest-layout>
    <div class="mb-5">
        <h1 class="text-xl font-semibold text-white">Вхід до кабінету</h1>
        <p class="mt-1 text-sm text-slate-400">Керуйте агентами, ключами та чергою друку.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Email" class="text-slate-200" />
            <x-text-input id="email" class="block mt-1 w-full bg-slate-950 border-slate-700 text-slate-100 focus:border-cyan-400 focus:ring-cyan-400" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Пароль" class="text-slate-200" />

            <x-text-input id="password" class="block mt-1 w-full bg-slate-950 border-slate-700 text-slate-100 focus:border-cyan-400 focus:ring-cyan-400"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-600 bg-slate-900 text-cyan-500 shadow-sm focus:ring-cyan-500" name="remember">
                <span class="ms-2 text-sm text-slate-300">Запам'ятати мене</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-slate-300 hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500" href="{{ route('password.request') }}">
                    Забули пароль?
                </a>
            @endif

            <x-primary-button class="ms-3">
                Увійти
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
