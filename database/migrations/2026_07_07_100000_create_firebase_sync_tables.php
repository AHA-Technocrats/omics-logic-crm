<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('firebase_sync_states')) {
            Schema::create('firebase_sync_states', function (Blueprint $table) {
                $table->id();
                $table->string('collection')->unique();
                $table->timestamp('last_synced_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('firebase_synced_documents')) {
            Schema::create('firebase_synced_documents', function (Blueprint $table) {
                $table->id();
                $table->string('firestore_doc_id')->unique();
                $table->timestamp('synced_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('firebase_synced_documents');
        Schema::dropIfExists('firebase_sync_states');
    }
};
