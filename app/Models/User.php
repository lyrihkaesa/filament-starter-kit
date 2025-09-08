<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

final class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

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
