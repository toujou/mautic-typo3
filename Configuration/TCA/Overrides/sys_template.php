<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
defined('TYPO3') || die;

ExtensionManagementUtility::addStaticFile('mautic', 'Configuration/TypoScript', 'Mautic');
