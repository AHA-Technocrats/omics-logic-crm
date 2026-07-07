<?php

namespace AHATechnocrats\OmicsLogic\Support;

use AHATechnocrats\OmicsLogic\Enums\LifecycleStage;

class LifecycleStageOptions
{
    /**
     * Options consumed by the system configuration select field.
     *
     * @return array<int, array{title: string, value: string}>
     */
    public function options(): array
    {
        return LifecycleStage::options();
    }
}
