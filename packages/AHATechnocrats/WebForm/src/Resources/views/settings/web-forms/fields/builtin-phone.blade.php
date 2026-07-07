<div class="webform-field">
    <x-web_form::form.control-group>
        <x-web_form::form.control-group.label
            for="persons[contact_numbers][0][value]"
            style="color: {{ $webForm->attribute_label_color }} !important;"
        >
            Phone number
        </x-web_form::form.control-group.label>

        <x-web_form::form.control-group.control
            type="text"
            name="persons[contact_numbers][0][value]"
            id="persons[contact_numbers][0][value]"
            rules="phone"
            label="Phone number"
            placeholder="Phone number"
        />

        <x-web_form::form.control-group.control
            type="hidden"
            name="persons[contact_numbers][0][label]"
            value="work"
        />

        <x-web_form::form.control-group.error control-name="persons[contact_numbers][0][value]" />
    </x-web_form::form.control-group>
</div>
