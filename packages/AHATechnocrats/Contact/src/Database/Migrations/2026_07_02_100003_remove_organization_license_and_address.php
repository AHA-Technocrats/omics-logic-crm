<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $attributeId = DB::table('attributes')
            ->where('entity_type', 'organizations')
            ->where('code', 'address')
            ->value('id');

        if ($attributeId) {
            DB::table('attribute_values')
                ->where('attribute_id', $attributeId)
                ->delete();

            DB::table('attributes')
                ->where('id', $attributeId)
                ->delete();
        }

        Schema::table('organizations', function (Blueprint $table) {
            if (Schema::hasColumn('organizations', 'license_status')) {
                $table->dropColumn('license_status');
            }

            if (Schema::hasColumn('organizations', 'address')) {
                $table->dropColumn('address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            if (! Schema::hasColumn('organizations', 'address')) {
                $table->json('address')->nullable()->after('name');
            }

            if (! Schema::hasColumn('organizations', 'license_status')) {
                $table->string('license_status')->default('none')->after('country_code');
            }
        });
    }
};
