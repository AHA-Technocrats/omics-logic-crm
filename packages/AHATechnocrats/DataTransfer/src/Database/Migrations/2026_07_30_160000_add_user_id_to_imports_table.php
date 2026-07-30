<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            if (! Schema::hasColumn('imports', 'user_id')) {
                $table->unsignedInteger('user_id')->nullable()->after('source_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            if (Schema::hasColumn('imports', 'user_id')) {
                $table->dropColumn('user_id');
            }
        });
    }
};
