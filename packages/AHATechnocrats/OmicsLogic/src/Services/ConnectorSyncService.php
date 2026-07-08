<?php

namespace AHATechnocrats\OmicsLogic\Services;

use AHATechnocrats\OmicsLogic\Enums\ConnectorType;
use AHATechnocrats\OmicsLogic\Models\Connector;
use AHATechnocrats\OmicsLogic\Models\ConnectorSyncRun;
use App\Firebase\Services\ConnectorFirebaseSyncService;

class ConnectorSyncService
{
    public function __construct(
        protected ConnectorFirebaseSyncService $connectorFirebaseSyncService,
    ) {}

    public function sync(Connector $connector): ConnectorSyncRun
    {
        $startedAt = now();

        $run = ConnectorSyncRun::query()->create([
            'connector_id' => $connector->id,
            'status' => 'running',
            'started_at' => $startedAt,
        ]);

        try {
            $stats = $this->runSync($connector);

            $run->update([
                'rows_total' => $stats['rows_total'],
                'rows_new' => $stats['rows_new'],
                'rows_merged' => $stats['rows_merged'],
                'rows_review' => $stats['rows_review'],
                'rows_failed' => $stats['rows_failed'],
                'status' => 'success',
                'finished_at' => now(),
            ]);

            $connector->update([
                'last_sync_at' => now(),
                'last_sync_status' => 'success',
                'status' => $connector->status === 'disabled' ? 'connected' : $connector->status,
            ]);
        } catch (\Throwable $exception) {
            $run->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);

            $connector->update([
                'last_sync_status' => 'failed',
            ]);

            throw $exception;
        }

        AuditLogger::log(
            action: 'connector_sync',
            entityType: 'connector',
            entityId: $connector->id,
            after: [
                'connector' => $connector->name,
                'rows_total' => $run->rows_total,
                'rows_new' => $run->rows_new,
                'status' => $run->status,
            ],
        );

        return $run->fresh();
    }

    /**
     * @return array{rows_total: int, rows_new: int, rows_merged: int, rows_review: int, rows_failed: int}
     */
    protected function runSync(Connector $connector): array
    {
        return match ($connector->type) {
            ConnectorType::PortalApi->value => $this->connectorFirebaseSyncService->sync($connector),
            default => throw new \RuntimeException('This connector type cannot be synced manually.'),
        };
    }

    public function updateConfig(Connector $connector, array $data): Connector
    {
        $before = $connector->only(['name', 'status', 'config']);

        $connector->update([
            'name' => $data['name'] ?? $connector->name,
            'status' => $data['status'] ?? $connector->status,
            'config' => array_merge($connector->config ?? [], $data['config'] ?? []),
        ]);

        AuditLogger::log(
            action: 'connector_configure',
            entityType: 'connector',
            entityId: $connector->id,
            before: $before,
            after: $connector->only(['name', 'status', 'config']),
        );

        return $connector->fresh();
    }
}
