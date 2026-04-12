<?php

declare(strict_types=1);

namespace App\Support\Filament;

final class FilamentNavigation
{
    /**
     * Get the navigation sort order based on the translated label.
     */
    public static function sort(?string $label): ?int
    {
        if ($label === null) {
            return null;
        }

        $navigationLabels = [
            // Manajemen Konten
            __('Post'),
            __('Media'),

            // Manajemen Sistem
            __('User'),
            __('Role'),
            __('Activity'),
        ];

        // Create array with automatic keys starting from -99
        $navigationSort = array_combine(
            range(-99, -99 + count($navigationLabels) - 1),
            $navigationLabels
        );

        $key = array_search($label, $navigationSort, true);

        return $key !== false ? (int) $key : null;
    }
}
