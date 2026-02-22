<?php

return [
    'puppeteer' => [
        'node_binary' => env('PUPPETEER_NODE_BINARY'),
        'npm_binary' => env('PUPPETEER_NPM_BINARY'),
        'chrome_path' => env('PUPPETEER_CHROME_PATH'),
        'no_sandbox' => (bool) env('PUPPETEER_NO_SANDBOX', true),
        'timeout' => (int) env('PUPPETEER_TIMEOUT', 60),
    ],
];
