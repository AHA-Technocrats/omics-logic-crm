<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(DatabaseTransactions::class);

it('cleans database tables and resets auto-increment indexes', function () {
    // 1. Arrange: Insert dummy data into target tables with high auto-increment IDs
    Schema::disableForeignKeyConstraints();

    DB::table('persons')->insert([
        'id' => 50,
        'name' => 'John Doe Contact',
        'emails' => json_encode(['john@example.com']),
    ]);

    DB::table('leads')->insert([
        'id' => 100,
        'title' => 'Test Lead operational data',
    ]);

    DB::table('organizations')->insert([
        'id' => 30,
        'name' => 'Test Org Inc',
    ]);

    DB::table('web_form_submissions')->insert([
        'id' => 40,
        'web_form_id' => 1,
    ]);

    DB::table('omics_audit_logs')->insert([
        'id' => 200,
        'actor_type' => 'user',
        'action' => 'test_action',
    ]);

    Schema::enableForeignKeyConstraints();

    // Verify dummy data is present before command runs
    expect(DB::table('leads')->count())->toBeGreaterThan(0);
    expect(DB::table('persons')->count())->toBeGreaterThan(0);
    expect(DB::table('organizations')->count())->toBeGreaterThan(0);
    expect(DB::table('web_form_submissions')->count())->toBeGreaterThan(0);
    expect(DB::table('omics_audit_logs')->count())->toBeGreaterThan(0);

    // 2. Act: Run the database cleanup command with --force and --submissions-only
    $this->artisan('crm:cleanup', [
        '--force' => true,
        '--submissions-only' => true,
    ])->assertSuccessful();

    // 3. Assert: Verify targeted tables are truncated
    expect(DB::table('leads')->count())->toBe(0);
    expect(DB::table('persons')->count())->toBe(0);
    expect(DB::table('organizations')->count())->toBe(0);
    expect(DB::table('web_form_submissions')->count())->toBe(0);
    expect(DB::table('omics_audit_logs')->count())->toBe(0);

    // 4. Assert auto-increment IDs reset to 1
    Schema::disableForeignKeyConstraints();

    $newPersonId = DB::table('persons')->insertGetId([
        'name' => 'Brand New Person',
        'emails' => json_encode(['brandnew@example.com']),
    ]);

    $newLeadId = DB::table('leads')->insertGetId([
        'title' => 'Brand New Lead',
    ]);

    $newOrgId = DB::table('organizations')->insertGetId([
        'name' => 'Brand New Org',
    ]);

    Schema::enableForeignKeyConstraints();

    expect($newPersonId)->toBe(1);
    expect($newLeadId)->toBe(1);
    expect($newOrgId)->toBe(1);
});
