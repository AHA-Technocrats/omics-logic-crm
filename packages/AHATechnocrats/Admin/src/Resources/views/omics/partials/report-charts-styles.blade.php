@once
    @push('styles')
        <style>
            .omics-report-bars {
                display: flex;
                flex-direction: column;
                gap: 12px;
            }

            .omics-bar-row {
                display: grid;
                grid-template-columns: minmax(0, 1.2fr) minmax(0, 2fr) 3rem;
                align-items: center;
                gap: 12px;
            }

            .omics-bar-label {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                font-size: 14px;
            }

            .omics-bar-track {
                height: 10px;
                width: 100%;
                border-radius: 9999px;
                background: #e5e7eb;
                overflow: hidden;
            }

            .dark .omics-bar-track {
                background: #374151;
            }

            .omics-bar-fill {
                display: block;
                height: 10px;
                border-radius: 9999px;
            }

            .omics-bar-value {
                text-align: right;
                font-size: 14px;
                font-weight: 600;
                color: #4b5563;
            }

            .dark .omics-bar-value {
                color: #d1d5db;
            }

            .omics-funnel {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 8px;
                width: 100%;
                max-width: 32rem;
                margin: 0 auto;
            }

            .omics-funnel-step {
                border-radius: 6px;
                padding: 10px 16px;
                text-align: center;
                font-size: 13px;
                font-weight: 600;
                color: #fff;
                min-height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .omics-funnel-step.is-empty {
                opacity: 0.45;
            }

            .omics-funnel-caption {
                margin-top: 12px;
                text-align: center;
                font-size: 12px;
                color: #6b7280;
            }

            .dark .omics-funnel-caption {
                color: #9ca3af;
            }
        </style>
    @endpush
@endonce
