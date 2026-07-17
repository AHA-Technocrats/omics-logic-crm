<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('omics_analytics_users')) {
            Schema::create('omics_analytics_users', function (Blueprint $table) {
                $table->id();
                $table->string('uid')->nullable()->unique();
                $table->string('email')->index();
                $table->string('name')->nullable();
                $table->string('country')->nullable();
                $table->string('organization')->nullable();
                $table->string('education')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('omics_analytics_enrollments')) {
            Schema::create('omics_analytics_enrollments', function (Blueprint $table) {
                $table->id();
                $table->string('enrollment_id')->unique()->comment('Firebase ID or Hash');
                $table->string('user_uid')->index();
                $table->string('product_name')->index();
                $table->string('product_type')->nullable()->index();
                $table->integer('rating')->nullable();
                $table->text('feedback')->nullable();
                $table->decimal('amount', 12, 4)->nullable();
                $table->string('currency')->nullable();
                $table->timestamp('purchased_at')->nullable()->index();
                $table->timestamps();

                $table->foreign('user_uid')->references('uid')->on('omics_analytics_users')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('omics_analytics_enrollments');
        Schema::dropIfExists('omics_analytics_users');
    }
};
