@php
    $parentAttribute = $attribute->attribute;
    $fieldName = $parentAttribute ? $parentAttribute->entity_type.'['.$parentAttribute->code.']' : null;
    $validations = $attribute->is_required ? 'required' : '';
@endphp

@if ($parentAttribute)
<div class="webform-field">
    <x-web_form::form.control-group>
        <x-web_form::form.control-group.label
            :for="$fieldName"
            class="{{ $validations }}"
            style="color: {{ $webForm->attribute_label_color }} !important;"
        >
            {{ $attribute->name ?? $parentAttribute->name }}
        </x-web_form::form.control-group.label>

        @switch($parentAttribute->type)
            @case('text')
                <x-web_form::form.control-group.control
                    type="text"
                    :name="$fieldName"
                    :id="$fieldName"
                    :rules="$validations"
                    :label="$attribute->name ?? $parentAttribute->name"
                    :placeholder="$attribute->placeholder"
                />

                <x-web_form::form.control-group.error :control-name="$fieldName" />

                @break

            @case('textarea')
                <x-web_form::form.control-group.control
                    type="textarea"
                    :name="$fieldName"
                    :id="$fieldName"
                    :rules="$validations"
                    :label="$attribute->name ?? $parentAttribute->name"
                    :placeholder="$attribute->placeholder"
                />
                <x-web_form::form.control-group.error :control-name="$fieldName" />

                @break

            @case('price')
                <x-web_form::form.control-group.control
                    type="text"
                    :name="$fieldName"
                    :id="$fieldName"
                    :rules="$validations.'|numeric'"
                    :label="$attribute->name ?? $parentAttribute->name"
                    :placeholder="$attribute->placeholder"
                />

                <x-web_form::form.control-group.error :control-name="$fieldName" />

                @break

            @case('email')
                <x-web_form::form.control-group.control
                    type="email"
                    name="{{ $fieldName }}[0][value]"
                    id="{{ $fieldName }}[0][value]"
                    rules="{{ $validations }}|email"
                    :label="$attribute->name ?? $parentAttribute->name"
                    :placeholder="$attribute->placeholder"
                />

                <x-web_form::form.control-group.control
                    type="hidden"
                    name="{{ $fieldName }}[0][label]"
                    id="{{ $fieldName }}[0][label]"
                    rules="required"
                    value="work"
                />

                <x-web_form::form.control-group.error control-name="{{ $fieldName }}[0][value]" />

                @break

            @case('checkbox')
                @php
                    $options = $parentAttribute->lookup_type
                        ? app('AHATechnocrats\Attribute\Repositories\AttributeRepository')->getLookUpOptions($parentAttribute->lookup_type)
                        : $parentAttribute->options()->orderBy('sort_order')->get();
                @endphp

                <div style="display: flex !important; flex-direction: column !important; gap: 12px !important; margin-top: 8px !important; width: 100% !important;">
                    @foreach ($options as $option)
                        <v-field
                            v-slot="{ field }"
                            type="checkbox"
                            name="{{ $fieldName }}[]"
                            value="{{ $option->id }}"
                            rules="{{ $loop->first ? $validations : '' }}"
                        >
                            <label style="display: flex !important; align-items: center !important; justify-content: flex-start !important; gap: 12px !important; margin: 0 !important; padding: 0 !important; width: 100% !important; cursor: pointer !important; text-align: left !important; float: none !important;">
                                <input
                                    type="checkbox"
                                    v-bind="field"
                                    value="{{ $option->id }}"
                                    style="margin: 0 !important; width: 20px !important; height: 20px !important; flex-shrink: 0 !important; cursor: pointer !important;"
                                    class="rounded-sm border-2 border-gray-400 bg-white text-gray-700 focus:ring-0 checked:border-gray-700 checked:bg-gray-700 dark:border-gray-500 dark:bg-gray-800"
                                />
                                <span style="font-size: 15px !important; font-weight: 400 !important; margin: 0 !important; padding: 0 !important; text-align: left !important; width: auto !important; display: block !important;" class="text-gray-800 dark:text-gray-200">
                                    {{ $option->name }}
                                </span>
                            </label>
                        </v-field>
                    @endforeach
                </div>

                <x-web_form::form.control-group.error :control-name="$fieldName.'[]'" />

                @break

            @case('file')
            @case('image')
                <x-web_form::form.control-group.control
                    type="file"
                    :name="$fieldName"
                    :id="$fieldName"
                    :rules="$validations"
                    :placeholder="$attribute->placeholder"
                    :label="$attribute->name ?? $parentAttribute->name"
                />

                <x-web_form::form.control-group.error control-name="{{ $fieldName }}" />

                @break

            @case('phone')
                <x-web_form::form.control-group.control
                    type="text"
                    name="{{ $fieldName }}[0][value]"
                    id="{{ $fieldName }}[0][value]"
                    rules="{{ $validations }}|phone"
                    :label="$attribute->name ?? $parentAttribute->name"
                    :placeholder="$attribute->placeholder"
                />

                <x-web_form::form.control-group.control
                    type="hidden"
                    name="{{ $fieldName }}[0][label]"
                    id="{{ $fieldName }}[0][label]"
                    rules="required"
                    value="work"
                />

                <x-web_form::form.control-group.error control-name="{{ $fieldName }}[0][value]" />

                @break

            @case('date')
                <x-web_form::form.control-group.control
                    type="date"
                    :name="$fieldName"
                    :id="$fieldName"
                    :rules="$validations"
                    :label="$attribute->name ?? $parentAttribute->name"
                    :placeholder="$attribute->placeholder"
                />

                <x-web_form::form.control-group.error :control-name="$fieldName" />

                @break

            @case('datetime')
                <x-web_form::form.control-group.control
                    type="datetime"
                    :name="$fieldName"
                    :id="$fieldName"
                    :rules="$validations"
                    :label="$attribute->name ?? $parentAttribute->name"
                    :placeholder="$attribute->placeholder"
                />

                <x-web_form::form.control-group.error :control-name="$fieldName" />

                @break

            @case('select')
            @case('lookup')
                @php
                    $options = $parentAttribute->lookup_type
                        ? app('AHATechnocrats\Attribute\Repositories\AttributeRepository')->getLookUpOptions($parentAttribute->lookup_type)
                        : $parentAttribute->options()->orderBy('sort_order')->get();
                @endphp

                <x-web_form::form.control-group.control
                    type="select"
                    :name="$fieldName"
                    :id="$fieldName"
                    :rules="$validations"
                    :label="$attribute->name ?? $parentAttribute->name"
                    :placeholder="$attribute->placeholder"
                >
                    @foreach ($options as $option)
                        <option value="{{ $option->id }}">{{ $option->name }}</option>
                    @endforeach
                </x-web_form::form.control-group.control>

                <x-web_form::form.control-group.error :control-name="$fieldName" />

                @break

            @case('multiselect')
                @php
                    $options = $parentAttribute->lookup_type
                        ? app('AHATechnocrats\Attribute\Repositories\AttributeRepository')->getLookUpOptions($parentAttribute->lookup_type)
                        : $parentAttribute->options()->orderBy('sort_order')->get();
                @endphp

                <x-web_form::form.control-group.control
                    type="select"
                    id="{{ $fieldName }}"
                    name="{{ $fieldName }}[]"
                    :rules="$validations"
                    :label="$attribute->name ?? $parentAttribute->name"
                    :placeholder="$attribute->placeholder"
                >
                    @foreach ($options as $option)
                        <option value="{{ $option->id }}">{{ $option->name }}</option>
                    @endforeach
                </x-web_form::form.control-group.control>

                <x-web_form::form.control-group.error :control-name="$fieldName" />

                @break

            @case('boolean')
                <x-web_form::form.control-group.control
                    type="select"
                    :name="$fieldName"
                    :id="$fieldName"
                    :rules="$validations"
                    :label="$attribute->name ?? $parentAttribute->name"
                    :placeholder="$attribute->placeholder"
                >
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </x-web_form::form.control-group.control>

                <x-web_form::form.control-group.error :control-name="$fieldName" />

                @break
        @endswitch
    </x-web_form::form.control-group>
</div>
@endif
