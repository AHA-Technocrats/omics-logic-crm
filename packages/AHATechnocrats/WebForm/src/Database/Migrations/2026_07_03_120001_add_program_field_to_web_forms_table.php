<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_forms', function (Blueprint $table) {
            if (! Schema::hasColumn('web_forms', 'program_field')) {
                $table->string('program_field')->default('optional')->after('allow_org_create');
            }

            if (! Schema::hasColumn('web_forms', 'program_options')) {
                $table->json('program_options')->nullable()->after('program_field');
            }
        });
    }

    public function down(): void
    {
        Schema::table('web_forms', function (Blueprint $table) {
            foreach (['program_field', 'program_options'] as $column) {
                if (Schema::hasColumn('web_forms', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
