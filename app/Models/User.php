<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

final class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use HasPanelShield;
    use HasRoles;
    use HasUuids;
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'anonymized_at',
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
        return $this->anonymized_at !== null;
    }

    public function anonymize(): void
    {
        $this->update([
            'name' => 'Anonymous User',
            'email' => 'anonymous_'.uuid_create().'@example.com',
            'password' => bcrypt(Str::random(40)),
            'anonymized_at' => now(),
        ]);
    }

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return true;
    }

    public function canImpersonate(): bool
    {
        // Let's prevent being impersonated by other users at our own company
        // example:
        // return $this->email === 'member@example.com';
        return true;
    }

    public function canBeImpersonated(): bool
    {
        // Let's prevent being impersonated by other users at our own company
        // example:
        // return $this->email === 'member@example.com';
        return true;
    }

    /**
     * Filament override implements HasAvatar
     *
     * @see HasAvatar
     */
    // @codeCoverageIgnoreStart
    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->avatar === null) {
            // return asset('images/thumbnails/images-dark-500x500.jpg');
            return null;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(config()->string('filament.default_filesystem_disk'));

        if (config('filament.default_filesystem_disk') === 'local') {
            return $disk->temporaryUrl($this->avatar, now()->addMinutes(5));
        }

        return $disk->url($this->avatar);
    }
    // @codeCoverageIgnoreEnd

    // @codeCoverageIgnoreStart
    protected static function booted(): void
    {
        self::deleting(function (self $user) {
            if ($user->isForceDeleting()) {
                $user->anonymize();

                return false; // batalin force delete, karena diganti anonymize
            }
        });
    }
    // @codeCoverageIgnoreEnd

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
