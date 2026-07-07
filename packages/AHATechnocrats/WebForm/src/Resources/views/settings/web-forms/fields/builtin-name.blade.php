<div class="webform-field">
    <x-web_form::form.control-group>
        <x-web_form::form.control-group.label
            for="persons[name]"
            class="required"
            style="color: {{ $webForm->attribute_label_color }} !important;"
        >
            Full Name
        </x-web_form::form.control-group.label>

        <x-web_form::form.control-group.control
            type="text"
            name="persons[name]"
            id="persons[name]"
            rules="required"
            label="Full Name"
            placeholder="Full Name"
        />

        <x-web_form::form.control-group.error control-name="persons[name]" />
    </x-web_form::form.control-group>
</div>
