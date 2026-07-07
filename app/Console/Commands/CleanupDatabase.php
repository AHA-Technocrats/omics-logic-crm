<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm:cleanup 
                            {--all : Clean all operational data (default)}
                            {--submissions-only : Keep webform configurations and only clean submissions}
                            {--include-products : Also clean product catalog tables}
                            {--force : Run without warning/confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean all leads, persons, organisations, webform submissions, audit logs, and other operational data, resetting auto-increment IDs to 0/1';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (! $this->option('force')) {
            if (! $this->confirm('WARNING: This will permanently delete all requested CRM operational data and reset auto-increment indexes. Are you sure you want to proceed?')) {
                $this->info('Cleanup cancelled.');

                return self::FAILURE;
            }
        }

        $this->info('Starting database cleanup...');

        $submissionsOnly = $this->option('submissions-only');
        $includeProducts = $this->option('include-products');

        // 1. Determine tables to truncate
        $tablesToTruncate = [
            // Lead operational tables
            'leads',
            'lead_products',
            'lead_activities',
            'lead_tags',
            'lead_quotes',

            // Person operational tables
            'persons',
            'person_activities',
            'person_tags',

            // Organization operational tables
            'organizations',

            // Audit / Sync / Review logs
            'omics_audit_logs',
            'omics_connector_sync_runs',
            'omics_merge_review_pairs',
            'omics_organization_aliases',

            // Email operational tables
            'emails',
            'email_attachments',
            'email_tags',

            // Quotes operational tables
            'quotes',
            'quote_items',

            // Activities operational tables
            'activities',
            'activity_participants',
            'activity_files',
        ];

        if ($submissionsOnly) {
            $tablesToTruncate[] = 'web_form_submissions';
        } else {
            $tablesToTruncate[] = 'web_form_submissions';
            $tablesToTruncate[] = 'web_forms';
            $tablesToTruncate[] = 'web_form_attributes';
        }

        if ($includeProducts) {
            $tablesToTruncate[] = 'products';
            $tablesToTruncate[] = 'product_inventories';
            $tablesToTruncate[] = 'product_activities';
            $tablesToTruncate[] = 'product_tags';
            $tablesToTruncate[] = 'omics_product_aliases';
        }

        // 2. Perform truncation for each table
        foreach ($tablesToTruncate as $table) {
            if (Schema::hasTable($table)) {
                $this->comment("Truncating and resetting index for table: {$table}");
                $this->truncateTable($table);
            } else {
                $this->warn("Table not found, skipping: {$table}");
            }
        }

        // 3. Clean up attribute values selectively (dynamic attributes)
        if (Schema::hasTable('attribute_values')) {
            $entityTypes = ['leads', 'persons', 'organizations', 'activities', 'quotes', 'emails'];
            if ($includeProducts) {
                $entityTypes[] = 'products';
            }

            $this->comment('Cleaning up dynamic attribute values for entity types: '.implode(', ', $entityTypes));

            DB::table('attribute_values')->whereIn('entity_type', $entityTypes)->delete();

            // If empty, truncate to reset the auto-increment ID to 1
            if (DB::table('attribute_values')->count() === 0) {
                $this->comment('attribute_values table is empty, resetting its index...');
                $this->truncateTable('attribute_values');
            }
        }

        $this->info('Database cleanup completed successfully!');

        return self::SUCCESS;
    }

    /**
     * Truncate a table and reset its auto-increment / index
     */
    protected function truncateTable(string $table): void
    {
        $driver = DB::connection()->getDriverName();

        switch ($driver) {
            case 'mysql':
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                DB::table($table)->truncate();
                DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = 1;");
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                break;

            case 'pgsql':
                DB::statement("TRUNCATE TABLE \"{$table}\" RESTART IDENTITY CASCADE;");
                break;

            case 'sqlite':
                DB::statement('PRAGMA foreign_keys = OFF;');
                DB::table($table)->delete();
                DB::statement('DELETE FROM sqlite_sequence WHERE name = ?;', [$table]);
                DB::statement('PRAGMA foreign_keys = ON;');
                break;

            default:
                Schema::disableForeignKeyConstraints();
                DB::table($table)->truncate();
                Schema::enableForeignKeyConstraints();
                break;
        }
    }
}
