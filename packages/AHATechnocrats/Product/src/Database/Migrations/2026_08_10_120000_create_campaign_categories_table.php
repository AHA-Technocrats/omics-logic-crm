<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('campaign_categories')) {
            Schema::create('campaign_categories', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->unique();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedInteger('category_id')->nullable()->after('category');
                $table->foreign('category_id')->references('id')->on('campaign_categories')->restrictOnDelete();
            });
        }

        $seedNames = [
            'Bioinformatics',
            'Next-Generation Sequencing',
            'Genomics',
            'Bacterial Genomics',
            'Pan Genomics',
            'Clinical Genomics',
            'Cancer Genomics',
            'Bulk Transcriptomics',
            'Single Cell Transcriptomics',
            'Spatial Transcriptomics',
            'Metagenomics',
            'Metabolomics',
            'Proteomics',
            'Epigenomics',
            'Multi-Omics',
            'Immunoinformatics',
            'Cheminformatics',
            'AI Drug Discovery',
            'Health Informatics',
            'R Coding',
            'Python',
            'Linux',
            'GitHub',
            'Deep Learning',
            'Machine Learning',
            'Artificial Intelligence',
            'Data Science',
            'Research',
            'Plant Biotechnology',
            'Computational Pathology',
            'Liquid Biopsy',
            'Training Programs',
            'Tracks',
        ];

        $now = now();

        foreach ($seedNames as $name) {
            $exists = DB::table('campaign_categories')
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                ->exists();

            if (! $exists) {
                DB::table('campaign_categories')->insert([
                    'name' => $name,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        if (! Schema::hasTable('products') || ! Schema::hasColumn('products', 'category')) {
            return;
        }

        $legacyNames = DB::table('products')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category');

        foreach ($legacyNames as $legacyName) {
            $legacyName = trim((string) $legacyName);

            if ($legacyName === '') {
                continue;
            }

            $exists = DB::table('campaign_categories')
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($legacyName)])
                ->exists();

            if (! $exists) {
                DB::table('campaign_categories')->insert([
                    'name' => $legacyName,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $categories = DB::table('campaign_categories')->get(['id', 'name']);

        foreach ($categories as $category) {
            DB::table('products')
                ->whereNotNull('category')
                ->whereRaw('LOWER(category) = ?', [mb_strtolower($category->name)])
                ->update([
                    'category_id' => $category->id,
                    'category' => $category->name,
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            });
        }

        Schema::dropIfExists('campaign_categories');
    }
};
