<?php

use AHATechnocrats\Attribute\Models\Attribute;
use AHATechnocrats\Contact\Models\Organization;
use AHATechnocrats\Contact\Models\Person;
use AHATechnocrats\WebForm\Models\WebForm;

it('creates a web form with dynamic custom attributes and handles submissions', function () {
    $admin = getDefaultAdmin();

    // Ensure default attributes exist in the test DB
    $nameAttr = Attribute::where('code', 'name')->where('entity_type', 'persons')->first()
        ?: Attribute::create([
            'code' => 'name',
            'name' => 'Name',
            'type' => 'text',
            'entity_type' => 'persons',
            'is_required' => true,
        ]);

    $emailAttr = Attribute::where('code', 'emails')->where('entity_type', 'persons')->first()
        ?: Attribute::create([
            'code' => 'emails',
            'name' => 'Emails',
            'type' => 'email',
            'entity_type' => 'persons',
            'is_required' => true,
        ]);

    // 1. Create a Web Form
    $response = test()->actingAs($admin)
        ->post(route('admin.settings.web_forms.store'), [
            'title' => 'Student Registration Form',
            'description' => 'Register for the bioinformatics program.',
            'submit_button_label' => 'Submit Details',
            'submit_success_action' => 'message',
            'submit_success_content' => 'Thank you for registering!',
            'create_lead' => '0',
            'organization_field' => 'optional',
            'allow_org_create' => '1',
            'attributes' => [
                'attribute_0' => [
                    'attribute_id' => (string) $nameAttr->id,
                    'name' => 'Full Name',
                    'is_required' => '1',
                ],
                'attribute_1' => [
                    'attribute_id' => (string) $emailAttr->id,
                    'name' => 'Email Address',
                    'is_required' => '1',
                ],
                // Add a dynamic custom attribute on the fly!
                'attribute_2' => [
                    'is_new' => '1',
                    'code' => 'custom_shirt_size',
                    'name' => 'T-Shirt Size',
                    'type' => 'text',
                    'entity_type' => 'persons',
                    'is_required' => '0',
                ],
            ],
        ]);

    $response->assertRedirect(route('admin.web_forms.index'));

    // Verify the web form was created in the DB
    $webForm = WebForm::where('title', 'Student Registration Form')->first();
    expect($webForm)->not->toBeNull();
    expect($webForm->description)->toBe('Register for the bioinformatics program.');
    expect($webForm->organization_field)->toBe('required');
    expect($webForm->allow_org_create)->toBeTrue();

    // Verify the custom attribute was created in the DB
    $customAttr = Attribute::where('code', 'custom_shirt_size')->first();
    expect($customAttr)->not->toBeNull();
    expect($customAttr->name)->toBe('T-Shirt Size');
    expect($customAttr->type)->toBe('text');
    expect($customAttr->entity_type)->toBe('persons');

    // 2. Submit the created Web Form
    $submissionResponse = test()
        ->post(route('admin.settings.web_forms.form_store', $webForm->id), [
            '_form_token' => session('_token') ?? 'test-token',
            'persons' => [
                'name' => 'Alice Cooper',
                'emails' => [
                    ['value' => 'alice@cooper.com', 'label' => 'work'],
                ],
                'custom_shirt_size' => 'Medium',
                'organization_name' => 'Bioinformatics Institute of Excellence',
            ],
        ]);

    $submissionResponse->assertOk();
    $submissionResponse->assertJsonFragment([
        'message' => 'Thank you for registering!',
    ]);

    // Verify the organization was created/resolved
    $organization = Organization::where('name', 'Bioinformatics Institute of Excellence')->first();
    expect($organization)->not->toBeNull();

    // Verify the Person was created and linked to the organization
    $person = Person::where('name', 'Alice Cooper')->first();
    expect($person)->not->toBeNull();
    expect($person->organization_id)->toBe($organization->id);

    // Verify the custom attribute value was saved
    $attrValue = DB::table('attribute_values')
        ->where('entity_type', 'persons')
        ->where('entity_id', $person->id)
        ->where('attribute_id', $customAttr->id)
        ->first();

    expect($attrValue)->not->toBeNull();
    expect($attrValue->text_value)->toBe('Medium');
});
