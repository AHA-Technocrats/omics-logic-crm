<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            if (! Schema::hasColumn('persons', 'inquiry_details')) {
                $table->text('inquiry_details')->nullable()->after('education_level');
            }
        });

        if (Schema::hasColumn('web_forms', 'create_lead')) {
            Schema::table('web_forms', function (Blueprint $table) {
                $table->boolean('create_lead')->default(true)->change();
            });

            DB::table('web_forms')->update(['create_lead' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            if (Schema::hasColumn('persons', 'inquiry_details')) {
                $table->dropColumn('inquiry_details');
            }
        });

        if (Schema::hasColumn('web_forms', 'create_lead')) {
            Schema::table('web_forms', function (Blueprint $table) {
                $table->boolean('create_lead')->default(false)->change();
            });
        }
    }
};
