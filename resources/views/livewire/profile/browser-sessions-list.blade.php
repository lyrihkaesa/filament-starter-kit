<div class="space-y-6">

    @if (count($browser_sessions_data) > 0)
        <div class="space-y-4">
            @foreach ($browser_sessions_data as $session)
                <div class="flex items-center gap-6">
                    <div class="flex-shrink-0 text-gray-500 dark:text-gray-400">
                        @if ($session->agent->is_desktop)
                            <x-filament::icon icon="heroicon-m-computer-desktop" class="h-8 w-8" />
                        @else
                            <x-filament::icon icon="heroicon-m-device-phone-mobile" class="h-8 w-8" />
                        @endif
                    </div>

                    <div class="flex-grow">
                        <div class="text-sm font-medium text-gray-950 dark:text-white">
                            {{ $session->agent->platform ? $session->agent->platform : __('Unknown') }} -
                            {{ $session->agent->browser ? $session->agent->browser : __('Unknown') }}
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

                    @if (!$session->is_current_device)
                        <div class="flex-shrink-0">
                            <x-filament::modal id="logout-session-{{ $session->id }}" width="md">
                                <x-slot name="trigger">
                                    <x-filament::link
                                        color="danger"
                                        tag="button"
                                        size="sm"
                                        class="text-xs"
                                    >
                                        {{ __('Log Out') }}
                                    </x-filament::link>
                                </x-slot>
                                
                                <x-slot name="heading">
                                    {{ __('Log Out Session') }}
                                </x-slot>

                                <x-slot name="description">
                                    {{ __('Are you sure you want to log out of this session? This will immediately terminate the session on that device.') }}
                                </x-slot>

                                <div class="flex justify-end gap-3">
                                    <x-filament::button
                                        color="gray"
                                        x-on:click="isOpen = false"
                                    >
                                        {{ __('Cancel') }}
                                    </x-filament::button>
                                    
                                    <x-filament::button
                                        color="danger"
                                        wire:click="logoutSession('{{ $session->id }}')"
                                        x-on:click="isOpen = false"
                                    >
                                        {{ __('Log Out') }}
                                    </x-filament::button>
                                </div>
                            </x-filament::modal>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
