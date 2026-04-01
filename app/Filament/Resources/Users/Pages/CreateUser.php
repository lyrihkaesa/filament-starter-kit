<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Actions\Users\CreateUserAction;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

final class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        /** @var array{name: string, email: string, password: string, avatar_curator_id?: string|null, email_verified_at?: string|null, roles?: array<int, string>} $data */
        return resolve(CreateUserAction::class)->handle($data);
    }
}
