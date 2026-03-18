<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">История заданий печати</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                <form method="GET" action="{{ route('jobs.index') }}" class="flex gap-3 items-end">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Статус</label>
                        <select name="status" class="rounded-md border-gray-300">
                            <option value="">Все</option>
                            @foreach (['queued','reserved','retry_wait','printed','failed'] as $item)
                                <option value="{{ $item }}" @selected($status === $item)>{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-primary-button>Фильтровать</x-primary-button>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-4 py-2 text-left">ID</th>
                                <th class="px-4 py-2 text-left">Принтер</th>
                                <th class="px-4 py-2 text-left">Тип</th>
                                <th class="px-4 py-2 text-left">Статус</th>
                                <th class="px-4 py-2 text-left">Спроби</th>
                                <th class="px-4 py-2 text-left">Создано</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($jobs as $job)
                                <tr class="border-t border-gray-100">
                                    <td class="px-4 py-2 font-mono text-xs">{{ $job->id }}</td>
                                    <td class="px-4 py-2">{{ $job->printer_selector }}</td>
                                    <td class="px-4 py-2">{{ $job->content_type }}</td>
                                    <td class="px-4 py-2">{{ $job->status }}</td>
                                    <td class="px-4 py-2">{{ $job->attempts_count }}</td>
                                    <td class="px-4 py-2">{{ optional($job->created_at)->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-4 py-3 text-gray-500" colspan="6">Заданий пока нет.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">{{ $jobs->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
