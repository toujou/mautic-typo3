<?php
use Bitmotion\Mautic\Hooks\MauticFormHook;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use Bitmotion\Mautic\Controller\BackendController;
defined('TYPO3') || die;

call_user_func(
    function ($extensionKey) {
        // Assign the hooks for pushing newly created and edited forms to Mautic
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeFormDuplicate'][1489959059] =
            MauticFormHook::class;

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeFormDelete'][1489959059] =
            MauticFormHook::class;

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeFormSave'][1489959059] =
            MauticFormHook::class;

        ExtensionManagementUtility::addStaticFile(
            $extensionKey,
            'Configuration/TypoScript',
            'Mautic'
        );

        // Backend Module
        if (version_compare(GeneralUtility::makeInstance(Typo3Version::class)->getVersion(), '10.0.0', '<')) {
            $extensionName = 'Bitmotion.Mautic';
            $controllerName = 'Backend';
        }

        ExtensionUtility::registerModule(
            $extensionName ?? $extensionKey,
            'tools',
            'api',
            'bottom',
            [
                $controllerName ?? BackendController::class => 'show, save'
            ],
            [
                'access' => 'admin',
                'iconIdentifier' => 'tx_mautic-mautic-icon',
                'labels' => 'LLL:EXT:mautic/Resources/Private/Language/locallang_mod.xlf',
            ]
        );
}, 'mautic');
