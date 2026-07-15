@php
    $person = $person ?? null;
    $allowEdit = $allowEdit ?? false;
    $updateUrl = $updateUrl ?? null;

    $campaigns = app(\AHATechnocrats\Product\Repositories\ProductRepository::class)->all(['id', 'name']);
    $sources = app(\AHATechnocrats\Lead\Repositories\SourceRepository::class)->all(['id', 'name']);
    $owners = app(\AHATechnocrats\User\Repositories\UserRepository::class)->all(['id', 'name']);

    $campaignOptions = collect([['id' => '', 'name' => trans('omicslogic::app.fields.any')]])
        ->merge($campaigns->map(fn ($campaign) => ['id' => (string) $campaign->id, 'name' => $campaign->name]))
        ->values()
        ->all();

    $sourceOptions = collect([['id' => '', 'name' => trans('omicslogic::app.fields.any')]])
        ->merge($sources->map(fn ($source) => ['id' => (string) $source->id, 'name' => $source->name]))
        ->values()
        ->all();

    $ownerOptions = collect([['id' => '', 'name' => trans('omicslogic::app.fields.unassigned')]])
        ->merge($owners->map(fn ($owner) => ['id' => (string) $owner->id, 'name' => $owner->name]))
        ->values()
        ->all();

@endphp

@if ($person)
    <div class="flex flex-col gap-1">

        <div class="grid grid-cols-[1fr_2fr] items-center gap-1">
            <div class="label dark:text-white">@lang('omicslogic::app.fields.campaign')</div>
            <div class="font-medium dark:text-white">
                <x-admin::form.control-group.controls.inline.select
                    name="primary_product_id"
                    :value="(string) ($person->primary_product_id ?? '')"
                    :options="$campaignOptions"
                    position="left"
                    :label="trans('omicslogic::app.fields.campaign')"
                    :url="$updateUrl"
                    :allow-edit="$allowEdit"
                    :value-label="$person->primaryProduct?->name ?? trans('omicslogic::app.fields.any')"
                />
            </div>
        </div>

        <div class="grid grid-cols-[1fr_2fr] items-center gap-1">
            <div class="label dark:text-white">@lang('omicslogic::app.fields.source')</div>
            <div class="font-medium dark:text-white">
                <x-admin::form.control-group.controls.inline.select
                    name="primary_source_id"
                    :value="(string) ($person->primary_source_id ?? '')"
                    :options="$sourceOptions"
                    position="left"
                    :label="trans('omicslogic::app.fields.source')"
                    :url="$updateUrl"
                    :allow-edit="$allowEdit"
                    :value-label="$person->primarySource?->name ?? trans('omicslogic::app.fields.any')"
                />
            </div>
        </div>

        <div class="grid grid-cols-[1fr_2fr] items-center gap-1">
            <div class="label dark:text-white">@lang('omicslogic::app.fields.owner')</div>
            <div class="font-medium dark:text-white">
                <x-admin::form.control-group.controls.inline.select
                    name="user_id"
                    :value="(string) ($person->user_id ?? '')"
                    :options="$ownerOptions"
                    position="left"
                    :label="trans('omicslogic::app.fields.owner')"
                    :url="$updateUrl"
                    :allow-edit="$allowEdit"
                    :value-label="$person->user?->name ?? trans('omicslogic::app.fields.unassigned')"
                />
            </div>
        </div>
    </div>
@endif
