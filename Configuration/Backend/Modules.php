<?php

declare(strict_types=1);

use Bitmotion\Mautic\Controller\BackendController;

// ExtensionUtility::registerModule(
//     'mautic',
//     'tools',
//     'api',
//     'bottom',
//     [
//         BackendController::class => 'show, save'
//     ],
//     [
//         'access' => 'admin',
//         'iconIdentifier' => 'tx_mautic-mautic-icon',
//         'labels' => 'LLL:EXT:mautic/Resources/Private/Language/locallang_mod.xlf',
//     ]
// );


return [
    'mautic_api' => [
        'parent' => 'tools',
        'position' => ['after' => 'csp'],
        'access' => 'admin',
        'path' => '/module/tools/mautic',
        'labels' => 'LLL:EXT:mautic/Resources/Private/Language/locallang_mod.xlf',
        'iconIdentifier' => 'tx_mautic-mautic-icon',

        'extensionName' => 'Toujou',
        'controllerActions' => [
            BackendController::class => [
                'show', 'save',
            ],
        ],
        'inheritNavigationComponentFromMainModule' => false,
    ],
];
