<?php

namespace App\Firebase\Services;

use AHATechnocrats\OmicsLogic\Enums\ConnectorType;
use AHATechnocrats\OmicsLogic\Models\Connector;
use AHATechnocrats\WebForm\Models\WebForm;
use App\Firebase\FirebaseManager;

class ConnectorFirebaseSyncService
{
    public const PORTAL_WEB_FORM_ID = 'firebase-portal-sync';

    public function __construct(
        protected FormSyncService $formSyncService,
        protected FirebaseManager $firebaseManager,
    ) {}

    /**
     * @return array{rows_total: int, rows_new: int, rows_merged: int, rows_review: int, rows_failed: int}
     */
    public function sync(Connector $connector): array
    {
        if ($connector->type !== ConnectorType::PortalApi->value) {
            throw new \RuntimeException('This connector type cannot be synced from Firebase.');
        }

        if ($connector->status === 'disabled') {
            throw new \RuntimeException('Connector is disabled. Enable it before syncing.');
        }

        $this->ensureFirebaseConfigured();

        $webFormId = $this->ensureWebFormMapping($connector);
        $batchSize = (int) ($connector->config['batch_size'] ?? config('firebase.sync.batch_size', 50));

        $stats = $this->formSyncService->sync($webFormId, $batchSize);

        return [
            'rows_total' => $stats['synced'] + $stats['skipped'] + $stats['failed'],
            'rows_new' => $stats['synced'],
            'rows_merged' => $stats['skipped'],
            'rows_review' => 0,
            'rows_failed' => $stats['failed'],
        ];
    }

    public function isFirebaseConfigured(): bool
    {
        try {
            $this->ensureFirebaseConfigured();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function resolveWebFormId(?Connector $connector = null): ?int
    {
        $connector ??= Connector::query()
            ->where('type', ConnectorType::PortalApi->value)
            ->first();

        if ($connector) {
            return $this->ensureWebFormMapping($connector);
        }

        $fallback = (int) config('firebase.sync.default_web_form_id');

        return $fallback > 0 ? $fallback : $this->resolveOrCreatePortalWebForm()->id;
    }

    /**
     * Resolve a valid CRM web form for Firestore imports, creating the
     * dedicated portal mapping form when needed, and persist it on the connector.
     */
    public function ensureWebFormMapping(Connector $connector): int
    {
        $configuredId = (int) ($connector->config['web_form_id'] ?? 0);

        $webForm = $configuredId > 0
            ? WebForm::query()->find($configuredId)
            : null;

        if (! $webForm) {
            $fallback = (int) config('firebase.sync.default_web_form_id');
            $webForm = $fallback > 0 ? WebForm::query()->find($fallback) : null;
        }

        if (! $webForm) {
            $webForm = $this->resolveOrCreatePortalWebForm();
        }

        $config = $connector->config ?? [];

        if ((int) ($config['web_form_id'] ?? 0) !== (int) $webForm->id) {
            $config['web_form_id'] = (int) $webForm->id;
            $connector->update(['config' => $config]);
        }

        return (int) $webForm->id;
    }

    public function resolveOrCreatePortalWebForm(): WebForm
    {
        return WebForm::query()->firstOrCreate(
            ['form_id' => self::PORTAL_WEB_FORM_ID],
            [
                'title' => 'OmicsLogic Portal (Firebase)',
                'description' => 'Internal mapping form for Firestore portal submissions synced into CRM leads.',
                'submit_button_label' => 'Submit',
                'submit_success_action' => 'message',
                'submit_success_content' => 'Thank you',
                'create_lead' => true,
                'is_active' => true,
                'allow_org_create' => true,
                'organization_field' => 'optional',
                'background_color' => '#ffffff',
                'form_background_color' => '#ffffff',
                'form_title_color' => '#000000',
                'form_submit_button_color' => '#0E9F6E',
                'attribute_label_color' => '#000000',
            ],
        );
    }

    public function shouldRunScheduledSync(Connector $connector): bool
    {
        if ($connector->status !== 'connected') {
            return false;
        }

        $schedule = (string) ($connector->config['sync_schedule'] ?? 'manual');

        if ($schedule === 'manual') {
            return false;
        }

        if (! $connector->last_sync_at) {
            return true;
        }

        return match ($schedule) {
            'hourly' => $connector->last_sync_at->lt(now()->subHour()),
            'daily' => $connector->last_sync_at->lt(now()->subDay()),
            'weekly' => $connector->last_sync_at->lt(now()->subWeek()),
            default => false,
        };
    }

    protected function ensureFirebaseConfigured(): void
    {
        $json = config('firebase.credentials_json');
        $path = config('firebase.credentials');

        $hasJson = is_string($json) && $json !== '';
        $hasFile = is_string($path) && $path !== '' && is_readable($path);

        if (! $hasJson && ! $hasFile) {
            throw new \RuntimeException('Firebase service account credentials are not configured.');
        }

        $this->firebaseManager->firestore();
    }
}
