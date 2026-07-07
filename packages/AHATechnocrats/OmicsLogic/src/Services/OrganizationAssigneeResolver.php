<?php

namespace AHATechnocrats\OmicsLogic\Services;

use AHATechnocrats\Contact\Models\Organization;
use AHATechnocrats\User\Models\User;

class OrganizationAssigneeResolver
{
    /**
     * Resolve the CRM user who should own a lead/person for this organization.
     */
    public function resolve(?Organization $organization): ?int
    {
        if ($organization?->account_owner_id) {
            return (int) $organization->account_owner_id;
        }

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
