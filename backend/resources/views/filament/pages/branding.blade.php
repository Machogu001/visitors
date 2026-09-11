<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">{{ __('Portal logos') }}</x-slot>
            <x-slot name="description">{{ __('Use transparent PNG or WebP images with enough contrast for each theme.') }}</x-slot>

            <div class="grid gap-6 lg:grid-cols-2">
                @foreach ([
                    ['property' => 'logoLight', 'asset' => 'logo_light', 'label' => __('Light theme logo'), 'surface' => 'bg-white'],
                    ['property' => 'logoDark', 'asset' => 'logo_dark', 'label' => __('Dark theme logo'), 'surface' => 'bg-gray-950'],
                ] as $logo)
                    <div class="space-y-4 rounded-lg border border-gray-200 p-5 dark:border-white/10">
                        <h3 class="text-sm font-semibold text-gray-950 dark:text-white">{{ $logo['label'] }}</h3>
                        <div class="{{ $logo['surface'] }} flex h-28 items-center justify-center rounded-lg border border-gray-200 p-5 dark:border-white/10">
                            @if ($this->currentPath($logo['asset']))
                                <img src="{{ asset($this->currentPath($logo['asset'])) }}" alt="{{ $logo['label'] }}" class="max-h-full max-w-full object-contain">
                            @endif
                        </div>
                        <input type="file" wire:model="{{ $logo['property'] }}" accept="image/png,image/jpeg,image/webp" class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-primary-600 file:px-4 file:py-2 file:font-semibold file:text-white dark:text-gray-300">
                        @error($logo['property'])
                            <p class="text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                        @if ($this->isCustom($logo['asset']))
                            <x-filament::button type="button" color="danger" size="sm" outlined wire:click="remove('{{ $logo['asset'] }}')">
                                {{ __('Use default logo') }}
                            </x-filament::button>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">{{ __('Browser tab icon') }}</x-slot>
            <x-slot name="description">{{ __('Upload a square PNG or WebP image. It appears in browser tabs and bookmarks.') }}</x-slot>

            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white p-3 dark:border-white/10">
                    @if ($this->currentPath('favicon'))
                        <img src="{{ asset($this->currentPath('favicon')) }}" alt="{{ __('Browser tab icon') }}" class="max-h-full max-w-full object-contain">
                    @else
                        <img src="{{ asset('favicon-32x32.png') }}" alt="{{ __('Browser tab icon') }}" class="h-12 w-12 object-contain">
                    @endif
                </div>
                <div class="min-w-0 flex-1 space-y-3">
                    <input type="file" wire:model="favicon" accept="image/png,image/jpeg,image/webp" class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-primary-600 file:px-4 file:py-2 file:font-semibold file:text-white dark:text-gray-300">
                    @error('favicon')
                        <p class="text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                    @if ($this->isCustom('favicon'))
                        <x-filament::button type="button" color="danger" size="sm" outlined wire:click="remove('favicon')">
                            {{ __('Use default icon') }}
                        </x-filament::button>
                    @endif
                </div>
            </div>
        </x-filament::section>

        <div class="flex justify-end">
            <x-filament::button type="submit" wire:loading.attr="disabled" wire:target="save,logoLight,logoDark,favicon">
                <span wire:loading.remove wire:target="save">{{ __('Save branding') }}</span>
                <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>