<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Azure Active Directory Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration values for Azure AD integration and Microsoft Graph API.
    | These values are used by AzureService to authenticate and manage users.
    |
    */

    'tenant_id' => env('AZURE_TENANT_ID'),
    'client_id' => env('AZURE_CLIENT_ID'),
    'client_secret' => env('AZURE_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Microsoft Graph API Configuration
    |--------------------------------------------------------------------------
    |
    | Base URLs for Microsoft Graph API and Azure AD authentication endpoints.
    |
    */

    'graph_api_base_url' => env('GRAPH_API_BASE_URL', 'https://graph.microsoft.com/v1.0'),
    'authority_base_url' => env('AUTHORITY_BASE_URL', 'https://login.microsoftonline.com'),

    /*
    |--------------------------------------------------------------------------
    | Token Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for OAuth token management and caching.
    |
    */

    'token_cache_key' => 'azure_access_token',
    'token_cache_duration' => 55, // minutes (Microsoft tokens expire at 60 mins)

    /*
    |--------------------------------------------------------------------------
    | User Provisioning Configuration
    |--------------------------------------------------------------------------
    |
    | Default settings for new Azure AD user creation.
    |
    */

    'default_domain' => env('AZURE_DEFAULT_DOMAIN', 'freight-in-time.com'),
    'default_password_length' => 16,
    'force_password_change' => true,
    'account_enabled_default' => true,
    'default_app_role_id' => env('AZURE_DEFAULT_APP_ROLE_ID', '00000000-0000-0000-0000-000000000000'),

    /*
    |--------------------------------------------------------------------------
    | User Principal Name (UPN) Generation
    |--------------------------------------------------------------------------
    |
    | Strategy for generating UPN (email) for new users:
    | 'name_based' => firstname.lastname@domain.com
    | 'employee_id' => employeeID@domain.com
    |
    */

    'upn_strategy' => env('AZURE_UPN_STRATEGY', 'name_based'),
    'upn_fallback_strategy' => 'employee_id',

    /*
    |--------------------------------------------------------------------------
    | Microsoft Graph API Endpoints
    |--------------------------------------------------------------------------
    |
    | Commonly used Graph API endpoints for reference.
    |
    */

    'endpoints' => [
        'users' => '/users',
        'groups' => '/groups',
        'service_principals' => '/servicePrincipals',
        'app_role_assignments' => '/appRoleAssignments',
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for API request retry logic.
    |
    */

    'retry' => [
        'max_attempts' => 3,
        'delay_milliseconds' => 1000, // 1 second between retries
        'exponential_backoff' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Azure-specific logging settings.
    |
    */

    'logging' => [
        'enabled' => true,
        'channel' => env('AZURE_LOG_CHANNEL', 'azure'),
        'level' => env('AZURE_LOG_LEVEL', 'info'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Integration Configuration
    |--------------------------------------------------------------------------
    |
    | Azure Group IDs and Enterprise App IDs for each module.
    | These will be moved to database eventually, but kept here for reference.
    |
    */

    'modules' => [
        'scm' => [
            'name' => 'Supply Chain Management',
            'azure_group_id' => env('AZURE_SCM_GROUP_ID'),
            'azure_enterprise_app_id' => env('AZURE_SCM_APP_ID'),
            'requires_group' => true,
            'requires_app_role' => true,
            'has_external_api' => true,
            'external_api_url' => env('SCM_API_URL', 'https://scm.freightintime.com/api/users/create'),
        ],
        'biz' => [
            'name' => 'Business Intelligence',
            'azure_group_id' => env('AZURE_BIZ_GROUP_ID'),
            'azure_enterprise_app_id' => env('AZURE_BIZ_APP_ID'),
            'requires_group' => true,
            'requires_app_role' => true,
            'has_external_api' => false,
        ],
        'fitgp' => [
            'name' => 'Freight-In-Time Global Platform',
            'azure_group_id' => env('AZURE_FITGP_GROUP_ID'),
            'azure_enterprise_app_id' => env('AZURE_FITGP_APP_ID'),
            'requires_group' => true,
            'requires_app_role' => true,
            'has_external_api' => true,
            'external_api_url' => env('FITGP_API_URL', 'https://fitgp.freightintime.com/api/provisioning/users'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Test Mode Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for testing Azure integration without affecting production.
    |
    */

    'test_mode' => env('AZURE_TEST_MODE', false),
    'test_user_domain' => env('TEST_USER_DOMAIN', 'yourdomain.com'),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Microsoft Graph API rate limits and throttling settings.
    |
    */

    'rate_limit' => [
        'requests_per_minute' => 60,
        'respect_retry_after_header' => true,
    ],

];