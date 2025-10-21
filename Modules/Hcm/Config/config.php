<?php

return [
    'name' => 'Hcm',
    'module_version' => '1.0.0',
    'description' => 'Havelock City Mall Integration - Connects Ultimate Forester POS with HCM API for real-time invoice sync and reporting',
    'pid' => config('app.pid', 'ULTIMATE_POS'),
    'author' => 'Ultimate Forester Team',
    'website' => 'https://ultimateforester.com',
    'requirements' => [
        'php' => '>=8.0',
        'laravel' => '>=10.0'
    ],
    'features' => [
        'Real-time sales sync',
        'Invoice management',
        'API monitoring',
        'Tenant configuration',
        'Ping monitoring',
        'Comprehensive logging'
    ],
    'api_endpoints' => [
        'ping' => '/api/ping',
        'sales' => '/api/sales',
        'invoice' => '/api/invoice'
    ]
];
<?php

return [
    'name' => 'Hcm',
    'module_version' => '1.0',
    'pid' => 23,
];
