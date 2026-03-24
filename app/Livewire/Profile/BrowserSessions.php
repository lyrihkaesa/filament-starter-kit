<?php

declare(strict_types=1);

namespace App\Livewire\Profile;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

final class BrowserSessions extends Component implements HasActions
{
    use InteractsWithActions;

    /**
     * @return Action
     */
    #[On('refresh-sessions')]
    public function refresh(): void
    {
        //
    }

    #[Computed]
    public function sessions(): Collection
    {
        if (config('session.driver') !== 'database') {
            return collect();
        }

        $currentSessionIdRaw = null;
        try {
            $currentSessionIdRaw = session()->getId();
        } catch (Throwable $e) {
            // No session
        }

        return DB::table('sessions')
            ->where('user_id', Auth::user()->getAuthIdentifier())
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function ($session) use ($currentSessionIdRaw): object {
                $agent = $this->createAgent((string) ($session->user_agent ?? ''));

                return (object) [
                    'agent' => (object) [
                        'is_desktop' => $agent['is_desktop'],
                        'platform' => $agent['platform'],
                        'browser' => $agent['browser'],
                    ],
                    'ip_address' => $session->ip_address,
                    'is_current_device' => $currentSessionIdRaw && $session->id === $currentSessionIdRaw,
                    'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                ];
            });
    }

    public function render(): View
    {
        return view('livewire.profile.browser-sessions');
    }

    protected function logoutOtherBrowserSessions(string $password): void
    {
        if (! Hash::check($password, Auth::user()->getAuthPassword())) {
            Notification::make()
                ->title(__('The provided password does not match our records.'))
                ->danger()
                ->send();

            return;
        }

        Auth::logoutOtherDevices($password);

        $this->deleteOtherSessionRecords();

        Notification::make()
            ->title(__('Done.'))
            ->success()
            ->send();
    }

    protected function deleteOtherSessionRecords(): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        $currentSessionIdRaw = null;
        try {
            $currentSessionIdRaw = session()->getId();
        } catch (Throwable $e) {
            // No session
        }

        DB::table('sessions')
            ->where('user_id', Auth::user()->getAuthIdentifier())
            ->when($currentSessionIdRaw, fn ($query) => $query->where('id', '<>', $currentSessionIdRaw))
            ->delete();
    }

    /**
     * @return array{is_desktop: bool, platform: string, browser: string}
     */
    protected function createAgent(string $userAgent): array
    {
        $isMobile = (bool) preg_match('/Mobile|Android|iPhone|iPad|Phone/i', $userAgent);

        $browser = 'Unknown Browser';
        if (preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Safari/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Edge/i', $userAgent)) {
            $browser = 'Edge';
        }

        $platform = 'Unknown OS';
        if (preg_match('/Windows/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/Mac/i', $userAgent)) {
            $platform = 'macOS';
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $platform = 'Linux';
        } elseif (preg_match('/Android/i', $userAgent)) {
            $platform = 'Android';
        } elseif (preg_match('/iPhone|iPad/i', $userAgent)) {
            $platform = 'iOS';
        }

        return [
            'is_desktop' => ! $isMobile,
            'browser' => $browser,
            'platform' => $platform,
        ];
    }
}
