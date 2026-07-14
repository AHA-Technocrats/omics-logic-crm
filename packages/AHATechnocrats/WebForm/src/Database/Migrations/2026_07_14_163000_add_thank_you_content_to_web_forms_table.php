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
            if (! Schema::hasColumn('web_forms', 'thank_you_content')) {
                $table->longText('thank_you_content')->nullable()->after('submit_success_content');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('web_forms', function (Blueprint $table) {
            if (Schema::hasColumn('web_forms', 'thank_you_content')) {
                $table->dropColumn('thank_you_content');
            }
        });
    }
};
