<?php

namespace AHATechnocrats\OmicsLogic\Support;

use Illuminate\Support\Str;

class LeadSourceIcon
{
    public static function cell(?string $sourceName): string
    {
        if (! $sourceName) {
            return '—';
        }

        $label = e($sourceName);
        $icon = e(self::iconClass($sourceName));

        return '<span style="display:inline-flex;align-items:center;gap:6px;color:#6b7280;">'
            .'<i class="'.$icon.' text-gray-800 dark:text-white" style="font-size:14px;width:14px;text-align:center;"></i>'
            .'<span class="text-gray-800 dark:text-white">'.$label.'</span>'
            .'</span>';
    }

    protected static function iconClass(string $sourceName): string
    {
        $normalized = Str::lower(trim($sourceName));

        return match (true) {
            Str::contains($normalized, 'portal') => 'fa fa-graduation-cap',
            Str::contains($normalized, ['google form', 'web-form', 'web form', 'webform']) => 'fa fa-wpforms',
            Str::contains($normalized, 'referral') => 'fa fa-share-alt',
            Str::contains($normalized, 'direct') => 'fa fa-bolt',
            Str::contains($normalized, 'email') => 'fa fa-envelope',
            Str::contains($normalized, 'phone') => 'fa fa-phone',
            Str::contains($normalized, 'web') => 'fa fa-globe',
            default => 'fa fa-tag',
        };
    }
}
