<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_forms', function (Blueprint $table) {
            if (! Schema::hasColumn('web_forms', 'field_order')) {
                $table->json('field_order')->nullable()->after('allow_org_create');
            }
        });
    }

    public function down(): void
    {
        Schema::table('web_forms', function (Blueprint $table) {
            if (Schema::hasColumn('web_forms', 'field_order')) {
                $table->dropColumn('field_order');
            }
        });
    }
};
