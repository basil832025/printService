<x-guest-layout>
    <div class="mb-5">
        <h1 class="text-xl font-semibold text-white">Підтвердження доступу</h1>
    </div>

    <div class="mb-4 text-sm text-slate-300">
        Це захищена зона. Підтвердіть ваш пароль, щоб продовжити.
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Пароль" class="text-slate-200" />

            <x-text-input id="password" class="block mt-1 w-full bg-slate-950 border-slate-700 text-slate-100 focus:border-cyan-400 focus:ring-cyan-400"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button>
                Підтвердити
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
