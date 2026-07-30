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
        'honeypot_person_field' => 'persons_hp.name',
        'honeypot_organization_field' => 'organizations_hp.name',
        'honeypot_reject_message' => 'Do not fill the data in the person and organisation.',
    ],

    'dedup' => [
        'auto_merge_threshold' => 0.95,
        'review_threshold' => 0.70,
    ],

    'lead_score' => [
        'domains' => [
            'institutional' => "edu\nac.uk\nac.in\ngov\nres.in\nharvard.edu\nox.ac.uk\niitb.ac.in\ndu.ac.in\nnih.gov\ncsir.res.in",
            'company' => "pfizer.com\nnovartis.com\nthermofisher.com\nillumina.com\nbiocon.com",
            'personal' => "gmail.com\noutlook.com\nhotmail.com\nyahoo.com\nicloud.com\nproton.me\nzoho.com\nmail.com",
        ],
        'country_tiers' => [
            'tier1' => "United States\nUnited Kingdom\nCanada\nAustralia\nGermany\nFrance\nDenmark\nFinland\nItaly\nPoland\nSingapore\nUnited Arab Emirates\nUAE\nQatar\nSaudi Arabia\nPuerto Rico\nAnguilla\nNetherlands\nSwitzerland\nAustria\nNorway\nRomania\nBelgium\nIreland\nJapan\nNew Zealand",
            'tier2' => "India\nChina\nMalaysia\nBrazil\nMexico\nSouth Africa\nThailand\nColombia\nPeru\nAlgeria\nCzech Republic\nIndonesia\nTurkey\nSpain\nTaiwan\nHong Kong\nSouth Korea",
            'tier3' => "Philippines\nVietnam\nMorocco\nEgypt\nSri Lanka\nKenya\nNepal\nBhutan\nBolivia\nCambodia\nCameroon\nIraq\nUkraine\nAngola\nNigeria\nEthiopia\nUganda\nTanzania\nSenegal\nPakistan\nBangladesh\nOman\nKuwait\nBahrain\nJordan",
            'tier4' => "Iran\nSudan\nGhana\nZimbabwe\nMalawi\nMali\nMadagascar\nBenin\nTunisia",
        ],
        'bands' => [
            'hot_min' => 75,
            'warm_min' => 55,
            'nurture_min' => 35,
        ],
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
