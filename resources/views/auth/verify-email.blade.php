<x-guest-layout>
    <div class="mb-5">
        <h1 class="text-xl font-semibold text-white">Підтвердження email</h1>
    </div>

    <div class="mb-4 text-sm text-slate-300">
        Дякуємо за реєстрацію. Підтвердьте email через посилання, яке ми надіслали. Якщо лист не прийшов — відправимо ще раз.
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-emerald-400">
            Нове посилання для підтвердження відправлено на вашу пошту.
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    Надіслати ще раз
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-slate-300 hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500">
                Вийти
            </button>
        </form>
    </div>
</x-guest-layout>
