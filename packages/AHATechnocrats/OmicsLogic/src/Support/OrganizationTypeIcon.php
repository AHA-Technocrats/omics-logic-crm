<?php

namespace AHATechnocrats\OmicsLogic\Support;

use AHATechnocrats\OmicsLogic\Enums\OrganizationType;

class OrganizationTypeIcon
{
    public static function html(?string $type): string
    {
        $organizationType = $type
            ? OrganizationType::tryFrom(strtolower($type))
            : null;

        $paths = match ($organizationType) {
            OrganizationType::University => self::graduationCap(),
            OrganizationType::Institute => self::bankBuilding(),
            OrganizationType::Company => self::skyscraper(),
            OrganizationType::School => self::openBook(),
            OrganizationType::College => self::library(),
            OrganizationType::Other => self::miscCategory(),
            default => self::anyType(),
        };

        return self::image($paths);
    }

    protected static function image(string $paths): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#ffffff">'
            .$paths
            .'</svg>';

        $src = 'data:image/svg+xml;base64,'.base64_encode($svg);

        return '<img src="'.$src.'" width="20" height="20" alt="" '
            .'style="display:block;width:20px;height:20px;flex-shrink:0;" />';
    }

    /** Graduation cap */
    protected static function graduationCap(): string
    {
        return '<path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>'
            .'<path d="M4.14 11.18A2 2 0 0 0 4 12c0 2.21 3.58 4 8 4s8-1.79 8-4a2 2 0 0 0-.14-.82L12 15.6 4.14 11.18z"/>';
    }

    /** Classical bank / institute building */
    protected static function bankBuilding(): string
    {
        return '<path d="M4 10h3v7H4v-7zm5 0h2v7H9v-7zm4 0h2v7h-2v-7zm5 0h3v7h-3v-7zM2 20h20v2H2v-2zm2.5-12L12 4l7.5 4H4.5z"/>';
    }

    /** Skyscraper / corporate tower */
    protected static function skyscraper(): string
    {
        return '<path d="M3 21h18v-2H3v2zm2-2h4V9H5v10zm6 0h2V3h-2v16zm4 0h4V7h-4v12z"/>';
    }

    /** Open book — primary / secondary school */
    protected static function openBook(): string
    {
        return '<path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4zm6 12H6v-2.5l2.5-1.5 2.5 1.5V16zm2-4-2.5-1.5L18 12V4h-4v8z"/>';
    }

    /** Library — college / higher-ed campus */
    protected static function library(): string
    {
        return '<path d="M12 11.55C9.64 9.35 6.48 8 3 8v11c3.48 0 6.64 1.35 9 3.55 2.36-2.2 5.52-3.55 9-3.55V8c-3.48 0-6.64 1.35-9 3.55z"/>';
    }

    /** Grid / category — uncategorized other */
    protected static function miscCategory(): string
    {
        return '<path d="M4 8h4V4H4v4zm6 12h4v-4h-4v4zm-6 0h4v-4H4v4zm0-6h4v-4H4v4zm6 0h4v-4h-4v4zm6-10v4h4V4h-4zm-6 4h4V4h-4v4zm6 6h4v-4h-4v4zm0 6h4v-4h-4v4z"/>';
    }

    /** Layers — any / unspecified type */
    protected static function anyType(): string
    {
        return '<path d="M12 2L2 7l10 5 10-5L12 2zM2 17l10 5 10-5M2 12l10 5 10-5"/>';
    }
}
