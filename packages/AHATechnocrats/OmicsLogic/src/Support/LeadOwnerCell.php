<?php

namespace AHATechnocrats\OmicsLogic\Support;

class LeadOwnerCell
{
    public static function cell(
        ?string $ownerName,
        ?string $ownerImage,
        int $leadId,
        ?int $personId = null,
    ): string {
        if (empty($ownerName)) {
            return self::assignBadge($leadId, $personId);
        }

        return self::assignedOwner($ownerName, $ownerImage, $leadId, $personId);
    }

    protected static function assignedOwner(
        string $name,
        ?string $image,
        int $leadId,
        ?int $personId = null,
    ): string {
        $content = '<div style="display:flex;align-items:center;gap:8px;">'
            .UserProfileAvatar::html($name, $image)
            .'<span style="font-size:14px;" class="text-gray-800 dark:text-white">'.e($name).'</span>'
            .'</div>';

        if (! bouncer()->hasPermission('leads.edit')) {
            return $content;
        }

        $url = e(self::editUrl($leadId, $personId));

        return '<a href="'.$url.'" style="display:inline-flex;text-decoration:none;color:inherit;cursor:pointer;" title="'.e(trans('omicslogic::app.fields.owner-profile')).'">'
            .$content
            .'</a>';
    }

    protected static function assignBadge(int $leadId, ?int $personId = null): string
    {
        $label = e(trans('omicslogic::app.fields.assign'));

        $badge = '<span style="display:inline-flex;align-items:center;gap:6px;background-color:#ffedd5;color:#92400e;border-radius:9999px;padding:4px 12px;font-size:12px;font-weight:600;">'
            .'<i class="fa fa-user-plus" style="font-size:12px;"></i>'
            .$label
            .'</span>';

        if (! bouncer()->hasPermission('leads.edit')) {
            return $badge;
        }

        $url = e(self::editUrl($leadId, $personId));

        return '<a href="'.$url.'" style="display:inline-flex;text-decoration:none;color:inherit;cursor:pointer;">'
            .$badge
            .'</a>';
    }

    protected static function editUrl(int $leadId, ?int $personId = null): string
    {
        $parameters = ['id' => $leadId];

        if ($personId) {
            $parameters['return'] = 'person-leads';
            $parameters['person_id'] = $personId;
        }

        return route('admin.leads.edit', $parameters);
    }
}
