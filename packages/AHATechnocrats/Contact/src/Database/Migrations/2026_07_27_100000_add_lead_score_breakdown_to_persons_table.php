<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $columns = [
                'product_interest_points' => fn () => $table->unsignedTinyInteger('product_interest_points')->default(0)->after('lead_score'),
                'email_domain_points' => fn () => $table->unsignedTinyInteger('email_domain_points')->default(0)->after('product_interest_points'),
                'country_points' => fn () => $table->unsignedTinyInteger('country_points')->default(0)->after('email_domain_points'),
                'profile_points' => fn () => $table->unsignedTinyInteger('profile_points')->default(0)->after('country_points'),
                'score_band' => fn () => $table->string('score_band', 20)->nullable()->after('profile_points'),
            ];

            foreach ($columns as $name => $callback) {
                if (! Schema::hasColumn('persons', $name)) {
                    $callback();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            foreach (['product_interest_points', 'email_domain_points', 'country_points', 'profile_points', 'score_band'] as $column) {
                if (Schema::hasColumn('persons', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
