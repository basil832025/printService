<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Особистий кабінет клієнта</h2>
            <div class="text-sm text-gray-500">tenant: {{ auth()->user()->tenant?->code }}</div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                    <div class="text-sm text-gray-500">В очереди</div>
                    <div class="text-2xl font-semibold text-gray-900">{{ $stats['queued'] }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                    <div class="text-sm text-gray-500">Напечатано</div>
                    <div class="text-2xl font-semibold text-emerald-700">{{ $stats['printed'] }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                    <div class="text-sm text-gray-500">Ошибки</div>
                    <div class="text-2xl font-semibold text-rose-700">{{ $stats['failed'] }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                    <div class="text-sm text-gray-500">Агенти online</div>
                    <div class="text-2xl font-semibold text-blue-700">{{ $stats['agents_online'] }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 font-medium">Последние задания</div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th class="px-4 py-2 text-left">ID</th>
                                    <th class="px-4 py-2 text-left">Принтер</th>
                                    <th class="px-4 py-2 text-left">Статус</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentJobs as $job)
                                    <tr class="border-t border-gray-100">
                                        <td class="px-4 py-2 font-mono text-xs">{{ $job->id }}</td>
                                        <td class="px-4 py-2">{{ $job->printer_selector }}</td>
                                        <td class="px-4 py-2">{{ $job->status }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="px-4 py-3 text-gray-500" colspan="3">Пока нет заданий.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 font-medium">API ключі</div>
                    <div class="p-4 space-y-2 text-sm">
                        @forelse ($apiKeys as $key)
                            <div class="flex items-center justify-between border border-gray-100 rounded-md px-3 py-2">
                                <div>
                                    <div class="font-mono text-xs">{{ $key->key_prefix }}***</div>
                                    <div class="text-gray-500 text-xs">{{ $key->revoked_at ? 'revoked' : 'active' }}</div>
                                </div>
                                <a href="{{ route('api-keys.index') }}" class="text-blue-600 hover:underline">Управление</a>
                            </div>
                        @empty
                            <div class="text-gray-500">Ключі ще не створені.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
