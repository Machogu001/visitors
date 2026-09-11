<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace App\Filament\Pages;

use App\Models\BrandingSetting;
use App\Models\User;
use App\Support\RasterImageUpload;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use UnitEnum;

class Branding extends Page
{
    use WithFileUploads;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Branding';

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 90;

    protected string $view = 'filament.pages.branding';

    public ?TemporaryUploadedFile $logoLight = null;

    public ?TemporaryUploadedFile $logoDark = null;

    public ?TemporaryUploadedFile $favicon = null;

    public function getTitle(): string
    {
        return __('Branding');
    }

    public function getSubheading(): ?string
    {
        return __('Update portal logos and the icon shown in browser tabs.');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && $user->is_active
            && $user->hasAnyRole(['admin', 'super_admin']);
    }

    public function save(): void
    {
        $this->validate([
            'logoLight' => RasterImageUpload::rules(),
            'logoDark' => RasterImageUpload::rules(),
            'favicon' => RasterImageUpload::rules(),
        ]);

        $branding = BrandingSetting::current();
        $updates = [];

        foreach ([
            'logoLight' => 'logo_light_path',
            'logoDark' => 'logo_dark_path',
            'favicon' => 'favicon_path',
        ] as $property => $column) {
            $upload = $this->{$property};

            if (! $upload instanceof TemporaryUploadedFile) {
                continue;
            }

            $this->deleteCustomFile($branding->{$column});
            $updates[$column] = 'storage/'.RasterImageUpload::store(
                $upload,
                directory: 'branding',
                attribute: $property,
            );
        }

        if ($updates === []) {
            Notification::make()
                ->title(__('Choose at least one image to update.'))
                ->warning()
                ->send();

            return;
        }

        $branding->update($updates);
        config(array_filter([
            'branding.logo_light' => $branding->logo_light_path,
            'branding.logo_dark' => $branding->logo_dark_path,
            'branding.favicon' => $branding->favicon_path,
        ]));

        $this->reset('logoLight', 'logoDark', 'favicon');

        Notification::make()
            ->title(__('Branding updated'))
            ->success()
            ->send();
    }

    public function remove(string $asset): void
    {
        $column = match ($asset) {
            'logo_light' => 'logo_light_path',
            'logo_dark' => 'logo_dark_path',
            'favicon' => 'favicon_path',
            default => null,
        };

        abort_unless($column !== null, 404);

        $branding = BrandingSetting::current();
        $this->deleteCustomFile($branding->{$column});
        $branding->update([$column => null]);
        config([
            'branding.'.str_replace('_path', '', $column) => config('branding.default_'.str_replace('_path', '', $column)),
        ]);

        Notification::make()
            ->title(__('Custom image removed. The configured default is active again.'))
            ->success()
            ->send();
    }

    public function currentPath(string $asset): ?string
    {
        return match ($asset) {
            'logo_light' => config('branding.logo_light'),
            'logo_dark' => config('branding.logo_dark'),
            'favicon' => config('branding.favicon'),
            default => null,
        };
    }

    public function isCustom(string $asset): bool
    {
        return str_starts_with((string) $this->currentPath($asset), 'storage/branding/');
    }

    private function deleteCustomFile(?string $path): void
    {
        if (is_string($path) && str_starts_with($path, 'storage/branding/')) {
            Storage::disk('public')->delete(substr($path, strlen('storage/')));
        }
    }
}
