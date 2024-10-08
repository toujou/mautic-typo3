<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Controller;

/***
 *
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2020 Florian Wessels <f.wessels@Leuchtfeuer.com>, Leuchtfeuer Digital Marketing
 *
 ***/
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use Psr\Http\Message\ResponseInterface;
use Bitmotion\Mautic\Domain\Model\AccessTokenData;
use Bitmotion\Mautic\Domain\Model\Dto\YamlConfiguration;
use Bitmotion\Mautic\Service\MauticAuthorizeService;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException;

class BackendController extends ActionController
{
    public const FLASH_MESSAGE_QUEUE = 'marketingautomation.mautic.flashMessages';

    public function __construct(private readonly ModuleTemplateFactory $moduleTemplateFactory)
    {}

    public function showAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $emConfiguration = GeneralUtility::makeInstance(YamlConfiguration::class);
        $authorizeService = GeneralUtility::makeInstance(MauticAuthorizeService::class);

        if ($authorizeService->validateCredentials() === true) {
            if (NULL === AccessTokenData::get()) {
                $moduleTemplate->assign('authorizeButton', $authorizeService->getAuthorizeButton());
            } else {
                $authorizeService->checkConnection();
            }
        }

        $moduleTemplate->assign('configuration', $emConfiguration);
        return $moduleTemplate->renderResponse('Backend/Show');

    }

    /**
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     */
    public function saveAction(array $configuration)
    {
        $emConfiguration = GeneralUtility::makeInstance(YamlConfiguration::class);

        if (str_ends_with($configuration['baseUrl'], '/')) {
            $configuration['baseUrl'] = rtrim($configuration['baseUrl'], '/');
        }

        $emConfiguration->save($configuration);
        $this->redirect('show');
    }
}
