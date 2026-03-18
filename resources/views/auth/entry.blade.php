<x-guest-layout>
    <div>
        <div class="mb-6 text-center">
            <h1 class="text-xl font-semibold text-white">Доступ до PrintService</h1>
            <p class="mt-1 text-sm text-slate-400">Вхід ліворуч, реєстрація праворуч.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <section class="rounded-xl border border-slate-800 bg-slate-950/70 p-4">
                <h2 class="text-base font-semibold text-white">Вхід</h2>
                <p class="mt-1 text-xs text-slate-400">Для існуючих клієнтів</p>

                <x-auth-session-status class="mt-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="mt-3">
                    @csrf

                    <div>
                        <x-input-label for="login_email" value="Email" class="text-slate-200" />
                        <x-text-input id="login_email" class="block mt-1 w-full bg-slate-950 border-slate-700 text-slate-100 focus:border-cyan-400 focus:ring-cyan-400" type="email" name="email" :value="old('email')" required autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="login_password" value="Пароль" class="text-slate-200" />
                        <x-text-input id="login_password" class="block mt-1 w-full bg-slate-950 border-slate-700 text-slate-100 focus:border-cyan-400 focus:ring-cyan-400" type="password" name="password" required autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="block mt-4">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox" class="rounded border-slate-600 bg-slate-900 text-cyan-500 shadow-sm focus:ring-cyan-500" name="remember">
                            <span class="ms-2 text-sm text-slate-300">Запам'ятати мене</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-between mt-5">
                        @if (Route::has('password.request'))
                            <a class="underline text-sm text-slate-300 hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500" href="{{ route('password.request') }}">Забули пароль?</a>
                        @endif

                        <x-primary-button>Увійти</x-primary-button>
                    </div>
                </form>
            </section>

            <section class="rounded-xl border border-slate-800 bg-slate-950/70 p-4">
                <h2 class="text-base font-semibold text-white">Реєстрація</h2>
                <p class="mt-1 text-xs text-slate-400">Для нових клієнтів</p>

                <form method="POST" action="{{ route('register') }}" class="mt-4">
                    @csrf

                    <div>
                        <x-input-label for="company_name" value="Назва компанії" class="text-slate-200" />
                        <x-text-input id="company_name" class="block mt-1 w-full bg-slate-950 border-slate-700 text-slate-100 focus:border-cyan-400 focus:ring-cyan-400" type="text" name="company_name" :value="old('company_name')" required autocomplete="organization" />
                        <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="tenant_code" value="Код клієнта (необов'язково)" class="text-slate-200" />
                        <x-text-input id="tenant_code" class="block mt-1 w-full bg-slate-950 border-slate-700 text-slate-100 focus:border-cyan-400 focus:ring-cyan-400" type="text" name="tenant_code" :value="old('tenant_code')" autocomplete="off" />
                        <p class="mt-1 text-xs text-slate-400">Якщо не вказати, код згенерується автоматично.</p>
                        <x-input-error :messages="$errors->get('tenant_code')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="name" value="Ваше ім'я" class="text-slate-200" />
                        <x-text-input id="name" class="block mt-1 w-full bg-slate-950 border-slate-700 text-slate-100 focus:border-cyan-400 focus:ring-cyan-400" type="text" name="name" :value="old('name')" required autocomplete="name" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="register_email" value="Email" class="text-slate-200" />
                        <x-text-input id="register_email" class="block mt-1 w-full bg-slate-950 border-slate-700 text-slate-100 focus:border-cyan-400 focus:ring-cyan-400" type="email" name="email" :value="old('email')" required autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="register_password" value="Пароль" class="text-slate-200" />
                        <x-text-input id="register_password" class="block mt-1 w-full bg-slate-950 border-slate-700 text-slate-100 focus:border-cyan-400 focus:ring-cyan-400" type="password" name="password" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="password_confirmation" value="Підтвердіть пароль" class="text-slate-200" />
                        <x-text-input id="password_confirmation" class="block mt-1 w-full bg-slate-950 border-slate-700 text-slate-100 focus:border-cyan-400 focus:ring-cyan-400" type="password" name="password_confirmation" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <div class="flex justify-end mt-5">
                        <x-primary-button>Зареєструватися</x-primary-button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-guest-layout>
