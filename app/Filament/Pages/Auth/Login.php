<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Actions\Auth\RequestRestoreAccountAction;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;

final class Login extends \Filament\Auth\Pages\Login
{
    public bool $showRestoreAccountHint = false;

    protected static string $layout = 'layouts.auth';

    protected string $view = 'filament.pages.auth.login';

    public function mount(): void
    {
        parent::mount();

        // Fill the form with the admin credentials
        if (app()->hasDebugModeEnabled()) {
            $this->form->fill([
                'email' => 'superadmin@example.com',
                'password' => 'password',
                'remember' => true,
            ]);
        }
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $tooManyRequestsException) {
            $this->getRateLimitedNotification($tooManyRequestsException)?->send();

            return null;
        }

        $data = $this->form->getState();

        // Check if user is soft deleted
        $user = User::onlyTrashed()->where('email', $data['email'])->first();

        if ($user && ! $user->isAnonymous()) {
            if ($user->isDeletedBySelf()) {
                $this->showRestoreAccountHint = true;

                throw ValidationException::withMessages([
                    'data.email' => __('auth.deleted'),
                ]);
            }

            if ($user->isDeletedByAdmin()) {
                throw ValidationException::withMessages([
                    'data.email' => __('auth.deleted_by_admin'),
                ]);
            }

            // Fallback for old records without deleted_by
            $this->showRestoreAccountHint = true;

            throw ValidationException::withMessages([
                'data.email' => __('auth.deleted'),
            ]);
        }

        return parent::authenticate();
    }

    public function requestRestoreAccount(RequestRestoreAccountAction $action): void
    {
        $data = $this->form->getState();
        $email = $data['email'] ?? null;

        if (! is_string($email)) {
            return;
        }

        if ($action->handle($email)) {
            Notification::make()
                ->title(__('auth.restore_requested'))
                ->success()
                ->send();

            $this->showRestoreAccountHint = false;
        }
    }

    protected function getEmailFormComponent(): TextInput
    {
        return TextInput::make('email')
            ->label(__('filament-panels::auth/pages/login.form.email.label'))
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->hint(fn (): ?HtmlString => $this->showRestoreAccountHint ? new HtmlString(Blade::render('
                <x-filament::link
                    wire:click="requestRestoreAccount"
                    class="cursor-pointer"
                    tabindex="-1"
                >
                    {{ __(\'auth.restore\') }}
                </x-filament::link>
            ')) : null);
    }
}
