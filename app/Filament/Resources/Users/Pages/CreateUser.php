<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Actions\Users\CreateUserAction;
use App\Filament\Concerns\InteractsWithCuratorAvatarUpload;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

final class CreateUser extends CreateRecord
{
    use InteractsWithCuratorAvatarUpload;

    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        /** @var array{name: string, email: string, password: string, avatar_curator_id?: int|null, email_verified_at?: string|null} $data */
        return resolve(CreateUserAction::class)->handle($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->resolveAvatarUploadData($data);
    }
}
