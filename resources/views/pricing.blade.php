<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ціни - PrintService</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
    <div class="relative min-h-screen overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_12%_8%,rgba(56,189,248,0.20),transparent_36%),radial-gradient(circle_at_85%_0%,rgba(16,185,129,0.16),transparent_33%),radial-gradient(circle_at_50%_90%,rgba(59,130,246,0.15),transparent_45%),linear-gradient(180deg,#020617,#050d1f_48%,#030712)]"></div>

        <div class="relative max-w-6xl mx-auto px-6 py-8 lg:py-10">
            <header class="flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-cyan-400 to-emerald-400 text-slate-950 font-bold grid place-items-center">P</div>
                    <div>
                        <div class="font-semibold tracking-wide">PrintService</div>
                        <div class="text-xs text-slate-400">Тарифи</div>
                    </div>
                </a>

                <nav class="flex items-center gap-3 text-sm">
                    <a href="{{ url('/') }}" class="px-4 py-2 rounded-md border border-slate-700 hover:border-slate-500 hover:text-white transition-colors">Головна</a>
                    @guest
                        <a href="{{ route('login') }}" class="px-4 py-2 rounded-md border border-slate-700 hover:border-slate-500 hover:text-white transition-colors">Вхід</a>
                        <a href="{{ route('register') }}" class="px-4 py-2 rounded-md bg-cyan-500 text-slate-950 font-medium hover:bg-cyan-400 transition-colors">Реєстрація</a>
                    @else
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-md bg-cyan-500 text-slate-950 font-medium hover:bg-cyan-400 transition-colors">Кабінет</a>
                    @endguest
                </nav>
            </header>

            <main class="pt-12 lg:pt-16 space-y-10">
                <section class="text-center max-w-3xl mx-auto">
                    <p class="inline-flex items-center gap-2 rounded-full border border-cyan-400/30 bg-cyan-400/10 px-3 py-1 text-xs uppercase tracking-[0.18em] text-cyan-200">USD</p>
                    <h1 class="mt-4 text-4xl lg:text-5xl font-semibold">Оберіть тариф для вашої мережі друку</h1>
                    <p class="mt-4 text-slate-300 text-lg">Обирайте план під вашу кількість друків і робочих станцій.</p>
                </section>

                <section class="grid lg:grid-cols-4 gap-5 items-stretch">
                    <article class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6 flex flex-col h-full">
                        <div>
                            <h2 class="text-xl font-semibold">Безкоштовно</h2>
                            <p class="mt-2 text-sm text-slate-400">Для тесту та старту</p>
                            <div class="mt-5">
                                <div class="text-4xl font-semibold">$0</div>
                                <div class="text-sm text-slate-400">до 50 друків</div>
                            </div>
                            <ul class="mt-5 space-y-2 text-sm text-slate-300 leading-6">
                                <li>- 1 комп'ютер</li>
                                <li>- базовий API доступ</li>
                                <li>- без пріоритетної підтримки</li>
                            </ul>
                        </div>
                        <div class="mt-6">
                            <a href="{{ route('register') }}" class="inline-flex h-11 items-center justify-center whitespace-nowrap rounded-lg border border-slate-500 bg-slate-900 px-5 text-[15px] font-semibold leading-5 text-slate-100 hover:border-slate-300 hover:bg-slate-800 transition-colors">Почати безкоштовно</a>
                        </div>
                    </article>

                    <article class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6 flex flex-col h-full">
                        <div>
                            <h2 class="text-xl font-semibold">Старт</h2>
                            <p class="mt-2 text-sm text-slate-400">Для невеликих команд</p>
                            <div class="mt-5">
                                <div class="text-4xl font-semibold">$8</div>
                                <div class="text-sm text-slate-400">за місяць</div>
                            </div>
                            <ul class="mt-5 space-y-2 text-sm text-slate-300 leading-6">
                                <li>- до 2 комп'ютерів</li>
                                <li>- до 5 000 друків/міс</li>
                                <li>- стандартна підтримка</li>
                            </ul>
                        </div>
                        <div class="mt-6">
                            <a href="{{ route('register') }}" class="inline-flex h-11 items-center justify-center whitespace-nowrap rounded-lg bg-emerald-400 px-5 text-[15px] font-semibold leading-5 text-slate-950 hover:bg-emerald-300 transition-colors">Обрати Старт</a>
                        </div>
                    </article>

                    <article class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6 flex flex-col h-full">
                        <div>
                            <h2 class="text-xl font-semibold">Стандарт</h2>
                            <p class="mt-2 text-sm text-slate-400">Оптимально для більшості бізнесів</p>
                            <div class="mt-5">
                                <div class="text-4xl font-semibold">$25</div>
                                <div class="text-sm text-slate-400">25 000 друків, до 5 комп'ютерів</div>
                            </div>
                            <ul class="mt-5 space-y-2 text-sm text-slate-300 leading-6">
                                <li>- до 5 комп'ютерів</li>
                                <li>- до 25 000 друків/міс</li>
                                <li>- пріоритет обробки черги</li>
                            </ul>
                        </div>
                        <div class="mt-6">
                            <a href="{{ route('register') }}" class="inline-flex h-11 items-center justify-center whitespace-nowrap rounded-lg bg-cyan-500 px-5 text-[15px] font-semibold leading-5 text-slate-950 hover:bg-cyan-400 transition-colors">Обрати Стандарт</a>
                        </div>
                    </article>

                    <article class="rounded-2xl border border-cyan-400/40 bg-slate-900/80 p-6 shadow-2xl shadow-cyan-950/30 flex flex-col h-full">
                        <div>
                            <div class="text-xs uppercase tracking-wider text-cyan-300">Рекомендовано</div>
                            <h2 class="mt-2 text-xl font-semibold">Бізнес</h2>
                            <p class="mt-2 text-sm text-slate-400">Для мереж і великого навантаження</p>
                            <div class="mt-5">
                                <div class="text-4xl font-semibold">$78</div>
                                <div class="text-sm text-slate-400">за місяць</div>
                            </div>
                            <ul class="mt-5 space-y-2 text-sm text-slate-300 leading-6">
                                <li>- до 10 комп'ютерів</li>
                                <li>- до 50 000 друків/міс</li>
                                <li>- retries, аудити, SLA</li>
                            </ul>
                        </div>
                        <div class="mt-6">
                            <a href="{{ route('register') }}" class="inline-flex h-11 items-center justify-center whitespace-nowrap rounded-lg bg-cyan-500 px-5 text-[15px] font-semibold leading-5 text-slate-950 hover:bg-cyan-400 transition-colors">Обрати Бізнес</a>
                        </div>
                    </article>
                </section>

                <section class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 overflow-x-auto">
                    <h3 class="text-xl font-semibold">Порівняння функцій</h3>
                    <table class="mt-4 min-w-full text-sm">
                        <thead class="text-slate-400 border-b border-slate-800">
                            <tr>
                                <th class="text-left py-2 pr-4">Функція</th>
                                <th class="text-left py-2 pr-4">Безкоштовно</th>
                                <th class="text-left py-2 pr-4">Старт</th>
                                <th class="text-left py-2 pr-4">Стандарт</th>
                                <th class="text-left py-2">Бізнес</th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-200">
                            <tr class="border-b border-slate-900">
                                <td class="py-2 pr-4">API jobs (create/poll/ack/fail)</td>
                                <td class="py-2 pr-4">Так</td>
                                <td class="py-2 pr-4">Так</td>
                                <td class="py-2 pr-4">Так</td>
                                <td class="py-2">Так</td>
                            </tr>
                            <tr class="border-b border-slate-900">
                                <td class="py-2 pr-4">Кабінет клієнта</td>
                                <td class="py-2 pr-4">Так</td>
                                <td class="py-2 pr-4">Так</td>
                                <td class="py-2 pr-4">Так</td>
                                <td class="py-2">Так</td>
                            </tr>
                            <tr class="border-b border-slate-900">
                                <td class="py-2 pr-4">Ліміт друків на місяць</td>
                                <td class="py-2 pr-4">50</td>
                                <td class="py-2 pr-4">5 000</td>
                                <td class="py-2 pr-4">25 000</td>
                                <td class="py-2">50 000</td>
                            </tr>
                            <tr class="border-b border-slate-900">
                                <td class="py-2 pr-4">Кількість комп'ютерів</td>
                                <td class="py-2 pr-4">1</td>
                                <td class="py-2 pr-4">2</td>
                                <td class="py-2 pr-4">5</td>
                                <td class="py-2">10</td>
                            </tr>
                            <tr>
                                <td class="py-2 pr-4">Пріоритетна підтримка</td>
                                <td class="py-2 pr-4">Ні</td>
                                <td class="py-2 pr-4">Ні</td>
                                <td class="py-2 pr-4">Так</td>
                                <td class="py-2">Так</td>
                            </tr>
                        </tbody>
                    </table>
                </section>
            </main>
        </div>

        @include('layouts.partials.site-footer')
    </div>
</body>
</html>
