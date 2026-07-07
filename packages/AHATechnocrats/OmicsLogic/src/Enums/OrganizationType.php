<?php

namespace AHATechnocrats\OmicsLogic\Enums;

enum OrganizationType: string
{
    case University = 'university';
    case Institute = 'institute';
    case College = 'college';
    case School = 'school';
    case Company = 'company';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::University => 'University',
            self::Institute => 'Institute',
            self::College => 'College',
            self::School => 'School',
            self::Company => 'Company',
            self::Other => 'Other',
        };
    }
}
