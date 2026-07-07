<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('omics_connectors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type');
            $table->string('name');
            $table->json('config')->nullable();
            $table->string('status')->default('disabled');
            $table->timestamp('last_sync_at')->nullable();
            $table->string('last_sync_status')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });

        Schema::create('omics_connector_sync_runs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('connector_id');
            $table->unsignedInteger('rows_total')->default(0);
            $table->unsignedInteger('rows_new')->default(0);
            $table->unsignedInteger('rows_merged')->default(0);
            $table->unsignedInteger('rows_review')->default(0);
            $table->unsignedInteger('rows_failed')->default(0);
            $table->string('status')->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('omics_segments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('filter_query');
            $table->unsignedInteger('owner_id')->nullable();
            $table->string('refresh_schedule')->default('manual');
            $table->timestamp('last_refreshed_at')->nullable();
            $table->unsignedInteger('contact_count_cached')->default(0);
            $table->boolean('is_shared')->default(false);
            $table->timestamps();
        });

        Schema::create('omics_merge_review_pairs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('person_a_id');
            $table->unsignedInteger('person_b_id');
            $table->decimal('confidence', 3, 2)->default(0);
            $table->json('match_signals')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedInteger('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->unique(['person_a_id', 'person_b_id']);
        });

        Schema::create('omics_audit_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('actor_type');
            $table->unsignedInteger('actor_id')->nullable();
            $table->string('action');
            $table->string('entity_type')->nullable();
            $table->unsignedInteger('entity_id')->nullable();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->boolean('is_reversible')->default(false);
            $table->timestamp('reversed_at')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->index('created_at');
        });

        Schema::create('omics_product_aliases', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_id');
            $table->string('alias_name');
            $table->string('source')->default('manual');
            $table->decimal('confidence', 3, 2)->nullable();
            $table->unsignedInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'alias_name']);
        });

        Schema::create('omics_organization_aliases', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('organization_id');
            $table->string('alias_name');
            $table->string('normalized_key')->index();
            $table->timestamps();
        });

        Schema::create('omics_disposable_email_domains', function (Blueprint $table) {
            $table->string('domain')->primary();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('omics_disposable_email_domains');
        Schema::dropIfExists('omics_organization_aliases');
        Schema::dropIfExists('omics_product_aliases');
        Schema::dropIfExists('omics_audit_logs');
        Schema::dropIfExists('omics_merge_review_pairs');
        Schema::dropIfExists('omics_segments');
        Schema::dropIfExists('omics_connector_sync_runs');
        Schema::dropIfExists('omics_connectors');
    }
};
