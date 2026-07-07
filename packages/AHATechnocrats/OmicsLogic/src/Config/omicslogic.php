<?php

return [
    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],

    'anti_spam' => [
        'min_submit_seconds' => (int) env('FORM_MIN_SUBMIT_SECONDS', 3),
        'rate_limit_ip' => (int) env('FORM_RATE_LIMIT_IP', 10),
        'rate_limit_email' => (int) env('FORM_RATE_LIMIT_EMAIL', 5),
        'block_disposable_email' => (bool) env('FORM_BLOCK_DISPOSABLE', true),
        'honeypot_field' => '_website_url',
    ],

    'dedup' => [
        'auto_merge_threshold' => 0.95,
        'review_threshold' => 0.70,
    ],

    'lead_score' => [
        'profile_weight' => 0.30,
        'engagement_weight' => 0.40,
        'intent_weight' => 0.20,
        'recency_weight' => 0.10,
    ],

    'countries' => [
        'India', 'United States', 'United Kingdom', 'Canada', 'Australia', 'Germany', 'France',
        'Netherlands', 'Italy', 'Spain', 'Switzerland', 'Sweden', 'Ireland', 'Nigeria',
        'South Africa', 'Kenya', 'Ghana', 'Egypt', 'Morocco', 'Ethiopia', 'Pakistan',
        'Bangladesh', 'Sri Lanka', 'Nepal', 'China', 'Japan', 'South Korea', 'Singapore',
        'Malaysia', 'Indonesia', 'Philippines', 'Vietnam', 'Thailand', 'Iran', 'Saudi Arabia',
        'United Arab Emirates', 'Qatar', 'Turkey', 'Israel', 'Jordan', 'Lebanon', 'Brazil',
        'Mexico', 'Argentina', 'Colombia', 'Chile', 'Russia', 'Poland', 'Ukraine', 'Other',
    ],

    'campaign_categories' => [
        'Transcriptomics',
        'NGS Wet Lab',
        'AI / Cheminformatics',
        'Clinical',
        'Metagenomics',
        'ML',
        'Cheminformatics',
        'Oncology',
    ],

    'portal' => [
        'leads_path' => env('OMICS_PORTAL_LEADS_PATH', '/api/crm/leads'),
        'timeout' => (int) env('OMICS_PORTAL_API_TIMEOUT', 30),
    ],

    'programs' => [
        ['key' => 'intro-bioinformatics', 'name' => 'Introduction to Modern Bioinformatics'],
        ['key' => 'genomic-data-analysis', 'name' => 'Genomic Data Analysis for Biomedical Research'],
        ['key' => 'transcriptomics', 'name' => 'Transcriptomics for Biomedical Research'],
        ['key' => 'metagenomics', 'name' => 'Metagenomic Data Analysis'],
        ['key' => 'python-data-science', 'name' => 'Biomedical Data Science using Python'],
        ['key' => 'r-data-science', 'name' => 'Biomedical Data Science using R'],
        ['key' => 'other', 'name' => 'Other:'],
    ],
];
