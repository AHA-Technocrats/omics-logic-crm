<div class="webform-field">
    <x-web_form::form.control-group>
        <x-web_form::form.control-group.label
            for="persons[emails][0][value]"
            class="required"
            style="color: {{ $webForm->attribute_label_color }} !important;"
        >
            Email Address
        </x-web_form::form.control-group.label>

        <x-web_form::form.control-group.control
            type="email"
            name="persons[emails][0][value]"
            id="persons[emails][0][value]"
            rules="required|email"
            label="Email Address"
            placeholder="Email Address"
        />

        <x-web_form::form.control-group.control
            type="hidden"
            name="persons[emails][0][label]"
            value="work"
        />

        <x-web_form::form.control-group.error control-name="persons[emails][0][value]" />
    </x-web_form::form.control-group>
</div>
