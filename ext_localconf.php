<?php

use Bitmotion\Mautic\Transformation\FormField\FileTransformation;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use Bitmotion\Mautic\Hooks\MauticTrackingHook;
use Bitmotion\Mautic\Hooks\PageLayoutView\MauticFormPreviewRenderer;
use Bitmotion\Mautic\Hooks\TCEmainHook;
use Bitmotion\Mautic\Hooks\MauticTagHook;
use Bitmotion\Mautic\Form\FormDataProvider\MauticFormDataProvider;
use TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRowDefaultValues;
use TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems;
use Bitmotion\Mautic\FormEngine\FieldControl\UpdateTagsControl;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\Driver\DriverRegistry;
use Bitmotion\Mautic\Driver\AssetDriver;
use TYPO3\CMS\Core\Resource\Index\ExtractorRegistry;
use Bitmotion\Mautic\Index\Extractor;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use Bitmotion\Mautic\Controller\FrontendController;
use Bitmotion\Mautic\Transformation\Form\CampaignFormTransformation;
use Bitmotion\Mautic\Transformation\Form\StandaloneFormTransformation;
use Bitmotion\Mautic\Transformation\FormField\IgnoreTransformation;
use Bitmotion\Mautic\Transformation\FormField\CheckboxTransformation;
use Bitmotion\Mautic\Transformation\FormField\DatetimeTransformation;
use Bitmotion\Mautic\Transformation\FormField\EmailTransformation;
use Bitmotion\Mautic\Transformation\FormField\HiddenTransformation;
use Bitmotion\Mautic\Transformation\FormField\MultiCheckboxTransformation;
use Bitmotion\Mautic\Transformation\FormField\MultiSelectTransformation;
use Bitmotion\Mautic\Transformation\FormField\NumberTransformation;
use Bitmotion\Mautic\Transformation\FormField\RadioButtonTransformation;
use Bitmotion\Mautic\Transformation\FormField\SingleSelectTransformation;
use Bitmotion\Mautic\Transformation\FormField\TelephoneTransformation;
use Bitmotion\Mautic\Transformation\FormField\TextTransformation;
use Bitmotion\Mautic\Transformation\FormField\TextareaTransformation;
use Bitmotion\Mautic\Transformation\FormField\UrlTransformation;
use Bitmotion\Mautic\Transformation\FormField\CountryListTransformation;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\Writer\NullWriter;
use TYPO3\CMS\Core\Log\Writer\FileWriter;
defined('TYPO3') || die;

call_user_func(function () {

    ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:mautic/Configuration/PageTS/Mod/Wizards/NewContentElement.tsconfig">'
    );

    ###################
    #      HOOKS      #
    ###################

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['configArrayPostProc']['mautic'] =
        MauticTrackingHook::class . '->addTrackingCode';

    // TODO v12change: this is broken in v12, but we can think about ignoring/disabling as we don't really need it
    // Register for hook to show preview of tt_content element of CType="mautic_form" in page module
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['mautic_form'] =
        MauticFormPreviewRenderer::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['mautic'] =
        TCEmainHook::class;

    if (TYPO3 === 'FE') {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-postTransform']['mautic_tag'] =
            MauticTagHook::class . '->setTags';
    }

    ###################
    #       FORM      #
    ###################
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][MauticFormDataProvider::class] = [
        'depends' => [
            DatabaseRowDefaultValues::class,
        ],
        'before' => [
            TcaSelectItems::class,
        ],
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1551778913] = [
        'nodeName' => 'updateTagsControl',
        'priority' => 30,
        'class' => UpdateTagsControl::class,
    ];

    ##################
    #   FAL DRIVER   #
    ##################
    $driverRegistry = GeneralUtility::makeInstance(DriverRegistry::class);
    $driverRegistry->registerDriverClass(
        AssetDriver::class,
        AssetDriver::DRIVER_SHORT_NAME,
        AssetDriver::DRIVER_NAME,
        'FILE:EXT:mautic/Configuration/FlexForm/AssetDriver.xml'
    );

    ##################
    #   EXTRACTOR    #
    ##################
    GeneralUtility::makeInstance(ExtractorRegistry::class)->registerExtractionService(Extractor::class);

    ###################
    #      PLUGIN     #
    ###################
    ExtensionUtility::configurePlugin(
        'Mautic',
        'Form',
        [FrontendController::class => 'form'],
        [FrontendController::class => 'form'],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    ###################
    #     EXTCONF     #
    ###################
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic'])) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic'] = [
            'transformation' => [
                'form' => [],
                'formField' => [],
            ]
        ];
    }

    #######################
    # FORM TRANSFORMATION #
    #######################
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['form']['mautic_finisher_campaign_prototype'] = CampaignFormTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['form']['mautic_finisher_standalone_prototype'] = StandaloneFormTransformation::class;


    ########################
    # FIELD TRANSFORMATION #
    ########################
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['AdvancedPassword'] = IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Checkbox'] = CheckboxTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['ContentElement'] = IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Date'] = DatetimeTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['DatePicker'] = DatetimeTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Email'] = EmailTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['GridRow'] = IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Fieldset'] = IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['FileUpload'] = FileTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Hidden'] = HiddenTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['ImageUpload'] = FileTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['MultiCheckbox'] = MultiCheckboxTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['MultiSelect'] = MultiSelectTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Number'] = NumberTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Page'] = IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Password'] = IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['RadioButton'] = RadioButtonTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['SingleSelect'] = SingleSelectTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['StaticText'] = IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['SummaryPage'] = IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Telephone'] = TelephoneTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Text'] = TextTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Textarea'] = TextareaTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Url'] = UrlTransformation::class;

    // Register custom field transformation classes
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['CountryList'] = CountryListTransformation::class;


    ###################
    #     LOGGING     #
    ###################
    // Turn logging off by default
    $GLOBALS['TYPO3_CONF_VARS']['LOG']['Bitmotion']['Mautic'] = [
        'writerConfiguration' => [
            LogLevel::DEBUG => [
                NullWriter::class => [],
            ],
        ],
    ];

    if (Environment::getContext()->isDevelopment()) {
        $GLOBALS['TYPO3_CONF_VARS']['LOG']['Bitmotion']['Mautic'] = [
            'writerConfiguration' => [
                LogLevel::DEBUG => [
                    FileWriter::class => [
                        'logFileInfix' => 'mautic'
                    ],
                ],
            ],
        ];
    }

});
