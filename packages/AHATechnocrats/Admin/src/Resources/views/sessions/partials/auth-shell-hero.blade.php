<div class="omics-login__hero relative hidden min-h-[100vh] w-1/2 overflow-hidden lg:block">
    <img
        class="omics-login__hero-bg absolute inset-0 h-full w-full"
        src="{{ vite()->asset('images/login-hero-panel.png') }}"
        alt=""
        aria-hidden="true"
    />

    <div class="omics-login__hero-overlay" aria-hidden="true"></div>

    <div class="omics-login__hero-inner relative z-10 flex min-h-[100vh] flex-col">
        <img
            class="omics-login__hero-logo"
            src="{{ vite()->asset('images/omics-logic-logo.png') }}"
            alt="OmicsLogic"
        />

        <div class="omics-login__hero-content">
            <h2 class="omics-login__hero-title">
                @lang('admin::app.users.login.hero-heading-line-1')
                <br>
                @lang('admin::app.users.login.hero-heading-line-2')
                <span class="omics-login__hero-highlight">@lang('admin::app.users.login.hero-heading-highlight')</span>
            </h2>

            <p class="omics-login__hero-description mt-6">
                @lang('admin::app.users.login.hero-description')
            </p>
        </div>
    </div>
</div>
