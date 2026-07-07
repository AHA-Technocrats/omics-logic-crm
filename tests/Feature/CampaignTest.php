<?php

use AHATechnocrats\Product\Models\Product;
use Illuminate\Support\Facades\DB;

it('performs campaign crud operations and syncs aliases', function () {
    $admin = getDefaultAdmin();

    // 1. Create a Campaign
    $response = test()->actingAs($admin)
        ->post(route('admin.campaigns.store'), [
            'entity_type' => 'products',
            'name' => 'Test Campaign',
            'sku' => 'test-campaign-sku',
            'price' => '100.00',
            'quantity' => '10',
            'category' => 'Transcriptomics',
            'mapping_status' => 'mapped',
            'is_active' => '1',
            'aliases' => 'Test Alias 1, Test Alias 2',
        ]);

    $response->assertRedirect(route('admin.campaigns.index'));

    // Check database
    $product = Product::where('sku', 'test-campaign-sku')->first();
    expect($product)->not->toBeNull();
    expect($product->name)->toBe('Test Campaign');
    expect($product->category)->toBe('Transcriptomics');
    expect($product->mapping_status)->toBe('mapped');
    expect($product->is_active)->toBeTrue();

    // Check aliases
    $aliases = DB::table('omics_product_aliases')
        ->where('product_id', $product->id)
        ->pluck('alias_name')
        ->toArray();

    expect($aliases)->toContain('Test Alias 1', 'Test Alias 2');

    // 2. Edit / Update the Campaign
    $response = test()->actingAs($admin)
        ->put(route('admin.campaigns.update', $product->id), [
            'entity_type' => 'products',
            'name' => 'Updated Campaign',
            'sku' => 'test-campaign-sku',
            'price' => '150.00',
            'quantity' => '5',
            'category' => 'Metagenomics',
            'mapping_status' => 'review',
            'is_active' => '0',
            'aliases' => 'Test Alias 2, Test Alias 3',
        ]);

    $response->assertRedirect(route('admin.campaigns.index'));

    // Verify update
    $product->refresh();
    expect($product->name)->toBe('Updated Campaign');
    expect($product->category)->toBe('Metagenomics');
    expect($product->mapping_status)->toBe('review');
    expect($product->is_active)->toBeFalse();

    // Verify updated aliases
    $updatedAliases = DB::table('omics_product_aliases')
        ->where('product_id', $product->id)
        ->pluck('alias_name')
        ->toArray();

    expect($updatedAliases)->not->toContain('Test Alias 1');
    expect($updatedAliases)->toContain('Test Alias 2', 'Test Alias 3');

    // 3. Delete the Campaign
    $response = test()->actingAs($admin)
        ->delete(route('admin.campaigns.delete', $product->id));

    $response->assertOk();

    // Verify deletion of campaign and aliases
    expect(Product::where('sku', 'test-campaign-sku')->first())->toBeNull();
    expect(DB::table('omics_product_aliases')->where('product_id', $product->id)->count())->toBe(0);
});
