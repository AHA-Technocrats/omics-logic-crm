@php
    use AHATechnocrats\WebForm\Helpers\WebFormPrograms;

    $campaigns = WebFormPrograms::forForm($webForm);
    $isRequired = ($webForm->program_field ?? 'required') === 'required';
    $programOptions = collect($campaigns);
@endphp

<div class="webform-field">
    <x-web_form::form.control-group>
        <x-web_form::form.control-group.label
            for="persons[program_interest]"
            class="{{ $isRequired ? 'required' : '' }}"
            style="color: {{ $webForm->attribute_label_color }} !important;"
        >
            Interested in Campaign
        </x-web_form::form.control-group.label>

        <x-web_form::form.control-group.control
            type="select"
            name="persons[program_interest]"
            id="persons[program_interest]"
            rules="{{ $isRequired ? 'required' : '' }}"
            label="Interested in Campaign"
            @change="programInterest = $event.target.value"
        >
            <option value="">Select campaign</option>

            @foreach ($programOptions as $program)
                <option value="{{ $program['name'] }}">{{ $program['name'] }}</option>
            @endforeach
        </x-web_form::form.control-group.control>

        <x-web_form::form.control-group.error control-name="persons[program_interest]" />
    </x-web_form::form.control-group>
</div>
