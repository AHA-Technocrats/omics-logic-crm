<x-admin::layouts.anonymous>
    <x-slot:title>
        @lang('admin::app.users.login.title')
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
                max-width:600px
            }

            .omics-login__card-title {
                color: #111827;
                font-size: 1.75rem;
                font-weight: 700;
                line-height: 1.2;
            }

            .omics-login__card-subtitle {
                color: #4b5563;
                font-size: 0.9375rem;
                margin-top: 0.5rem;
            }

            .omics-login__card-hint {
                color: #6b7280;
                font-size: 0.8125rem;
                line-height: 1.5;
                margin-top: 0.75rem;
            }

            .omics-login__social-row {
                display: flex;
                gap: 0.75rem;
                margin-top: 1.5rem;
            }

            .omics-login__social-btn {
                align-items: center;
                background-color: #ffffff;
                border: 1px solid #e5e7eb;
                border-radius: 0.75rem;
                box-shadow: 0 4px 14px rgba(15, 43, 91, 0.06);
                cursor: pointer;
                display: flex;
                flex: 1;
                height: 3.25rem;
                justify-content: center;
                max-width: 3.25rem;
                transition: border-color 0.2s ease, box-shadow 0.2s ease;
            }

            .omics-login__social-btn:hover {
                border-color: #cbd5e1;
                box-shadow: 0 6px 18px rgba(15, 43, 91, 0.1);
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

            .omics-login__forgot-link {
                color: #2563eb;
                font-size: 0.8125rem;
                /* text-decoration: underline; */
            }

            .omics-login__forgot-link:hover {
                color: #1d5cc9;
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

            .omics-login__legal {
                color: #6b7280;
                font-size: 0.75rem;
                line-height: 1.6;
                margin-top: 1.75rem;
                text-align: center;
            }

            .omics-login__legal-link {
                color: #2563eb;
                font-weight: 600;
            }

            .omics-login__legal-link:hover {
                color: #1d4ed8;
            }

            .omics-login__visibility-toggle {
                color: #2563eb;
            }

            .forget-text {
                display: flex;
                justify-content: flex-end;
                color: #1d5cc9;
            }
        </style>
    @endpush

    <div class="omics-login flex min-h-[100vh]">
        @include('admin::sessions.partials.auth-shell-hero')

        <div class="omics-login__panel omics-login__panel--card flex min-h-[100vh] w-full items-center justify-center px-6 py-10 lg:w-1/2 lg:px-12">
            <div class="omics-login__card w-full max-w-[480px]">
                {!! view_render_event('admin.sessions.login.form_controls.before') !!}

                <div>
                    <h1 class="omics-login__card-title">
                        @lang('admin::app.users.login.card-title')
                    </h1>

                    <p class="omics-login__card-hint">
                        @lang('admin::app.users.login.social-hint')
                    </p>
                </div>



                <x-admin::form :action="route('admin.session.store')">
                    <div class="mt-6 flex flex-col gap-5">
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="omics-login__label required !mb-2 !font-medium">
                                @lang('admin::app.users.login.email')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="email"
                                class="omics-login__input !w-full"
                                id="email"
                                name="email"
                                rules="required|email"
                                :label="trans('admin::app.users.login.email')"
                                :placeholder="trans('admin::app.users.login.email-placeholder')"
                            />

                            <x-admin::form.control-group.error control-name="email" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="omics-login__label required !mb-2 !font-medium">
                                @lang('admin::app.users.login.password')
                            </x-admin::form.control-group.label>

                            <div class="relative">
                                <x-admin::form.control-group.control
                                    type="password"
                                    class="omics-login__input !w-full !pr-11"
                                    id="password"
                                    name="password"
                                    rules="required|min:6"
                                    :label="trans('admin::app.users.login.password')"
                                    :placeholder="trans('admin::app.users.login.password-placeholder')"
                                />

                                <button
                                    type="button"
                                    class="omics-login__visibility-toggle icon-eye-hide absolute right-3.5 top-1/2 z-10 -translate-y-1/2 cursor-pointer border-0 bg-transparent p-0 text-xl"
                                    onclick="switchVisibility()"
                                    id="visibilityIcon"
                                    aria-label="Toggle password visibility"
                                ></button>
                            </div>

                            <x-admin::form.control-group.error control-name="password" />
                        </x-admin::form.control-group>

                        <div class="-mt-2 forget-text">
                            <a
                                class="omics-login__forgot-link"
                                href="{{ route('admin.forgot_password.create') }}"
                            >
                                @lang('admin::app.users.login.forget-password-link')
                            </a>
                        </div>

                        <button
                            type="submit"
                            class="omics-login__submit"
                            aria-label="{{ trans('admin::app.users.login.submit-btn') }}"
                        >
                            @lang('admin::app.users.login.submit-btn')
                        </button>
                    </div>
                </x-admin::form>

                <!-- <p class="omics-login__legal">
                    @lang('admin::app.users.login.terms-text')

                    <a class="omics-login__legal-link" href="https://ahatechnocrats.com/">
                        @lang('admin::app.users.login.terms-link')
                    </a> -->

                    <!-- @lang('admin::app.users.login.and')

                    <a class="omics-login__legal-link" href="#">
                        @lang('admin::app.users.login.privacy-link')
                    </a>. -->
                <!-- </p> -->

                {!! view_render_event('admin.sessions.login.form_controls.after') !!}
            </div>
        </div>
    </div>

    <div class="fixed bottom-0 left-0 right-0 z-[1]">
        <div class="border-t bg-gray-900 py-5 text-center text-sm text-white font-normal dark:border-gray-800 dark:bg-gray-900 dark:text-white max-md:py-3">
            <p>{!! core()->getConfigData('general.settings.footer.label') !!}</p>
        </div>
    </div>

    @push('scripts')
        <script>
            function switchVisibility() {
                const passwordField = document.getElementById('password');
                const visibilityIcon = document.getElementById('visibilityIcon');

                passwordField.type = passwordField.type === 'password' ? 'text' : 'password';
                visibilityIcon.classList.toggle('icon-eye');
            }
        </script>
    @endpush
</x-admin::layouts.anonymous>
