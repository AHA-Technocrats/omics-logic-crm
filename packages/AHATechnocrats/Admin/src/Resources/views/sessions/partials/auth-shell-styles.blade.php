<link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
    rel="stylesheet"
/>

<style>
    body,
    #app {
        margin: 0;
        min-height: 100vh;
    }

    .omics-login {
        font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
    }

    .omics-login__hero img.omics-login__hero-bg {
        object-fit: cover;
        object-position: center center;
    }

    .omics-login__hero-overlay {
        background: linear-gradient(115deg, rgb(12 43 109 / 92%) 0%, rgb(12 30 69 / 72%) 40%, rgba(7, 18, 42, 0.45) 70%, rgba(7, 18, 42, 0.2) 100%);
        inset: 0;
        position: absolute;
        z-index: 1;
    }

    .omics-login__hero-inner {
        align-items: flex-start;
        padding: 2.5rem 2.5rem 2.5rem 3rem;
    }

    .omics-login__hero-logo {
        height: auto;
        max-width: 220px;
        width: auto;
    }

    .omics-login__hero-content {
        margin-top: 3.5rem;
        max-width: 700px;
        text-align: left;
    }

    .omics-login__hero-title {
        color: #ffffff;
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .omics-login__hero-highlight {
        color: #5eb3f6;
    }

    .omics-login__hero-description {
        color: rgba(255, 255, 255, 0.88);
        font-size: 1rem;
        line-height: 1.65 !important;
    }

    .omics-login__panel {
        background-color: #ffffff;
    }

    .omics-login__heading {
        color: #0f2b5b;
    }

    .omics-login__subtitle {
        color: #64748b;
    }

    .omics-login__label {
        color: #334155;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .omics-login__input {
        border-color: #e2e8f0 !important;
        border-radius: 0.625rem !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
        font-size: 0.9375rem !important;
        min-height: 3rem;
        padding-top: 0.75rem !important;
        padding-bottom: 0.75rem !important;
    }

    .omics-login__input::placeholder {
        color: #94a3b8 !important;
    }

    .omics-login__input:hover,
    .omics-login__input:focus {
        border-color: #cbd5e1 !important;
    }

    .omics-login__link {
        color: #2b6fe0;
    }

    .omics-login__link:hover {
        color: #1d5cc9;
    }

    .omics-login__submit {
        align-items: center;
        background-color: #1d5cc9;
        border: 1px solid #1d5cc9;
        border-radius: 0.625rem;
        color: #ffffff;
        cursor: pointer;
        display: flex;
        font-size: 1rem;
        font-weight: 600;
        justify-content: center;
        min-height: 3rem;
        transition: background-color 0.2s ease, border-color 0.2s ease;
        width: 100%;
    }

    .omics-login__submit:hover,
    .omics-login__submit:focus {
        background-color: #1a52b0;
        border-color: #1a52b0;
        opacity: 1;
    }

    .omics-login__footer {
        color: #64748b;
    }
</style>
