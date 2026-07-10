@php
    $owner = $lead->user;
    $ownerImages = $owner?->image
        ? [['id' => 'image', 'url' => $owner->image_url]]
        : [];
@endphp

@if ($owner)
    <div class="mt-4 border-t border-gray-200 pt-4 dark:border-gray-800">
        <x-admin::form.control-group>
            <x-admin::form.control-group.label>
                @lang('omicslogic::app.fields.owner-profile')
            </x-admin::form.control-group.label>

            <x-admin::media.images
                name="lead_owner_image"
                :uploaded-images="$ownerImages"
            />

            <p class="mt-2 text-xs text-gray-600 dark:text-gray-300">
                @lang('omicslogic::app.fields.lead-owner-profile-help')
            </p>
        </x-admin::form.control-group>
    </div>
@endif
