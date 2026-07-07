<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('omics_organization_merge_review_pairs')) {
            return;
        }

        Schema::create('omics_organization_merge_review_pairs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('organization_a_id');
            $table->unsignedInteger('organization_b_id');
            $table->decimal('confidence', 3, 2)->default(0);
            $table->json('match_signals')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedInteger('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_a_id', 'organization_b_id'], 'omics_org_merge_pair_unique');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('omics_organization_merge_review_pairs');
    }
};
