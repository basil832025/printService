<footer class="border-t border-slate-800 bg-slate-950/80">
    <div class="max-w-6xl mx-auto px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-400">
        <div>PrintService © {{ date('Y') }}</div>
        <div class="flex items-center gap-4">
            <a href="{{ url('/') }}" class="hover:text-slate-200 transition-colors">Головна</a>
            <a href="{{ route('pricing') }}" class="hover:text-slate-200 transition-colors">Ціни</a>
            @guest
                <a href="{{ route('login') }}" class="hover:text-slate-200 transition-colors">Вхід</a>
                <a href="{{ route('register') }}" class="hover:text-slate-200 transition-colors">Реєстрація</a>
            @else
                <a href="{{ route('dashboard') }}" class="hover:text-slate-200 transition-colors">Кабінет</a>
            @endguest
        </div>
    </div>
</footer>
