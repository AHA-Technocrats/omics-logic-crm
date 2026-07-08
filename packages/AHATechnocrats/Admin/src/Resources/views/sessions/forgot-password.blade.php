<x-admin::layouts.anonymous>
    <x-slot:title>
        @lang('admin::app.users.forget-password.create.page-title')
    </x-slot>

    @push('styles')
        @include('admin::sessions.partials.auth-shell-styles')
    @endpush

    <div class="omics-login flex min-h-[100vh]">
        @include('admin::sessions.partials.auth-shell-hero')

        <div class="omics-login__panel flex min-h-[100vh] w-full flex-col items-center justify-center px-6 py-10 lg:w-1/2 lg:px-16">
            <div class="w-full max-w-[420px]">
                {!! view_render_event('admin.sessions.forgor_password.form_controls.before') !!}

                <div class="mb-8">
                    <h1 class="omics-login__heading text-[2rem] font-bold leading-tight">
                        @lang('admin::app.users.forget-password.create.title')
                    </h1>

                    <p class="omics-login__subtitle mt-2 text-base">
                        @lang('admin::app.users.forget-password.create.subtitle')
                    </p>
                </div>

                <x-admin::form :action="route('admin.forgot_password.store')">
                    <div class="flex flex-col gap-5">
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="omics-login__label required !mb-2 !font-medium !text-[#334155]">
                                @lang('admin::app.users.forget-password.create.email')
                            </x-admin::form.control-group.label>

                            <div class="relative">
                                <span class="pointer-events-none absolute left-3.5 top-1/2 z-10 -translate-y-1/2 text-[#94a3b8]">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M4 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H4C2.9 20 2 19.1 2 18V6C2 4.9 2.9 4 4 4Z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M22 6L12 13L2 6" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>

                                <x-admin::form.control-group.control
                                    type="email"
                                    class="omics-login__input !w-full !pl-11"
                                    id="email"
                                    name="email"
                                    rules="required|email"
                                    :value="old('email')"
                                    :label="trans('admin::app.users.forget-password.create.email')"
                                    :placeholder="trans('admin::app.users.forget-password.create.email-placeholder')"
                                />
                            </div>

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

                <p class="omics-login__footer mt-10 text-center text-sm">
                    <a
                        class="omics-login__link font-medium"
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
