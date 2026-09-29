<?php

namespace AHATechnocrats\OmicsLogic\Services;

use AHATechnocrats\Contact\Models\Organization;
use AHATechnocrats\User\Models\User;

class OrganizationAssigneeResolver
{
    /**
     * Fallback owner for a lead/person: legacy org sales owner, else first admin.
     */
    public function resolve(?Organization $organization): ?int
    {
        if ($organization?->user_id) {
            return (int) $organization->user_id;
        }

        return $this->superAdminId();
    }

    protected function superAdminId(): ?int
    {
        $userId = User::query()
            ->where('status', 1)
            ->whereHas('role', fn ($query) => $query->where('permission_type', 'all'))
            ->orderBy('id')
            ->value('id');

        if ($userId) {
            return (int) $userId;
        }

        return User::query()
            ->where('status', 1)
            ->orderBy('id')
            ->value('id');
    }
}
