<?php

namespace AHATechnocrats\Attribute\Providers;

use AHATechnocrats\Attribute\Models\Attribute;
use AHATechnocrats\Attribute\Models\AttributeOption;
use AHATechnocrats\Attribute\Models\AttributeValue;
use AHATechnocrats\Core\Providers\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    /**
     * @var array{
     *  0: class-string<Attribute>,
     *  1: class-string<AttributeOption>,
     *  2: class-string<AttributeValue>
     * }
     */
    protected $models = [
        Attribute::class,
        AttributeOption::class,
        AttributeValue::class,
    ];
}
