<x-guest-layout>
    <div class="mb-5">
        <h1 class="text-xl font-semibold text-white">Реєстрація клієнта</h1>
        <p class="mt-1 text-sm text-slate-400">Створіть tenant і отримайте доступ до особистого кабінету PrintService.</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="company_name" value="Назва компанії" class="text-slate-200" />
            <x-text-input id="company_name" class="block mt-1 w-full bg-slate-950 border-slate-700 text-slate-100 focus:border-cyan-400 focus:ring-cyan-400" type="text" name="company_name" :value="old('company_name')" required autofocus autocomplete="organization" />
            <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="tenant_code" value="Код клієнта (необов'язково)" class="text-slate-200" />
            <x-text-input id="tenant_code" class="block mt-1 w-full bg-slate-950 border-slate-700 text-slate-100 focus:border-cyan-400 focus:ring-cyan-400" type="text" name="tenant_code" :value="old('tenant_code')" autocomplete="off" />
            <p class="mt-1 text-xs text-slate-400">Використовується в API як tenant_code. Якщо залишити порожнім, згенерується автоматично.</p>
            <x-input-error :messages="$errors->get('tenant_code')" class="mt-2" />
        </div>

        <!-- Name -->
        <div class="mt-4">
            <x-input-label for="name" value="Ваше ім'я" class="text-slate-200" />
            <x-text-input id="name" class="block mt-1 w-full bg-slate-950 border-slate-700 text-slate-100 focus:border-cyan-400 focus:ring-cyan-400" type="text" name="name" :value="old('name')" required autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" value="Email" class="text-slate-200" />
            <x-text-input id="email" class="block mt-1 w-full bg-slate-950 border-slate-700 text-slate-100 focus:border-cyan-400 focus:ring-cyan-400" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Пароль" class="text-slate-200" />

            <x-text-input id="password" class="block mt-1 w-full bg-slate-950 border-slate-700 text-slate-100 focus:border-cyan-400 focus:ring-cyan-400"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

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
            <a class="underline text-sm text-slate-300 hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500" href="{{ route('login') }}">
                Вже є акаунт?
            </a>

            <x-primary-button class="ms-4">
                Зареєструватися
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
