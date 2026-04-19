<div class="space-y-3">
    @if ($usages->isEmpty())
        <div class="rounded-lg border border-gray-200 p-4 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-300">
            {{ __('Media ini belum dipakai oleh data lain.') }}
        </div>
    @else
        <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800/40">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">{{ __('Model') }}</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">{{ __('Record ID') }}</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">{{ __('Field') }}</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900/20">
                    @foreach ($usages as $usage)
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-800 dark:text-gray-100">{{ $usage['model_label'] }}</td>
                            <td class="px-4 py-2 font-mono text-xs text-gray-700 dark:text-gray-200">{{ $usage['model_id'] }}</td>
                            <td class="px-4 py-2 font-mono text-xs text-gray-700 dark:text-gray-200">{{ $usage['field_name'] }}</td>
                            <td class="px-4 py-2 text-sm">
                                <div class="flex flex-col gap-1">
                                    @forelse ($usage['actions'] as $action)
                                        <a href="{{ $action['url'] }}" 
                                           class="inline-flex items-center gap-1 text-primary-600 hover:underline dark:text-primary-400" 
                                           target="_blank" 
                                           rel="noopener noreferrer"
                                        >
                                            @if(filled($action['icon']))
                                                <x-filament::icon
                                                    :icon="$action['icon']"
                                                    class="h-4 w-4"
                                                />
                                            @endif
                                            {{ $action['label'] }}
                                        </a>
                                    @empty
                                        <span class="text-gray-500 dark:text-gray-400 italic text-xs">{{ __('Tidak ada link aksi') }}</span>
                                    @endforelse
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
