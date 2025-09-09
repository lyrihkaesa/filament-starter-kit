<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

final class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasPanelShield, HasRoles, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function isAnonymous(): bool
    {
        return ! is_null($this->anonymized_at);
    }

    public function anonymize(): void
    {
        $this->update([
            'name' => "Anonymous {$this->name}",
            'email' => "anonymous{$this->email}",
            'deleted_at' => $this->deleted_at ?? now(),
            'anonymized_at' => now(),
        ]);
    }

    /**
     * Filament override implements FilamentUser
     * And override trait HasPanelShield
     * Jika user memiliki role 'super_admin' atau 'panel_user' maka bisa akses panel.
     *
     * @see FilamentUser
     * @see HasPanelShield
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // return str_ends_with($this->email, '@example.com') && $this->hasVerifiedEmail();
        return true;
    }

    public function canImpersonate(): bool
    {
        // Let's prevent impersonating other users at our own company
        // example:
        // return $this->email === 'superadmin@example.com';
        return true;
    }

    public function canBeImpersonated(): bool
    {
        // Let's prevent being impersonated by other users at our own company
        // example:
        // return $this->email === 'member@example.com';
        return true;
    }

    protected static function booted(): void
    {
        self::deleting(function (self $user) {
            if ($user->isForceDeleting()) {
                $user->anonymize();

                return false; // batalin force delete, karena diganti anonymize
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'anonymized_at' => 'datetime',
        ];
    }
}
