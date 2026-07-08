<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Firebase credentials
    |--------------------------------------------------------------------------
    |
    | Option A (local dev): place your service account JSON at the default path
    | below (storage/app/private/firebase/service-account.json). This directory
    | is NOT web-accessible and is excluded from git by storage/app/.gitignore.
    |
    | Option B: set FIREBASE_CREDENTIALS to a custom absolute file path.
    |
    | Option C: set FIREBASE_CREDENTIALS_JSON with the full JSON string (common
    | on Docker / cloud hosts where mounting a file is awkward).
    |
    | You do NOT need both a file and FIREBASE_CREDENTIALS_JSON — pick one.
    |
    */

    'credentials_json' => env('FIREBASE_CREDENTIALS_JSON'),

    'credentials' => env('FIREBASE_CREDENTIALS') ?: storage_path('app/private/firebase/service-account.json'),

    'project_id' => env('FIREBASE_PROJECT_ID'),

    /*
  |--------------------------------------------------------------------------
  | Firestore transport
  |--------------------------------------------------------------------------
  |
  | Use "rest" for local Windows/dev without the gRPC PHP extension.
  | Use "grpc" in production when ext-grpc is installed for better performance.
  |
  */

    'transport' => env('FIREBASE_TRANSPORT', 'rest'),

    /*
    |--------------------------------------------------------------------------
    | Google HTTP client (OAuth + Firestore REST)
    |--------------------------------------------------------------------------
    |
    | On Windows local dev, PHP often lacks a CA bundle and SSL calls to
    | Google fail (cURL error 60). By default we disable verify in "local"
    | only. For production, leave verify enabled or set FIREBASE_CA_BUNDLE.
    |
    */

    'http' => [
        'verify' => env('FIREBASE_HTTP_VERIFY'),
        'ca_bundle' => env('FIREBASE_CA_BUNDLE'),
        'timeout' => (int) env('FIREBASE_HTTP_TIMEOUT', 15),
        'connect_timeout' => (int) env('FIREBASE_HTTP_CONNECT_TIMEOUT', 10),
    ],

    'api_key' => env('FIREBASE_API_KEY'),

    'collections' => [
        'users' => env('FIREBASE_USERS_COLLECTION', 'Users'),
        'achievements' => env('FIREBASE_ACHIEVEMENTS_COLLECTION', 'Achievements'),
        'forms' => env('FIREBASE_FORMS_COLLECTION', 'Forms'),
    ],

    'pagination' => [
        'default_limit' => 20,
        'max_limit' => 100,
    ],

    'forms' => [
        'date_field' => env('FIREBASE_FORMS_DATE_FIELD', 'submittedAt'),
        'default_sort_field' => env('FIREBASE_FORMS_SORT_FIELD', 'submittedAt'),
        'default_sort_direction' => env('FIREBASE_FORMS_SORT_DIRECTION', 'desc'),
        'field_map' => [
            'email' => ['email', 'email_address', 'emailAddress'],
            'name' => ['name', 'full_name', 'fullName'],
            'phone' => ['phone', 'phone_number', 'phoneNumber', 'contact_number'],
            'country' => ['country', 'country_code', 'countryCode'],
            'organization' => ['organization', 'organization_name', 'company', 'university'],
            'education' => ['education_level', 'education', 'educationLevel'],
            'program_interest' => ['program_interest', 'programInterest', 'programSlug', 'programId', 'campaign', 'interest'],
            'inquiry_details' => ['inquiry_details', 'message', 'comments', 'notes', 'feedbacks', 'feedback'],
        ],
    ],

    'achievements' => [
        'order_field' => env('FIREBASE_ACHIEVEMENTS_ORDER_FIELD', 'timeStamp'),
        'order_direction' => env('FIREBASE_ACHIEVEMENTS_ORDER_DIRECTION', 'desc'),
        'timestamp_fields' => ['timeStamp', 'completedAt', 'createdAt', 'updatedAt', 'occurredAt'],
        'type_icons' => [
            'lesson_completed' => ['icon' => 'icon-tick', 'icon_class' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300'],
            'step' => ['icon' => 'icon-tick', 'icon_class' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300'],
            'course' => ['icon' => 'icon-product', 'icon_class' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300'],
            'purchase' => ['icon' => 'icon-cart', 'icon_class' => 'bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300'],
            'registration' => ['icon' => 'icon-activity', 'icon_class' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300'],
            'default' => ['icon' => 'icon-activity', 'icon_class' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300'],
        ],
    ],

    'sync' => [
        'enabled' => env('FIREBASE_FORMS_SYNC_ENABLED', true),
        'batch_size' => (int) env('FIREBASE_FORMS_SYNC_BATCH_SIZE', 50),
        'default_web_form_id' => env('FIREBASE_DEFAULT_WEB_FORM_ID'),
    ],

];
