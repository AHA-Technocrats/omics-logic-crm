<?php

use AHATechnocrats\Contact\Models\Organization;
use AHATechnocrats\OmicsLogic\Services\OrganizationResolver;
use Illuminate\Support\Facades\DB;

it('resolves organizations by exact name, similarity, and aliases', function () {
    $resolver = app(OrganizationResolver::class);

    // 1. Create a base organization
    $org = Organization::create([
        'name' => 'Stanford University',
        'normalized_name' => 'stanford university',
        'type' => 'University',
        'entity_type' => 'organizations',
    ]);

    // 2. Resolve by exact name (case-insensitive & whitespace trimmed)
    $resolved = $resolver->resolve('  stanford university  ');
    expect($resolved)->not->toBeNull();
    expect($resolved->id)->toBe($org->id);

    // 3. Resolve by fuzzy similarity
    $resolvedFuzzy = $resolver->resolve('Stanford Univ');
    expect($resolvedFuzzy)->not->toBeNull();
    expect($resolvedFuzzy->id)->toBe($org->id);

    // 4. Resolve by alias from aliases table
    DB::table('omics_organization_aliases')->insert([
        'organization_id' => $org->id,
        'alias_name' => 'Stanford',
        'normalized_key' => 'stanford',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $resolvedAlias = $resolver->resolve('Stanford');
    expect($resolvedAlias)->not->toBeNull();
    expect($resolvedAlias->id)->toBe($org->id);

    // 5. Resolve new organization (creates it)
    $newResolved = $resolver->resolve('Harvard University');
    expect($newResolved)->not->toBeNull();
    expect($newResolved->name)->toBe('Harvard University');
    expect($newResolved->normalized_name)->toBe('harvard university');

    // Cleanup
    $org->delete();
    $newResolved->delete();
    DB::table('omics_organization_aliases')->where('organization_id', $org->id)->delete();
});
