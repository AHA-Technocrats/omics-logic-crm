<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('web_forms', function (Blueprint $table) {
            if (! Schema::hasColumn('web_forms', 'show_campaign_other')) {
                $table->boolean('show_campaign_other')->default(true)->after('program_options');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('web_forms', function (Blueprint $table) {
            if (Schema::hasColumn('web_forms', 'show_campaign_other')) {
                $table->dropColumn('show_campaign_other');
            }
        });
    }
};
