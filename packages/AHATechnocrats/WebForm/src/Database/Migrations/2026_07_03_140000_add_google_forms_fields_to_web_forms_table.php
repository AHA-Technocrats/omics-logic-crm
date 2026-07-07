<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_forms', function (Blueprint $table) {
            if (! Schema::hasColumn('web_forms', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('create_lead');
            }

            if (! Schema::hasColumn('web_forms', 'send_submitter_email')) {
                $table->boolean('send_submitter_email')->default(false)->after('is_active');
            }

            if (! Schema::hasColumn('web_forms', 'email_template_id')) {
                $table->unsignedInteger('email_template_id')->nullable()->after('send_submitter_email');
            }

            if (! Schema::hasColumn('web_forms', 'campaign_scope')) {
                $table->string('campaign_scope', 20)->default('all')->after('product_id');
            }
        });

        if (Schema::hasColumn('web_forms', 'organization_field')) {
            DB::table('web_forms')->update([
                'organization_field' => 'required',
                'program_field' => 'required',
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('web_forms', function (Blueprint $table) {
            foreach (['is_active', 'send_submitter_email', 'email_template_id', 'campaign_scope'] as $column) {
                if (Schema::hasColumn('web_forms', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
