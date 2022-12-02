<?php
declare(strict_types = 1);

use Bitmotion\Mautic\Middleware\AuthorizeMiddleware;

return [
    'frontend' => [
        'bitmotion/mautic/authorize' => [
            'target' => AuthorizeMiddleware::class,
            'after' => [
                'typo3/cms-frontend/backend-user-authentication',
            ],
            'before' => [
                'typo3/cms-frontend/base-redirect-resolver',
            ],
        ],
    ],
];
