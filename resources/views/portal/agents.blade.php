<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Windows агенти</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-4 py-2 text-left">Ім'я</th>
                                <th class="px-4 py-2 text-left">Machine UID</th>
                                <th class="px-4 py-2 text-left">OS</th>
                                <th class="px-4 py-2 text-left">Версия</th>
                                <th class="px-4 py-2 text-left">Статус</th>
                                <th class="px-4 py-2 text-left">Last seen</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($agents as $agent)
                                <tr class="border-t border-gray-100">
                                    <td class="px-4 py-2">{{ $agent->name }}</td>
                                    <td class="px-4 py-2 font-mono text-xs">{{ $agent->machine_uid }}</td>
                                    <td class="px-4 py-2">{{ $agent->os_info ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $agent->version ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $agent->status }}</td>
                                    <td class="px-4 py-2">{{ optional($agent->last_seen_at)->diffForHumans() ?? 'never' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-4 py-3 text-gray-500" colspan="6">Агенти ще не підключались.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
