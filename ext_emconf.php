<?php

$EM_CONF['mautic'] = [
    'title' => 'Marketing Automation - Mautic Adapter',
    'description' => 'Add-on TYPO3 extension that enhances the "marketing-automation" TYPO3 extension by connecting it to the Mautic Marketing Automation platform: Determine "Persona" from Mautic segments. Also provides additional services e.g. language synchronisation between Mautic and TYPO3.',
    'category' => 'fe',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'author' => 'Florian Wessels, Jurian Jansen',
    'author_company' => 'Leuchtfeuer Digital Marketing',
    'author_email' => 'dev@Leuchtfeuer.com',
    'clearCacheOnLoad' => 0,
    'version' => '5.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.*',
            'marketing_automation' => '*',
        ],
        'conflicts' => [],
        'suggests' => [
            'static_info_tables' => '6.7.0',
            'form' => '11.5.*'
        ],
    ],
];

