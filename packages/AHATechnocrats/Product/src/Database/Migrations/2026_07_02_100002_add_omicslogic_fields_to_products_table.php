<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $columns = [
                'category' => fn () => $table->string('category')->nullable()->after('name'),
                'is_active' => fn () => $table->boolean('is_active')->default(true)->after('category'),
                'canonical_product_id' => fn () => $table->unsignedInteger('canonical_product_id')->nullable()->after('is_active'),
                'mapping_status' => fn () => $table->string('mapping_status')->default('mapped')->after('canonical_product_id'),
                'mapping_confidence' => fn () => $table->decimal('mapping_confidence', 3, 2)->nullable()->after('mapping_status'),
            ];

            foreach ($columns as $name => $callback) {
                if (! Schema::hasColumn('products', $name)) {
                    $callback();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            foreach (['category', 'is_active', 'canonical_product_id', 'mapping_status', 'mapping_confidence'] as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
