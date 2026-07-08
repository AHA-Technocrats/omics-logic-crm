<?php

namespace App\Console\Commands\Firebase;

use AHATechnocrats\OmicsLogic\Enums\ConnectorType;
use AHATechnocrats\OmicsLogic\Models\Connector;
use AHATechnocrats\OmicsLogic\Services\ConnectorSyncService;
use App\Firebase\Services\ConnectorFirebaseSyncService;
use App\Firebase\Services\FormSyncService;
use Illuminate\Console\Command;

class SyncFirebaseFormsCommand extends Command
{
    protected $signature = 'firebase:sync-forms';

    protected $description = 'Sync Firestore website forms into CRM leads and submissions';

    public function handle(
        ConnectorSyncService $connectorSyncService,
        ConnectorFirebaseSyncService $connectorFirebaseSyncService,
        FormSyncService $formSyncService,
    ): int {
        $connector = Connector::query()
            ->where('type', ConnectorType::PortalApi->value)
            ->first();

        if ($connector) {
            if ($connector->status === 'connected' && $connectorFirebaseSyncService->shouldRunScheduledSync($connector)) {
                try {
                    $run = $connectorSyncService->sync($connector);

                    $this->info(sprintf(
                        'Firebase connector sync complete. Rows: %d, New: %d, Skipped: %d, Failed: %d',
                        $run->rows_total ?? 0,
                        $run->rows_new ?? 0,
                        $run->rows_merged ?? 0,
                        $run->rows_failed ?? 0,
                    ));
                } catch (\Throwable $exception) {
                    $this->error('Firebase connector sync failed: '.$exception->getMessage());
                }

                return self::SUCCESS;
            }

            if (($connector->config['sync_schedule'] ?? 'manual') !== 'manual') {
                $this->comment('Firebase connector sync skipped — waiting for next scheduled interval.');

                return self::SUCCESS;
            }

            $this->comment('Firebase connector sync is manual only. Use Sync now on the Connectors page.');

            return self::SUCCESS;
        }

        $webFormId = $connectorFirebaseSyncService->resolveWebFormId();
        $stats = $formSyncService->sync($webFormId);

        $this->info(sprintf(
            'Firebase form sync complete. Synced: %d, Skipped: %d, Failed: %d',
            $stats['synced'],
            $stats['skipped'],
            $stats['failed'],
        ));

        return self::SUCCESS;
    }
}
