<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Actions\Auth\RestoreAccountAction;
use Filament\Notifications\Notification;
use Filament\Pages\SimplePage;
use Illuminate\Http\Request;

final class RestoreAccount extends SimplePage
{
    protected static string $layout = 'layouts.auth';

    protected string $view = 'filament.pages.auth.restore-account';

    public function mount(Request $request, RestoreAccountAction $action): void
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

        if ($action->handle($userId)) {
            Notification::make()
                ->title(__('auth.restore_success'))
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title(__('auth.restore_failed'))
                ->danger()
                ->send();
        }

        $this->redirect(filament()->getLoginUrl());
    }

    public function getHeading(): string
    {
        return __('auth.restoring_heading');
    }
}
