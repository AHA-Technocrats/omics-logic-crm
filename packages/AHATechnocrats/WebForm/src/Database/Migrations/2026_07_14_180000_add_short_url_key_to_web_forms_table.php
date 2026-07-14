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
            if (! Schema::hasColumn('web_forms', 'short_url_key')) {
                $table->string('short_url_key', 32)->nullable()->after('form_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('web_forms', function (Blueprint $table) {
            if (Schema::hasColumn('web_forms', 'short_url_key')) {
                $table->dropColumn('short_url_key');
            }
        });
    }
};
