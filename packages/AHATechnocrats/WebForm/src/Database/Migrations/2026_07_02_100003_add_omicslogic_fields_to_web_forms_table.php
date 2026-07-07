<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_forms', function (Blueprint $table) {
            $columns = [
                'product_id' => fn () => $table->unsignedInteger('product_id')->nullable()->after('create_lead'),
                'turnstile_enabled' => fn () => $table->boolean('turnstile_enabled')->default(true)->after('product_id'),
                'honeypot_enabled' => fn () => $table->boolean('honeypot_enabled')->default(true)->after('turnstile_enabled'),
                'min_submit_seconds' => fn () => $table->unsignedSmallInteger('min_submit_seconds')->default(3)->after('honeypot_enabled'),
                'rate_limit_per_ip' => fn () => $table->unsignedSmallInteger('rate_limit_per_ip')->default(10)->after('min_submit_seconds'),
                'rate_limit_per_email' => fn () => $table->unsignedSmallInteger('rate_limit_per_email')->default(5)->after('rate_limit_per_ip'),
                'block_disposable' => fn () => $table->boolean('block_disposable')->default(true)->after('rate_limit_per_email'),
                'organization_field' => fn () => $table->string('organization_field')->default('required')->after('block_disposable'),
                'allow_org_create' => fn () => $table->boolean('allow_org_create')->default(true)->after('organization_field'),
            ];

            foreach ($columns as $name => $callback) {
                if (! Schema::hasColumn('web_forms', $name)) {
                    $callback();
                }
            }
        });

        if (! Schema::hasTable('web_form_submissions')) {
            Schema::create('web_form_submissions', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('web_form_id');
                $table->unsignedInteger('person_id')->nullable();
                $table->json('payload')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->unsignedTinyInteger('spam_score')->default(0);
                $table->string('status')->default('accepted');
                $table->timestamps();

                $table->index(['web_form_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('web_form_submissions');

        Schema::table('web_forms', function (Blueprint $table) {
            foreach (['product_id', 'turnstile_enabled', 'honeypot_enabled', 'min_submit_seconds', 'rate_limit_per_ip', 'rate_limit_per_email', 'block_disposable', 'organization_field', 'allow_org_create'] as $column) {
                if (Schema::hasColumn('web_forms', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
