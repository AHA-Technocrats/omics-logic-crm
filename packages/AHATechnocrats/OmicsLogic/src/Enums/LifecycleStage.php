<?php

namespace AHATechnocrats\OmicsLogic\Enums;

enum LifecycleStage: string
{
    /**
     * System configuration key holding the admin-selected default stage.
     */
    public const CONFIG_KEY = 'general.settings.contacts.default_lifecycle_stage';

    case Subscriber = 'subscriber';
    case Lead = 'lead';
    case Engaged = 'engaged';
    case Customer = 'customer';
    case Dormant = 'dormant';

    public function label(): string
    {
        return match ($this) {
            self::Subscriber => 'Subscriber',
            self::Lead => 'Lead',
            self::Engaged => 'Engaged',
            self::Customer => 'Customer',
            self::Dormant => 'Dormant',
        };
    }

    /**
     * The default lifecycle stage, resolved from the system configuration.
     *
     * Falls back to the first stage when no configuration value is set.
     */
    public static function default(): self
    {
        $configured = core()->getConfigData(self::CONFIG_KEY);

        return self::tryFrom((string) $configured) ?? self::Subscriber;
    }

    /**
     * Stages formatted as {title, value} option pairs.
     *
     * @return array<int, array{title: string, value: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $stage): array => ['title' => $stage->label(), 'value' => $stage->value],
            self::cases(),
        );
    }
}
