<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $attributeIds = DB::table('attributes')
            ->where('entity_type', 'persons')
            ->where(function ($query) {
                $query->where('code', 'job_title')
                    ->orWhere('is_user_defined', 1);
            })
            ->pluck('id');

        if ($attributeIds->isNotEmpty()) {
            DB::table('attribute_values')
                ->whereIn('attribute_id', $attributeIds)
                ->delete();

            DB::table('attributes')
                ->whereIn('id', $attributeIds)
                ->delete();
        }
    }

    public function down(): void
    {
        // Attributes are restored via installer seeder on fresh installs only.
    }
};
