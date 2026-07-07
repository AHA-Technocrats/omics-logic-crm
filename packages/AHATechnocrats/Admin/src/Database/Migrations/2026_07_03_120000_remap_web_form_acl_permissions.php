<?php

use AHATechnocrats\User\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Remap web form ACL keys from settings submenu to top-level navigation.
     */
    public function up(): void
    {
        $map = [
            'settings.other_settings.web_forms' => 'web_forms',
            'settings.other_settings.web_forms.view' => 'web_forms.view',
            'settings.other_settings.web_forms.create' => 'web_forms.create',
            'settings.other_settings.web_forms.edit' => 'web_forms.edit',
            'settings.other_settings.web_forms.delete' => 'web_forms.delete',
            'activities' => 'web_forms',
        ];

        Role::query()->where('permission_type', 'custom')->each(function (Role $role) use ($map) {
            $permissions = collect($role->permissions ?? [])
                ->map(fn (string $permission) => $map[$permission] ?? $permission)
                ->unique()
                ->values()
                ->all();

            $role->update(['permissions' => $permissions]);
        });
    }

    public function down(): void
    {
        $map = [
            'web_forms' => 'settings.other_settings.web_forms',
            'web_forms.view' => 'settings.other_settings.web_forms.view',
            'web_forms.create' => 'settings.other_settings.web_forms.create',
            'web_forms.edit' => 'settings.other_settings.web_forms.edit',
            'web_forms.delete' => 'settings.other_settings.web_forms.delete',
        ];

        Role::query()->where('permission_type', 'custom')->each(function (Role $role) use ($map) {
            $permissions = collect($role->permissions ?? [])
                ->map(fn (string $permission) => $map[$permission] ?? $permission)
                ->unique()
                ->values()
                ->all();

            $role->update(['permissions' => $permissions]);
        });
    }
};
