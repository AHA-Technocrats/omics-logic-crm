@php
    $countries = config('omicslogic.countries', []);
@endphp

<div class="webform-field">
    <x-web_form::form.control-group>
        <x-web_form::form.control-group.label
            for="persons[country_code]"
            class="required"
            style="color: {{ $webForm->attribute_label_color }} !important;"
        >
            Country
        </x-web_form::form.control-group.label>

        <x-web_form::form.control-group.control
            type="select"
            name="persons[country_code]"
            id="persons[country_code]"
            rules="required"
            label="Country"
        >
            <option value="">Select country</option>
            @foreach ($countries as $country)
                <option value="{{ $country }}">{{ $country }}</option>
            @endforeach
        </x-web_form::form.control-group.control>

        <x-web_form::form.control-group.error control-name="persons[country_code]" />
    </x-web_form::form.control-group>
</div>
