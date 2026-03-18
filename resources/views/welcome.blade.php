<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PrintService</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_12%_8%,rgba(56,189,248,0.20),transparent_36%),radial-gradient(circle_at_85%_0%,rgba(16,185,129,0.16),transparent_33%),radial-gradient(circle_at_50%_90%,rgba(59,130,246,0.15),transparent_45%),linear-gradient(180deg,#020617,#050d1f_48%,#030712)]"></div>
        <div class="absolute inset-x-0 top-[-9rem] h-80 bg-cyan-400/10 blur-3xl"></div>

        <div class="relative max-w-6xl mx-auto px-6 py-8 lg:py-10">
            <header class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-cyan-400 to-emerald-400 text-slate-950 font-bold grid place-items-center">P</div>
                    <div>
                        <div class="font-semibold tracking-wide">PrintService</div>
                        <div class="text-xs text-slate-400">Хмарна черга + Windows агент</div>
                    </div>
                </div>

                <nav class="flex items-center gap-3 text-sm">
                    <a href="{{ route('pricing') }}" class="px-4 py-2 rounded-md border border-slate-700 hover:border-slate-500 hover:text-white transition-colors">Ціни</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-md bg-cyan-500 text-slate-950 font-medium hover:bg-cyan-400 transition-colors">Особистий кабінет</a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 rounded-md border border-slate-700 hover:border-slate-500 hover:text-white transition-colors">Вхід</a>
                        <a href="{{ route('register') }}" class="px-4 py-2 rounded-md bg-cyan-500 text-slate-950 font-medium hover:bg-cyan-400 transition-colors">Реєстрація</a>
                    @endauth
                </nav>
            </header>

            <main class="pt-14 lg:pt-20 space-y-16 lg:space-y-24">
                <section class="grid lg:grid-cols-2 gap-10 items-center">
                    <div>
                        <p class="inline-flex items-center gap-2 rounded-full border border-cyan-400/30 bg-cyan-400/10 px-3 py-1 text-xs uppercase tracking-[0.18em] text-cyan-200">MVP готовий</p>
                        <h1 class="mt-5 text-4xl lg:text-6xl font-semibold leading-[1.08]">Друк документів через єдине API з локальним агентом на Windows</h1>
                        <p class="mt-5 text-slate-300 text-lg leading-relaxed">Реєструйте клієнтів, видавайте персональні ключі, приймайте завдання у хмарі та друкуйте локально через захищену чергу.</p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="{{ route('register') }}" class="px-6 py-3 rounded-lg bg-emerald-400 text-slate-950 font-semibold hover:bg-emerald-300 transition-colors">Створити кабінет клієнта</a>
                            <a href="{{ route('login') }}" class="px-6 py-3 rounded-lg border border-slate-700 hover:border-slate-500 transition-colors">Увійти</a>
                        </div>
                        <div class="mt-6 text-sm text-slate-400">Сумісно з чергою друку та агентом для старих Windows (включно з Win7 x86).</div>
                    </div>

                    <div class="rounded-2xl border border-slate-800 bg-slate-900/80 backdrop-blur p-5 shadow-2xl shadow-cyan-950/30">
                        <div class="text-sm text-slate-400">Швидкий старт</div>
                        <pre class="mt-3 overflow-x-auto rounded-xl border border-slate-800 bg-slate-950 px-4 py-4 text-xs text-slate-200"><code>POST {{ url('/api/print/v1/jobs') }}
Content-Type: application/json

{
  "tenant_code": "default",
  "printer_selector": "cashdesk_1",
  "job_type": "raw",
  "content_type": "text/html",
  "payload": "&lt;h1&gt;Чек&lt;/h1&gt;"
}</code></pre>
                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-lg border border-slate-800 bg-slate-950/70 p-3">
                                <div class="text-slate-400 text-xs">Відповідь</div>
                                <div class="font-mono text-cyan-300 mt-1">status: queued</div>
                            </div>
                            <div class="rounded-lg border border-slate-800 bg-slate-950/70 p-3">
                                <div class="text-slate-400 text-xs">Потік</div>
                                <div class="font-mono text-emerald-300 mt-1">API -> Агент -> Принтер</div>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl lg:text-3xl font-semibold">Як це працює</h2>
                    <div class="mt-6 grid md:grid-cols-4 gap-4">
                        <div class="rounded-xl border border-slate-800 bg-slate-900/70 p-4">
                            <div class="text-xs text-cyan-300 uppercase tracking-wider">Крок 1</div>
                            <div class="mt-2 font-medium">Клієнт надсилає JSON</div>
                            <p class="mt-1 text-sm text-slate-400">Ваш сервіс приймає завдання у хмарну чергу.</p>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-900/70 p-4">
                            <div class="text-xs text-cyan-300 uppercase tracking-wider">Крок 2</div>
                            <div class="mt-2 font-medium">Черга резервує завдання</div>
                            <p class="mt-1 text-sm text-slate-400">Контроль статусів, retries і журнал спроб.</p>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-900/70 p-4">
                            <div class="text-xs text-cyan-300 uppercase tracking-wider">Крок 3</div>
                            <div class="mt-2 font-medium">Windows агент забирає завдання</div>
                            <p class="mt-1 text-sm text-slate-400">За API ключем і підписом агент отримує next-job.</p>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-900/70 p-4">
                            <div class="text-xs text-cyan-300 uppercase tracking-wider">Крок 4</div>
                            <div class="mt-2 font-medium">Друк і callback статусу</div>
                            <p class="mt-1 text-sm text-slate-400">ack/fail повертаються у хмару та відображаються у кабінеті.</p>
                        </div>
                    </div>
                </section>

                <section class="grid lg:grid-cols-2 gap-6">
                    <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6">
                        <h3 class="text-xl font-semibold">Для власника сервісу</h3>
                        <ul class="mt-4 space-y-2 text-sm text-slate-300">
                            <li>- multi-tenant ізоляція клієнтів</li>
                            <li>- створення та відкликання API ключів у кабінеті</li>
                            <li>- моніторинг черги, агентів і помилок</li>
                        </ul>
                    </div>
                    <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6">
                        <h3 class="text-xl font-semibold">Для клієнтської інтеграції</h3>
                        <ul class="mt-4 space-y-2 text-sm text-slate-300">
                            <li>- простий JSON API для відправлення друку</li>
                            <li>- статусна модель queued/reserved/printed/failed</li>
                            <li>- сумісність з локальними принтерами через Windows Agent</li>
                        </ul>
                    </div>
                </section>

            </main>
        </div>

        @include('layouts.partials.site-footer')
    </div>
</body>
</html>
