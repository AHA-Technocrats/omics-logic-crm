<x-admin::layouts.anonymous>
    <x-slot:title>
        @lang('admin::app.users.forget-password.create.page-title')
    </x-slot>

    @push('styles')
        @include('admin::sessions.partials.auth-shell-styles')

        <style>
            .omics-login__panel--card {
                background-color: #0c1b3f;
                background-image: url('{{ vite()->asset('images/login-panel-bg.png') }}');
                background-position: center;
                background-repeat: no-repeat;
                background-size: cover;
                position: relative;
                border-left: 1px solid rgba(255, 255, 255, .281);
            }

            .omics-login__card {
                background-color: #ffffff;
                border-radius: 1rem;
                box-shadow: 0 18px 45px rgba(15, 43, 91, 0.1);
                padding: 2.5rem;
                max-width: 600px;
            }

            .omics-login__card-title {
                color: #111827;
                font-size: 1.75rem;
                font-weight: 700;
                line-height: 1.2;
            }

            .omics-login__card-hint {
                color: #6b7280;
                font-size: 0.8125rem;
                line-height: 1.5;
                margin-top: 0.75rem;
            }

            .omics-login__card .omics-login__label {
                color: #111827;
                font-size: 0.875rem;
                font-weight: 500;
            }

            .omics-login__card .omics-login__input {
                background-color: #eef3fb !important;
                border-color: transparent !important;
                border-radius: 0.75rem !important;
                min-height: 3rem;
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }

            .omics-login__card .omics-login__input:hover,
            .omics-login__card .omics-login__input:focus {
                background-color: #e8effa !important;
                border-color: #dbeafe !important;
            }

            .omics-login__card .omics-login__submit {
                background-color: #2563eb;
                border-color: #2563eb;
                border-radius: 0.75rem;
                font-size: 1rem;
                font-weight: 600;
                min-height: 3.25rem;
            }

            .omics-login__card .omics-login__submit:hover,
            .omics-login__card .omics-login__submit:focus {
                background-color: #1d4ed8;
                border-color: #1d4ed8;
            }

            .omics-login__back-link {
                color: #2563eb;
                font-size: 0.8125rem;
                font-weight: 500;
            }

            .omics-login__back-link:hover {
                color: #1d5cc9;
            }
        </style>
    @endpush

    <div class="omics-login flex min-h-[100vh]">
        @include('admin::sessions.partials.auth-shell-hero')

        <div class="omics-login__panel omics-login__panel--card flex min-h-[100vh] w-full items-center justify-center px-6 py-10 lg:w-1/2 lg:px-12">
            <div class="omics-login__card w-full max-w-[480px]">
                {!! view_render_event('admin.sessions.forgor_password.form_controls.before') !!}

                <div>
                    <h1 class="omics-login__card-title">
                        @lang('admin::app.users.forget-password.create.title')
                    </h1>

                    <p class="omics-login__card-hint">
                        @lang('admin::app.users.forget-password.create.subtitle')
                    </p>
                </div>

                <x-admin::form :action="route('admin.forgot_password.store')">
                    <div class="mt-6 flex flex-col gap-5">
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="omics-login__label required !mb-2 !font-medium">
                                @lang('admin::app.users.forget-password.create.email')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="email"
                                class="omics-login__input !w-full"
                                id="email"
                                name="email"
                                rules="required|email"
                                :value="old('email')"
                                :label="trans('admin::app.users.forget-password.create.email')"
                                :placeholder="trans('admin::app.users.forget-password.create.email-placeholder')"
                            />

                            <x-admin::form.control-group.error control-name="email" />
                        </x-admin::form.control-group>

                        <button
                            type="submit"
                            class="omics-login__submit"
                            aria-label="{{ trans('admin::app.users.forget-password.create.submit-btn') }}"
                        >
                            @lang('admin::app.users.forget-password.create.submit-btn')
                        </button>
                    </div>
                </x-admin::form>

                <p class="mt-6 text-center">
                    <a
                        class="omics-login__back-link"
                        href="{{ route('admin.session.create') }}"
                    >
                        @lang('admin::app.users.forget-password.create.sign-in-link')
                    </a>
                </p>

                {!! view_render_event('admin.sessions.forgor_password.form_controls.after') !!}
            </div>
        </div>
    </div>
</x-admin::layouts.anonymous>
