<?php

namespace App\Console\Commands\Firebase;

use AHATechnocrats\OmicsLogic\Services\CustomerAnalyticsSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncCustomerAnalyticsCommand extends Command
{
    protected $signature = 'omics:sync-customer-analytics';

    protected $description = 'Syncs Customer Analytics data (Forms, Users, Purchases, Achievements) from Firebase to MySQL';

    public function handle(CustomerAnalyticsSyncService $service): int
    {
        $this->info('Starting Customer Analytics Sync...');

        try {
            $stats = $service->syncAll();

            $this->info(sprintf(
                'Sync completed successfully! Users updated: %d. Enrollments synced: %d.',
                $stats['users_updated'],
                $stats['enrollments_synced']
            ));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Sync failed: ' . $e->getMessage());
            Log::error('Customer Analytics Sync failed: ' . $e->getMessage(), ['exception' => $e]);
            return self::FAILURE;
        }
    }
}
