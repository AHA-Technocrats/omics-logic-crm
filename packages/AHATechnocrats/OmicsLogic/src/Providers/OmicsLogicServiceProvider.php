<?php

namespace AHATechnocrats\OmicsLogic\Providers;

use AHATechnocrats\OmicsLogic\Services\Audit\AuditDescriber;
use AHATechnocrats\OmicsLogic\Services\Audit\AuditDiffer;
use AHATechnocrats\OmicsLogic\Services\Audit\AuditEventSubscriber;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class OmicsLogicServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'omicslogic');

        $this->app->register(ModuleServiceProvider::class);

        $this->registerAuditListeners();

        if ($this->app->runningInConsole()) {
            $this->commands([]);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__).'/Config/omicslogic.php', 'omicslogic');
        $this->mergeConfigFrom(dirname(__DIR__).'/Config/audit.php', 'audit');

        $this->app->singleton(AuditDiffer::class, fn () => new AuditDiffer(
            redactedKeys: (array) config('audit.redacted_keys', []),
            ignoredKeys: (array) config('audit.ignored_keys', []),
        ));

        $this->app->singleton(AuditDescriber::class);

        $this->app->singleton(AuditEventSubscriber::class);
    }

    /**
     * Attach the audit subscriber to every configured CRM lifecycle event.
     */
    protected function registerAuditListeners(): void
    {
        if (! config('audit.enabled', true)) {
            return;
        }

        $subscriber = $this->app->make(AuditEventSubscriber::class);

        foreach ($subscriber->events() as $event) {
            // For a specific (non-wildcard) event, Laravel passes the dispatched
            // payload spread as arguments, so we capture the event name here and
            // collect the payload via a variadic parameter.
            Event::listen($event, function (...$args) use ($subscriber, $event) {
                $subscriber->handle($event, $args);
            });
        }
    }
}
