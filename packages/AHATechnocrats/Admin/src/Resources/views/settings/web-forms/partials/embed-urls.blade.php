@php
    $webForm = $webForm ?? null;
    $fullUrl = $fullUrl ?? ($webForm ? route('admin.settings.web_forms.preview', $webForm->form_id) : '');
    $shortUrl = $shortUrl ?? '';
    $bindShort = $bindShort ?? false;
    $bindFull = $bindFull ?? false;
@endphp

@if ($shortUrl !== '' || $bindShort)
    <x-admin::form.control-group>
        <x-admin::form.control-group.label class="required">
            @lang('admin::app.settings.webforms.edit.short-url')
        </x-admin::form.control-group.label>

        @if ($bindShort)
            <input
                type="text"
                id="shortUrl"
                name="shortUrl"
                class="w-full rounded border border-gray-300 px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                :value="shortUrl"
                readonly
            />
        @else
            <input
                type="text"
                id="shortUrl"
                name="shortUrl"
                class="w-full rounded border border-gray-300 px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                value="{{ $shortUrl }}"
                readonly
            />
        @endif

        <span
            id="shortUrlBtn"
            class="cursor-pointer text-xs font-normal text-brandColor hover:text-sky-600 hover:underline"
            @click="copyToClipboard('#shortUrl','#shortUrlBtn')"
        >
            @lang('admin::app.settings.webforms.edit.copy')
        </span>

        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            @lang('admin::app.settings.webforms.edit.short-url-help')
        </p>
    </x-admin::form.control-group>
@endif

<x-admin::form.control-group>
    <x-admin::form.control-group.label class="required">
        @lang('admin::app.settings.webforms.edit.public-url')
    </x-admin::form.control-group.label>

    @if ($bindFull)
        <input
            type="text"
            id="publicUrl"
            name="publicUrl"
            class="w-full rounded border border-gray-300 px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
            :value="previewUrl"
            readonly
        />
    @else
        <input
            type="text"
            id="publicUrl"
            name="publicUrl"
            class="w-full rounded border border-gray-300 px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
            value="{{ $fullUrl }}"
            readonly
        />
    @endif

    <span
        id="publicUrlBtn"
        class="cursor-pointer text-xs font-normal text-brandColor hover:text-sky-600 hover:underline"
        @click="copyToClipboard('#publicUrl','#publicUrlBtn')"
    >
        @lang('admin::app.settings.webforms.edit.copy')
    </span>
</x-admin::form.control-group>
