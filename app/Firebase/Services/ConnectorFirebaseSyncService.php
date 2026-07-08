<?php

namespace App\Firebase\Services;

use AHATechnocrats\OmicsLogic\Enums\ConnectorType;
use AHATechnocrats\OmicsLogic\Models\Connector;
use App\Firebase\FirebaseManager;

class ConnectorFirebaseSyncService
{
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

        $webFormId = (int) ($connector->config['web_form_id'] ?? config('firebase.sync.default_web_form_id'));

        if ($webFormId <= 0) {
            throw new \RuntimeException('Select a web form to map Firestore submissions into CRM leads.');
        }

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

        $webFormId = (int) ($connector?->config['web_form_id'] ?? 0);

        if ($webFormId > 0) {
            return $webFormId;
        }

        $fallback = (int) config('firebase.sync.default_web_form_id');

        return $fallback > 0 ? $fallback : null;
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
