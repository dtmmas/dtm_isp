<?php

return [
    'thermal' => [
        'enabled' => env('THERMAL_PRINT_ENABLED', true),
        'printer' => env('THERMAL_PRINTER_NAME'),
        'copies' => (int) env('THERMAL_PRINT_COPIES', 2),
        'force_raw' => env('THERMAL_FORCE_RAW', true),
        'fallback_browser' => env('THERMAL_BROWSER_FALLBACK', true),
        'line_width' => (int) env('THERMAL_LINE_WIDTH', 32),
        'qz' => [
            'script_url' => env('QZ_TRAY_SCRIPT_URL', 'https://cdn.jsdelivr.net/npm/qz-tray@2.2.6/qz-tray.js'),
            'signature_algorithm' => env('QZ_TRAY_SIGNATURE_ALGORITHM', 'SHA512'),
            'certificate_path' => env('QZ_TRAY_CERT_PATH'),
            'private_key_path' => env('QZ_TRAY_PRIVATE_KEY_PATH'),
        ],
    ],
];
