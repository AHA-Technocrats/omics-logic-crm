@php
    $educationLevels = ['Undergraduate', 'Masters', 'PhD', 'Faculty', 'Industry'];
@endphp

<div class="webform-field">
    <x-web_form::form.control-group>
        <x-web_form::form.control-group.label
            for="persons[education_level]"
            style="color: {{ $webForm->attribute_label_color }} !important;"
        >
            Level of Education
        </x-web_form::form.control-group.label>

        <x-web_form::form.control-group.control
            type="select"
            name="persons[education_level]"
            id="persons[education_level]"
            label="Level of Education"
        >
            <option value="">Select education level</option>
            @foreach ($educationLevels as $level)
                <option value="{{ $level }}">{{ $level }}</option>
            @endforeach
            <option value="Doctorate (Ph.D.)">Doctorate (Ph.D.)</option>
        </x-web_form::form.control-group.control>

        <x-web_form::form.control-group.error control-name="persons[education_level]" />
    </x-web_form::form.control-group>
</div>
