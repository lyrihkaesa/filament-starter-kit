<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\SimplePage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class RestoreAccount extends SimplePage
{
    protected static string $layout = 'layouts.auth';

    protected string $view = 'filament.pages.auth.restore-account';

    public function mount(Request $request): void
    {
        if (! $request->hasValidSignature()) {
            Notification::make()
                ->title(__('auth.restore_failed'))
                ->danger()
                ->send();

            $this->redirect(filament()->getLoginUrl());

            return;
        }

        $userId = $request->route('id');
        $user = User::onlyTrashed()->find($userId);

        if (! $user) {
            Notification::make()
                ->title(__('auth.restore_failed'))
                ->danger()
                ->send();

            $this->redirect(filament()->getLoginUrl());

            return;
        }

        $user->restore();

        Notification::make()
            ->title(__('auth.restore_success'))
            ->success()
            ->send();

        $this->redirect(filament()->getLoginUrl());
    }

    public function getHeading(): string
    {
        return __('auth.restoring_heading');
    }
}
