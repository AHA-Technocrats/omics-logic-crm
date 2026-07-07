<?php

use AHATechnocrats\OmicsLogic\Services\CountryLabelResolver;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('country_code', 100)->nullable()->change();
        });

        Schema::table('persons', function (Blueprint $table) {
            $table->string('country_code', 100)->nullable()->change();
        });

        $resolver = app(CountryLabelResolver::class);

        foreach (DB::table('organizations')->whereNotNull('country_code')->get(['id', 'country_code']) as $row) {
            $resolved = $resolver->resolve($row->country_code);

            if ($resolved && $resolved !== $row->country_code) {
                DB::table('organizations')->where('id', $row->id)->update(['country_code' => $resolved]);
            }
        }

        foreach (DB::table('persons')->whereNotNull('country_code')->get(['id', 'country_code']) as $row) {
            $resolved = $resolver->resolve($row->country_code);

            if ($resolved && $resolved !== $row->country_code) {
                DB::table('persons')->where('id', $row->id)->update(['country_code' => $resolved]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('country_code', 3)->nullable()->change();
        });

        Schema::table('persons', function (Blueprint $table) {
            $table->string('country_code', 3)->nullable()->change();
        });
    }
};
