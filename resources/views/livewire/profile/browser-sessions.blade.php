<div class="space-y-6">
    @if (count($this->sessions) > 0)
        <div class="space-y-4">
            @foreach ($this->sessions as $session)
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 text-gray-500 dark:text-gray-400">
                        @if ($session->agent->is_desktop)
                            <x-filament::icon
                                icon="heroicon-m-computer-desktop"
                                class="h-8 w-8"
                            />
                        @else
                            <x-filament::icon
                                icon="heroicon-m-device-phone-mobile"
                                class="h-8 w-8"
                            />
                        @endif
                    </div>

                    <div class="flex-grow">
                        <div class="text-sm font-medium text-gray-950 dark:text-white">
                            {{ $session->agent->platform ? $session->agent->platform : __('Unknown') }} - {{ $session->agent->browser ? $session->agent->browser : __('Unknown') }}
                        </div>

                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $session->ip_address }},
                            @if ($session->is_current_device)
                                <span class="text-success-600 font-semibold">{{ __('This device') }}</span>
                            @else
                                {{ __('Last active') }} {{ $session->last_active }}
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
