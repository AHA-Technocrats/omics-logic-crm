<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $columns = [
                'type' => fn () => $table->string('type')->default('other')->after('name'),
                'country_code' => fn () => $table->string('country_code', 100)->nullable()->after('type'),
                'account_owner_id' => fn () => $table->unsignedInteger('account_owner_id')->nullable()->after('country_code'),
                'normalized_name' => fn () => $table->string('normalized_name')->nullable()->unique()->after('account_owner_id'),
                'website' => fn () => $table->string('website')->nullable()->after('normalized_name'),
                'notes' => fn () => $table->text('notes')->nullable()->after('website'),
                'contacts_count' => fn () => $table->unsignedInteger('contacts_count')->default(0)->after('notes'),
                'engaged_count' => fn () => $table->unsignedInteger('engaged_count')->default(0)->after('contacts_count'),
                'customers_count' => fn () => $table->unsignedInteger('customers_count')->default(0)->after('engaged_count'),
            ];

            foreach ($columns as $name => $callback) {
                if (! Schema::hasColumn('organizations', $name)) {
                    $callback();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            foreach (['type', 'country_code', 'account_owner_id', 'normalized_name', 'website', 'notes', 'contacts_count', 'engaged_count', 'customers_count'] as $column) {
                if (Schema::hasColumn('organizations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
