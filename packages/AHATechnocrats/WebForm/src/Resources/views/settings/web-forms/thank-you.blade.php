<x-web_form::layouts>
    <x-slot:title>
        {{ strip_tags($webForm->title) }} — Thank you
    </x-slot>

    @push('styles')
        <style>
            .webform-shell {
                display: flex;
                min-height: 100vh;
                align-items: center;
                justify-content: center;
                padding: 48px 16px;
                box-sizing: border-box;
            }

            .webform-inner {
                display: flex;
                width: 100%;
                max-width: 640px;
                flex-direction: column;
                align-items: center;
                gap: 20px;
            }

            .webform-card {
                width: 100%;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                background: #fff;
                padding: 24px;
                box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
                box-sizing: border-box;
            }

            .webform-header {
                margin-bottom: 24px;
                padding-bottom: 20px;
                border-bottom: 1px solid #e5e7eb;
            }

            .webform-title {
                margin: 0 0 12px;
                font-size: 28px;
                line-height: 1.25;
                font-weight: 700;
            }

            .webform-thank-you {
                margin: 0;
                font-size: 16px;
                line-height: 1.6;
                color: #374151;
            }

            .webform-thank-you h1,
            .webform-thank-you h2,
            .webform-thank-you h3 {
                margin: 0 0 12px;
                color: inherit;
                font-weight: 600;
            }

            .webform-thank-you h2 {
                font-size: 22px;
            }

            .webform-thank-you p {
                margin: 0 0 0.75em;
            }

            .webform-thank-you p:last-child {
                margin-bottom: 0;
            }

            .webform-thank-you ul,
            .webform-thank-you ol {
                margin: 0 0 0.75em 1.25em;
                padding: 0;
            }

            .webform-submit-another {
                margin-top: 24px;
            }

            .webform-submit-another a {
                color: {{ $webForm->form_submit_button_color }};
                font-weight: 500;
                text-decoration: none;
            }

            .webform-submit-another a:hover {
                text-decoration: underline;
            }

            .dark .webform-card {
                background: #111827;
                border-color: #374151;
            }

            .dark .webform-thank-you {
                color: #e5e7eb;
            }
        </style>
    @endpush

    <div
        class="webform-shell"
        style="background-color: {{ $webForm->background_color }}"
    >
        <div class="webform-inner">
            @if ($logo = core()->getConfigData('general.general.admin_logo.logo_image'))
                <img
                    style="max-height: 64px; width: auto;"
                    src="{{ Storage::url($logo) }}"
                    alt="{{ config('app.name') }}"
                />
            @else
                <img
                    style="max-height: 64px; width: auto;"
                    src="{{ vite()->asset('images/logo.svg') }}"
                    alt="{{ config('app.name') }}"
                />
            @endif

            <div
                class="webform-card"
                style="background-color: {{ $webForm->form_background_color }}; border-top: 8px solid {{ $webForm->form_submit_button_color }}"
            >
                <div class="webform-header">
                    <h1
                        class="webform-title"
                        style="color: {{ $webForm->form_title_color }} !important;"
                    >
                        {{ $webForm->title }}
                    </h1>
                </div>

                <div class="webform-thank-you">
                    {!! $webForm->resolvedThankYouContent() !!}
                </div>

                <div class="webform-submit-another">
                    <a href="{{ route('admin.settings.web_forms.preview', $webForm->form_id) }}">
                        Submit another response
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-web_form::layouts>
