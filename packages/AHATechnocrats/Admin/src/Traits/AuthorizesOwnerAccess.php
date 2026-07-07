<?php

namespace AHATechnocrats\Admin\Traits;

use Illuminate\Http\RedirectResponse;

trait AuthorizesOwnerAccess
{
    /**
     * Redirect when the current user cannot access a record owned by the given user.
     */
    protected function authorizeOwner(?int $ownerUserId, string $redirectRoute): ?RedirectResponse
    {
        $userIds = bouncer()->getAuthorizedUserIds();

        if ($userIds && ! in_array($ownerUserId, $userIds)) {
            return redirect()->route($redirectRoute);
        }

        return null;
    }

    /**
     * Redirect when the current user cannot access a record owned by any of the given users.
     *
     * @param  array<int|null>  $ownerUserIds
     */
    protected function authorizeAnyOwner(array $ownerUserIds, string $redirectRoute): ?RedirectResponse
    {
        $userIds = bouncer()->getAuthorizedUserIds();

        if (! $userIds) {
            return null;
        }

        $ownerUserIds = array_values(array_filter($ownerUserIds));

        if (empty(array_intersect($ownerUserIds, $userIds))) {
            return redirect()->route($redirectRoute);
        }

        return null;
    }
}
