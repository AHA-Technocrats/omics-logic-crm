<?php

use AHATechnocrats\User\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Remap legacy contacts.* ACL keys to top-level persons / organizations keys.
     */
    public function up(): void
    {
        $map = [
            'contacts' => null,
            'contacts.persons' => 'persons',
            'contacts.persons.create' => 'persons.create',
            'contacts.persons.create.quick-create' => 'persons.create.quick-create',
            'contacts.persons.view' => 'persons.view',
            'contacts.persons.edit' => 'persons.edit',
            'contacts.persons.delete' => 'persons.delete',
            'contacts.organizations' => 'organizations',
            'contacts.organizations.create' => 'organizations.create',
            'contacts.organizations.create.quick-create' => 'organizations.create.quick-create',
            'contacts.organizations.view' => 'organizations.view',
            'contacts.organizations.edit' => 'organizations.edit',
            'contacts.organizations.delete' => 'organizations.delete',
        ];

        Role::query()->where('permission_type', 'custom')->each(function (Role $role) use ($map) {
            $permissions = collect($role->permissions ?? [])
                ->map(fn (string $permission) => $map[$permission] ?? $permission)
                ->filter(fn ($permission) => is_string($permission) && $permission !== 'contacts')
                ->unique()
                ->values()
                ->all();

            $role->update(['permissions' => $permissions]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $map = [
            'persons' => 'contacts.persons',
            'persons.create' => 'contacts.persons.create',
            'persons.create.quick-create' => 'contacts.persons.create.quick-create',
            'persons.view' => 'contacts.persons.view',
            'persons.edit' => 'contacts.persons.edit',
            'persons.delete' => 'contacts.persons.delete',
            'organizations' => 'contacts.organizations',
            'organizations.create' => 'contacts.organizations.create',
            'organizations.create.quick-create' => 'contacts.organizations.create.quick-create',
            'organizations.view' => 'contacts.organizations.view',
            'organizations.edit' => 'contacts.organizations.edit',
            'organizations.delete' => 'contacts.organizations.delete',
        ];

        Role::query()->where('permission_type', 'custom')->each(function (Role $role) use ($map) {
            $permissions = collect($role->permissions ?? [])
                ->flatMap(function (string $permission) use ($map) {
                    if (! isset($map[$permission])) {
                        return [$permission];
                    }

                    return [$map[$permission], 'contacts'];
                })
                ->unique()
                ->values()
                ->all();

            $role->update(['permissions' => $permissions]);
        });
    }
};
