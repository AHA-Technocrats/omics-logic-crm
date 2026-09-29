<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('organizations', 'account_owner_id')) {
            return;
        }

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('account_owner_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('organizations', 'account_owner_id')) {
            return;
        }

        Schema::table('organizations', function (Blueprint $table) {
            $table->unsignedInteger('account_owner_id')->nullable()->after('country_code');
        });
    }
};
