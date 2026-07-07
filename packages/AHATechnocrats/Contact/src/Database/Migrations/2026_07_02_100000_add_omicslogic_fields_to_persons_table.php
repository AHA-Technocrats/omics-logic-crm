<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $columns = [
                'lifecycle_stage' => fn () => $table->string('lifecycle_stage')->nullable()->after('organization_id'),
                'is_student' => fn () => $table->boolean('is_student')->default(false)->after('lifecycle_stage'),
                'converted_at' => fn () => $table->timestamp('converted_at')->nullable()->after('is_student'),
                'lead_score' => fn () => $table->unsignedTinyInteger('lead_score')->default(0)->after('converted_at'),
                'country_code' => fn () => $table->string('country_code', 100)->nullable()->after('lead_score'),
                'education_level' => fn () => $table->string('education_level', 50)->nullable()->after('country_code'),
                'inquiry_details' => fn () => $table->text('inquiry_details')->nullable()->after('education_level'),
                'primary_source_id' => fn () => $table->unsignedInteger('primary_source_id')->nullable()->after('inquiry_details'),
                'portal_user_id' => fn () => $table->string('portal_user_id', 100)->nullable()->unique()->after('primary_source_id'),
                'primary_product_id' => fn () => $table->unsignedInteger('primary_product_id')->nullable()->after('portal_user_id'),
                'sales_stage' => fn () => $table->string('sales_stage', 50)->nullable()->after('primary_product_id'),
                'next_action' => fn () => $table->string('next_action')->nullable()->after('sales_stage'),
                'next_action_due' => fn () => $table->date('next_action_due')->nullable()->after('next_action'),
                'last_contacted_at' => fn () => $table->timestamp('last_contacted_at')->nullable()->after('next_action_due'),
                'last_activity_at' => fn () => $table->timestamp('last_activity_at')->nullable()->after('last_contacted_at'),
                'engagement_lessons' => fn () => $table->unsignedInteger('engagement_lessons')->default(0)->after('last_activity_at'),
                'is_opted_out' => fn () => $table->boolean('is_opted_out')->default(false)->after('engagement_lessons'),
                'normalized_email' => fn () => $table->string('normalized_email')->nullable()->index()->after('is_opted_out'),
                'normalized_phone' => fn () => $table->string('normalized_phone', 50)->nullable()->index()->after('normalized_email'),
                'spam_score' => fn () => $table->unsignedTinyInteger('spam_score')->default(0)->after('normalized_phone'),
                'spam_status' => fn () => $table->string('spam_status')->default('clean')->after('spam_score'),
                'merged_into_id' => fn () => $table->unsignedInteger('merged_into_id')->nullable()->after('spam_status'),
            ];

            foreach ($columns as $name => $callback) {
                if (! Schema::hasColumn('persons', $name)) {
                    $callback();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $drop = [
                'lifecycle_stage', 'is_student', 'converted_at', 'lead_score', 'country_code',
                'education_level', 'primary_source_id', 'portal_user_id', 'primary_product_id',
                'sales_stage', 'next_action', 'next_action_due', 'last_contacted_at', 'last_activity_at',
                'engagement_lessons', 'is_opted_out', 'normalized_email', 'normalized_phone',
                'spam_score', 'spam_status', 'merged_into_id',
            ];

            foreach ($drop as $column) {
                if (Schema::hasColumn('persons', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
