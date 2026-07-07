<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('omics_audit_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('omics_audit_logs', 'event')) {
                $table->string('event')->nullable()->after('action');
            }

            if (! Schema::hasColumn('omics_audit_logs', 'description')) {
                $table->text('description')->nullable()->after('event');
            }

            if (! Schema::hasColumn('omics_audit_logs', 'route')) {
                $table->string('route')->nullable()->after('after');
            }

            if (! Schema::hasColumn('omics_audit_logs', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('route');
            }

            if (! Schema::hasColumn('omics_audit_logs', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }
        });

        // Adding indexes for common audit filters. Wrapped defensively so the
        // migration is idempotent across database drivers that report duplicate
        // index creation differently.
        foreach (['action', ['actor_type', 'actor_id']] as $columns) {
            try {
                Schema::table('omics_audit_logs', function (Blueprint $table) use ($columns) {
                    $table->index($columns);
                });
            } catch (Throwable $e) {
                // Index already exists — safe to ignore.
            }
        }
    }

    public function down(): void
    {
        Schema::table('omics_audit_logs', function (Blueprint $table) {
            foreach (['event', 'description', 'route', 'ip_address', 'user_agent'] as $column) {
                if (Schema::hasColumn('omics_audit_logs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
