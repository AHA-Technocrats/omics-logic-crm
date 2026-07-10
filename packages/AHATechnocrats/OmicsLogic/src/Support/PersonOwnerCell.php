<?php

namespace AHATechnocrats\OmicsLogic\Support;

class PersonOwnerCell
{
    public static function cell(?string $ownerName, ?string $ownerImage, int $personId): string
    {
        if (empty($ownerName)) {
            return self::unassignedBadge($personId);
        }

        return self::assignedOwner($ownerName, $ownerImage, $personId);
    }

    protected static function assignedOwner(string $name, ?string $image, int $personId): string
    {
        $content = '<div style="display:flex;align-items:center;gap:8px;">'
            .UserProfileAvatar::html($name, $image)
            .'<span style="color:#1f2937;font-size:14px;">'.e($name).'</span>'
            .'</div>';

        if (! bouncer()->hasPermission('persons.edit')) {
            return $content;
        }

        $url = e(route('admin.contacts.persons.edit', $personId));

        return '<a href="'.$url.'" style="display:inline-flex;text-decoration:none;color:inherit;cursor:pointer;" title="'.e(trans('omicslogic::app.fields.owner-profile')).'">'
            .$content
            .'</a>';
    }

    protected static function unassignedBadge(int $personId): string
    {
        $label = e(trans('omicslogic::app.fields.unassigned'));

        $badge = '<span style="display:inline-flex;align-items:center;gap:6px;background-color:#ffedd5;color:#92400e;border-radius:9999px;padding:4px 12px;font-size:12px;font-weight:600;">'
            .'<i class="fa fa-user-plus" style="font-size:12px;"></i>'
            .$label
            .'</span>';

        if (! bouncer()->hasPermission('persons.edit')) {
            return $badge;
        }

        $url = e(route('admin.contacts.persons.edit', $personId));

        return '<a href="'.$url.'" style="display:inline-flex;text-decoration:none;color:inherit;cursor:pointer;">'
            .$badge
            .'</a>';
    }
}
