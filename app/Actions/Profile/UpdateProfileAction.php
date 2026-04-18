<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Actions\Media\SyncMediaUsageAction;
use App\Actions\ResolveMediaAction;
use App\Models\CuratorMedia;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final readonly class UpdateProfileAction
{
    public function __construct(
        private ResolveMediaAction $resolveMediaAction,
        private SyncMediaUsageAction $syncMediaUsageAction,
    ) {}

    /**
     * @param array{
     *     name?: string,
     *     avatar_upload_id?: string|null,
     *     avatar_media_id?: string|null,
     *     locale?: string,
     *     timezone?: string,
     *     theme?: string,
     * } $data
     */
    public function handle(User $user, array $data): User
    {
        /** @var User $updatedUser */
        $updatedUser = DB::transaction(function () use ($user, $data): User {
            if (array_key_exists('avatar_upload_id', $data) && $data['avatar_upload_id']) {
                $media = $this->resolveMediaAction->handle($data['avatar_upload_id'], null, 'user_avatar');
                if ($media instanceof CuratorMedia) {
                    $data['avatar_curator_id'] = $media->id;
                }
            } elseif (array_key_exists('avatar_media_id', $data) && $data['avatar_media_id']) {
                $data['avatar_curator_id'] = $data['avatar_media_id'];
            }

            /** @var array<string, mixed> $updateData */
            $updateData = Arr::only($data, ['name', 'locale', 'timezone', 'theme', 'avatar_curator_id']);

            $user->update($updateData);

            if (array_key_exists('avatar_curator_id', $updateData)) {
                $avatarId = is_scalar($updateData['avatar_curator_id']) ? (string) $updateData['avatar_curator_id'] : null;
                $this->syncMediaUsageAction->handle($user, 'avatar_curator_id', $avatarId);
            }

            return $user->fresh() ?: $user;
        });

        return $updatedUser;
    }
}
