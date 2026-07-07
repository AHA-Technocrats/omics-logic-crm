<?php

namespace AHATechnocrats\OmicsLogic\Enums;

enum SpamStatus: string
{
    case Clean = 'clean';
    case Suspect = 'suspect';
    case Blocked = 'blocked';
}
