<div class="webform-field">
    <x-web_form::form.control-group>
        <x-web_form::form.control-group.label
            for="persons[inquiry_details]"
            style="color: {{ $webForm->attribute_label_color }} !important;"
        >
            Any other details / queries
        </x-web_form::form.control-group.label>

        <x-web_form::form.control-group.control
            type="textarea"
            name="persons[inquiry_details]"
            id="persons[inquiry_details]"
            label="Any other details / queries"
            placeholder="Optional"
        />

        <x-web_form::form.control-group.error control-name="persons[inquiry_details]" />
    </x-web_form::form.control-group>
</div>
