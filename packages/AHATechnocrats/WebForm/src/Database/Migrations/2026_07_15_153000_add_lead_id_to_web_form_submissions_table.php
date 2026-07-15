<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('web_form_submissions', 'lead_id')) {
            Schema::table('web_form_submissions', function (Blueprint $table) {
                $table->unsignedInteger('lead_id')->nullable()->after('person_id');
                $table->foreign('lead_id')->references('id')->on('leads')->nullOnDelete();
                $table->index('lead_id');
            });
        }

        // Pair existing person submissions to leads in creation order (1:1 per person).
        $personIds = DB::table('web_form_submissions')
            ->whereNotNull('person_id')
            ->whereNull('lead_id')
            ->distinct()
            ->pluck('person_id');

        foreach ($personIds as $personId) {
            $submissionIds = DB::table('web_form_submissions')
                ->where('person_id', $personId)
                ->whereNull('lead_id')
                ->orderBy('id')
                ->pluck('id');

            $leadIds = DB::table('leads')
                ->where('person_id', $personId)
                ->orderBy('id')
                ->pluck('id');

            $count = min($submissionIds->count(), $leadIds->count());

            for ($i = 0; $i < $count; $i++) {
                DB::table('web_form_submissions')
                    ->where('id', $submissionIds[$i])
                    ->update(['lead_id' => $leadIds[$i]]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('web_form_submissions', 'lead_id')) {
            return;
        }

        Schema::table('web_form_submissions', function (Blueprint $table) {
            $table->dropForeign(['lead_id']);
            $table->dropColumn('lead_id');
        });
    }
};
