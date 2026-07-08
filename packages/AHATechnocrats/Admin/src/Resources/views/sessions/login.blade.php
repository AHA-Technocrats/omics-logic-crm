<x-admin::layouts.anonymous>
    <x-slot:title>
        @lang('admin::app.users.login.title')
    </x-slot>

    @push('styles')
        @include('admin::sessions.partials.auth-shell-styles')

        <style>
            .omics-login__divider::before,
            .omics-login__divider::after {
                background-color: #e2e8f0;
                content: '';
                flex: 1;
                height: 1px;
            }

            .omics-login__google {
                background-color: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 0.625rem;
                color: #334155;
                cursor: pointer;
                font-size: 0.9375rem;
                font-weight: 500;
                min-height: 3rem;
                transition: background-color 0.2s ease, border-color 0.2s ease;
                width: 100%;
            }

            .omics-login__google:hover {
                background-color: #f8fafc;
                border-color: #cbd5e1;
            }
        </style>
    @endpush

    <div class="omics-login flex min-h-[100vh]">
        @include('admin::sessions.partials.auth-shell-hero')

        <div class="omics-login__panel flex min-h-[100vh] w-full flex-col items-center justify-center px-6 py-10 lg:w-1/2 lg:px-16">
            <div class="w-full max-w-[420px]">
                {!! view_render_event('admin.sessions.login.form_controls.before') !!}

                <div class="mb-8">
                    <h1 class="omics-login__heading text-[2rem] font-bold leading-tight">
                        @lang('admin::app.users.login.welcome-title')
                    </h1>

                    <p class="omics-login__subtitle mt-2 text-base">
                        @lang('admin::app.users.login.welcome-subtitle')
                    </p>
                </div>

                <x-admin::form :action="route('admin.session.store')">
                    <div class="flex flex-col gap-5">
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="omics-login__label required !mb-2 !font-medium !text-[#334155]">
                                @lang('admin::app.users.login.email')
                            </x-admin::form.control-group.label>

                            <div class="relative">
                                <span class="pointer-events-none absolute left-3.5 top-1/2 z-10 -translate-y-1/2 text-[#94a3b8]">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M20 21V19C20 16.7909 18.2091 15 16 15H8C5.79086 15 4 16.7909 4 19V21" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>

                                <x-admin::form.control-group.control
                                    type="email"
                                    class="omics-login__input !w-full !pl-11"
                                    id="email"
                                    name="email"
                                    rules="required|email"
                                    :label="trans('admin::app.users.login.email')"
                                    :placeholder="trans('admin::app.users.login.email-placeholder')"
                                />
                            </div>

                            <x-admin::form.control-group.error control-name="email" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="omics-login__label required !mb-2 !font-medium !text-[#334155]">
                                @lang('admin::app.users.login.password')
                            </x-admin::form.control-group.label>

                            <div class="relative">
                                <span class="pointer-events-none absolute left-3.5 top-1/2 z-10 -translate-y-1/2 text-[#94a3b8]">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <rect x="5" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.75"/>
                                        <path d="M8 11V8C8 5.79086 9.79086 4 12 4C14.2091 4 16 5.79086 16 8V11" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                                    </svg>
                                </span>

                                <x-admin::form.control-group.control
                                    type="password"
                                    class="omics-login__input !w-full !pl-11 !pr-11"
                                    id="password"
                                    name="password"
                                    rules="required|min:6"
                                    :label="trans('admin::app.users.login.password')"
                                    :placeholder="trans('admin::app.users.login.password-placeholder')"
                                />

                                <button
                                    type="button"
                                    class="icon-eye-hide absolute right-3.5 top-1/2 z-10 -translate-y-1/2 cursor-pointer border-0 bg-transparent p-0 text-xl text-[#94a3b8]"
                                    onclick="switchVisibility()"
                                    id="visibilityIcon"
                                    aria-label="Toggle password visibility"
                                ></button>
                            </div>

                            <x-admin::form.control-group.error control-name="password" />
                        </x-admin::form.control-group>

                        <div class="-mt-1 flex justify-end">
                            <a
                                class="omics-login__link text-sm font-medium"
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

                        <!-- <div class="omics-login__divider flex items-center gap-4">
                            <span class="text-sm text-[#94a3b8]">
                                @lang('admin::app.users.login.or-divider')
                            </span>
                        </div> -->

                        <!-- <button
                            type="button"
                            class="omics-login__google flex items-center justify-center gap-3"
                        >
                            <svg width="18" height="18" viewBox="0 0 18 18" aria-hidden="true">
                                <path fill="#4285F4" d="M17.64 9.2045C17.64 8.5665 17.5827 8.001 17.4764 7.4545H9V10.7455H13.8436C13.635 11.84 13.0009 12.7545 12.0477 13.3182V15.5682H14.9564C16.6582 14.0018 17.64 11.8182 17.64 9.2045Z"/>
                                <path fill="#34A853" d="M9 18C11.43 18 13.4673 17.1945 14.9564 15.5682L12.0477 13.3182C11.2418 13.8636 10.2109 14.1818 9 14.1818C6.65455 14.1818 4.67182 12.5909 3.96409 10.5H0.957275V12.8182C2.43818 15.6818 5.48182 18 9 18Z"/>
                                <path fill="#FBBC05" d="M3.96409 10.5C3.78409 9.90909 3.68182 9.27273 3.68182 8.5C3.68182 7.72727 3.78409 7.09091 3.96409 6.5V4.18182H0.957275C0.347727 5.39091 0 6.90909 0 8.5C0 10.0909 0.347727 11.6091 0.957275 12.8182L3.96409 10.5Z"/>
                                <path fill="#EA4335" d="M9 3.81818C10.3218 3.81818 11.5077 4.27273 12.3527 5.13636L14.8927 2.59636C13.4636 1.25455 11.4264 0.363636 9 0.363636C5.48182 0.363636 2.43818 2.68182 0.957275 5.54545L3.96409 7.86364C4.67182 5.77273 6.65455 4.18182 9 4.18182Z"/>
                            </svg>

                            @lang('admin::app.users.login.google-sign-in')
                        </button> -->
                    </div>
                </x-admin::form>

                <p class="omics-login__footer mt-10 text-center text-sm">
                    @lang('admin::app.users.login.new-user')

                    <a class="omics-login__link font-medium" href="https://ahatechnocratscrm.com/">
                        @lang('admin::app.users.login.contact-admin')
                    </a>
                </p>

                {!! view_render_event('admin.sessions.login.form_controls.after') !!}
            </div>
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
