<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">API ключі та інтеграція</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('new_activation_code'))
                <div class="bg-cyan-50 border border-cyan-200 text-cyan-900 rounded-lg p-4">
                    <div class="font-medium">Код активації для Windows Agent (зашифрований):</div>
                    <div class="mt-2 text-xs text-cyan-800">Скопіюйте тільки поле нижче (без kid/exp/one-time):</div>
                    <textarea
                        id="activation-code"
                        readonly
                        onclick="this.select()"
                        class="mt-2 w-full min-h-28 rounded-md border border-cyan-300 bg-white font-mono text-xs text-cyan-950 p-2"
                    >{{ session('new_activation_code') }}</textarea>
                    <div class="mt-2 text-xs">
                        kid: <span class="font-mono">{{ session('new_activation_code_kid') }}</span>,
                        exp: <span class="font-mono">{{ session('new_activation_code_exp') }}</span>,
                        one-time: <span class="font-mono">{{ session('new_activation_code_one_time') }}</span>
                    </div>
                    <div class="mt-3">
                        <button
                            type="button"
                            onclick="navigator.clipboard && navigator.clipboard.writeText(document.getElementById('activation-code').value.trim())"
                            class="inline-flex items-center px-3 py-1.5 text-xs rounded-md bg-cyan-700 text-white hover:bg-cyan-800"
                        >
                            Copy activation code
                        </button>
                    </div>
                </div>
            @endif

            @if (session('new_site_api_key'))
                <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-lg p-4">
                    <div class="font-medium">Site API key (для сайтів/MyAdmin):</div>
                    <textarea
                        id="site-api-key"
                        readonly
                        onclick="this.select()"
                        class="mt-2 w-full min-h-24 rounded-md border border-amber-300 bg-white font-mono text-xs text-amber-950 p-2"
                    >{{ session('new_site_api_key') }}</textarea>
                    <div class="mt-2 text-xs">Ключ показується лише один раз. Використовуйте в заголовку Authorization: Bearer ...</div>
                    <div class="mt-3">
                        <button
                            type="button"
                            onclick="navigator.clipboard && navigator.clipboard.writeText(document.getElementById('site-api-key').value.trim())"
                            class="inline-flex items-center px-3 py-1.5 text-xs rounded-md bg-amber-700 text-white hover:bg-amber-800"
                        >
                            Copy site API key
                        </button>
                    </div>
                </div>
            @endif

            @if (session('status'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-lg p-3">{{ session('status') }}</div>
            @endif

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                <h3 class="font-medium text-gray-900 mb-4">Створити ключ для Windows Agent</h3>
                <form method="POST" action="{{ route('api-keys.store') }}" class="grid grid-cols-1 gap-4">
                    @csrf
                    <div class="text-sm text-gray-600">
                        Згенеруємо одноразовий код активації для Windows Agent. Після введення в застосунок агент створиться автоматично на сервері.
                    </div>
                    <div class="flex items-end">
                        <x-primary-button>Сгенерировать код активации</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                <h3 class="font-medium text-gray-900 mb-4">Створити Site API key (для сайтів)</h3>
                <form method="POST" action="{{ route('api-keys.store-site') }}" class="grid grid-cols-1 gap-4">
                    @csrf
                    <div class="text-sm text-gray-600">
                        Цей ключ використовується сайтами для створення jobs через Bearer авторизацію.
                    </div>
                    <div class="flex items-end">
                        <x-primary-button>Сгенерировать site API key</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 font-medium">Поточні ключі</div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-4 py-2 text-left">Тип</th>
                                <th class="px-4 py-2 text-left">Префикс</th>
                                <th class="px-4 py-2 text-left">Статус</th>
                                <th class="px-4 py-2 text-left">Истекает</th>
                                <th class="px-4 py-2 text-left"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($apiKeys as $key)
                                <tr class="border-t border-gray-100">
                                    <td class="px-4 py-2">{{ $key->key_type ?? 'agent' }}</td>
                                    <td class="px-4 py-2 font-mono text-xs">{{ $key->key_prefix }}***</td>
                                    <td class="px-4 py-2">{{ $key->revoked_at ? 'revoked' : 'active' }}</td>
                                    <td class="px-4 py-2">{{ optional($key->expires_at)->format('Y-m-d H:i') ?? 'never' }}</td>
                                    <td class="px-4 py-2 text-right">
                                        @if (! $key->revoked_at)
                                            <form method="POST" action="{{ route('api-keys.destroy', $key) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-rose-600 hover:underline">Revoke</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-4 py-3 text-gray-500" colspan="5">Ключей пока нет.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 text-sm text-gray-700 space-y-2">
                <div><span class="font-medium">Base API URL:</span> <span class="font-mono">{{ $apiBaseUrl }}</span></div>
                <div><span class="font-medium">Create job endpoint:</span> <span class="font-mono">POST {{ $apiBaseUrl }}/jobs</span></div>
                <div><span class="font-medium">Agent poll endpoint:</span> <span class="font-mono">GET {{ $apiBaseUrl }}/agents/next</span></div>
            </div>
        </div>
    </div>
</x-app-layout>
