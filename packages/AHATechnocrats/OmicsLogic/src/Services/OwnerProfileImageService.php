<?php

namespace AHATechnocrats\OmicsLogic\Services;

use AHATechnocrats\User\Repositories\UserRepository;

class OwnerProfileImageService
{
    public function __construct(protected UserRepository $userRepository) {}

    public function syncFromRequest(
        string $field,
        int $ownerId,
        bool $removeWhenMissing = false,
        array $editPermissions = [],
    ): void {
        if (! $ownerId || ! $this->canUpdate($ownerId, $editPermissions)) {
            return;
        }

        $file = $this->userRepository->profileImageFromRequest($field);

        $this->userRepository->updateProfileImage($ownerId, $file, $removeWhenMissing);
    }

    public function canUpdate(int $ownerId, array $editPermissions = []): bool
    {
        $user = auth()->guard('user')->user();

        if (! $user) {
            return false;
        }

        if ((int) $user->id === $ownerId) {
            return true;
        }

        foreach ($editPermissions as $permission) {
            if (bouncer()->hasPermission($permission)) {
                return true;
            }
        }

        return bouncer()->hasPermission('settings.user.users.edit');
    }
}
