@php
    use AHATechnocrats\WebForm\Helpers\WebFormFieldOrder;

    $fieldOrder = WebFormFieldOrder::resolveOrder($webForm);
    $attributesById = $webForm->attributes->loadMissing('attribute')->keyBy('id');
@endphp

@foreach ($fieldOrder as $fieldKey)
    @if (str_starts_with($fieldKey, 'builtin:'))
        @php
            $partial = WebFormFieldOrder::partialForKey($fieldKey);
        @endphp

        @if ($partial)
            @include('web_form::settings.web-forms.fields.'.$partial, ['webForm' => $webForm])
        @endif
    @elseif (str_starts_with($fieldKey, 'attribute:'))
        @php
            $attributeId = (int) substr($fieldKey, strlen('attribute:'));
            $attribute = $attributesById->get($attributeId);
        @endphp

        @if ($attribute)
            @include('web_form::settings.web-forms.fields.attribute', [
                'webForm' => $webForm,
                'attribute' => $attribute,
            ])
        @endif
    @endif
@endforeach
