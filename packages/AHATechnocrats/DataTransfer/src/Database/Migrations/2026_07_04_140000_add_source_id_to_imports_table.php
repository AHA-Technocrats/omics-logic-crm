<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            if (! Schema::hasColumn('imports', 'source_id')) {
                $table->unsignedInteger('source_id')->nullable()->after('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            if (Schema::hasColumn('imports', 'source_id')) {
                $table->dropColumn('source_id');
            }
        });
    }
};
