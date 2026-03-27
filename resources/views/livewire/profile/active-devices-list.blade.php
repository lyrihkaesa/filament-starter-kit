<div class="space-y-6">

    @if (count($active_devices) > 0)
        <div class="space-y-4">
            @foreach ($active_devices as $device)
                <div class="flex items-center gap-6">
                    <div class="flex-shrink-0 text-gray-500 dark:text-gray-400">
                        @if ($device->type === 'mobile_app')
                            <x-filament::icon icon="heroicon-m-device-phone-mobile" class="h-8 w-8" />
                        @elseif ($device->type === 'desktop_app')
                            <x-filament::icon icon="heroicon-m-computer-desktop" class="h-8 w-8" />
                        @elseif ($device->type === 'api_client')
                            <x-filament::icon icon="heroicon-m-command-line" class="h-8 w-8" />
                        @else
                            {{-- web_session --}}
                            <x-filament::icon icon="heroicon-m-globe-alt" class="h-8 w-8" />
                        @endif
                    </div>

                    <div class="flex-grow">
                        <div class="text-sm font-medium text-gray-950 dark:text-white">
                            {{ $device->label }}
                        </div>

                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            @if ($device->ipAddress)
                                {{ $device->ipAddress }},
                            @endif
                            @if ($device->isCurrentDevice)
                                <span class="text-success-600 font-semibold">{{ __('This device') }}</span>
                            @else
                                {{ __('Last active') }} {{ $device->lastActiveAt }}
                            @endif
                        </div>
                    </div>

                    @if (!$device->isCurrentDevice)
                        <div class="flex-shrink-0">
                            <x-filament::modal id="revoke-device-{{ $device->deviceId }}" width="md">
                                <x-slot name="trigger">
                                    <x-filament::link
                                        color="danger"
                                        tag="button"
                                        size="sm"
                                        class="text-xs"
                                    >
                                        {{ __('Sign Out') }}
                                    </x-filament::link>
                                </x-slot>

                                <x-slot name="heading">
                                    {{ __('Sign Out Device') }}
                                </x-slot>

                                <x-slot name="description">
                                    {{ __('Are you sure you want to sign out from ":device"? This will immediately terminate the session or revoke the access token.', ['device' => $device->label]) }}
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
                                        wire:click="revokeDevice('{{ $device->deviceId }}')"
                                        x-on:click="isOpen = false"
                                    >
                                        {{ __('Sign Out') }}
                                    </x-filament::button>
                                </div>
                            </x-filament::modal>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ __('No active devices found.') }}
        </p>
    @endif
</div>
