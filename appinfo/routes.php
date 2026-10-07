<?php
return [
    'routes' => [
        ['name' => 'pwa#getManifest', 'url' => '/manifest.json', 'verb' => 'GET'],
        ['name' => 'pwa#getServiceWorker', 'url' => '/sw.js', 'verb' => 'GET'],
        ['name' => 'pwa#getIcon', 'url' => '/icon', 'verb' => 'GET'],
        ['name' => 'pwa#getInstallPage', 'url' => '/install/{app}', 'verb' => 'GET'],
        ['name' => 'admin#saveConfig', 'url' => '/api/v1/admin/config', 'verb' => 'POST'],
        ['name' => 'admin#uploadIcon', 'url' => '/api/v1/admin/icon', 'verb' => 'POST'],
    ]
];
