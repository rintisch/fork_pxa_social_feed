<?php

declare(strict_types=1);

namespace Pixelant\PxaSocialFeed\Controller;

use Pixelant\PxaSocialFeed\Domain\Model\Configuration;
use Pixelant\PxaSocialFeed\Domain\Model\Feed;
use Pixelant\PxaSocialFeed\Domain\Model\Token;
use Pixelant\PxaSocialFeed\Domain\Repository\AbstractBackendRepository;
use Pixelant\PxaSocialFeed\Domain\Validation\Validator\ConfigurationValidator;
use Pixelant\PxaSocialFeed\Domain\Validation\Validator\TokenValidator;
use Pixelant\PxaSocialFeed\Service\Task\ImportFeedsTaskService;
use Pixelant\PxaSocialFeed\Utility\ConfigurationUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder as BackendUriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * SocialFeedAdministrationController
 */
class AdministrationController extends ActionController
{
    protected ModuleTemplate $moduleTemplate;

    public function __construct(
        protected \Pixelant\PxaSocialFeed\Domain\Repository\BackendUserGroupRepository $backendUserGroupRepository,
        private readonly PageRenderer $pageRenderer,
        protected ModuleTemplateFactory $moduleTemplateFactory,
        protected \Pixelant\PxaSocialFeed\Domain\Repository\ConfigurationRepository $configurationRepository,
        protected \Pixelant\PxaSocialFeed\Domain\Repository\TokenRepository $tokenRepository,
        protected \Pixelant\PxaSocialFeed\Domain\Repository\FeedRepository $feedRepository
    ) {}

    protected function initializeView()
    {
        $this->pageRenderer->addCssFile('EXT:pxa_social_feed/Resources/Public/Css/Backend/SocialFeedModule.css');
        $this->pageRenderer->loadJavaScriptModule('@pixelant/pxa-social-feed/social-feed-administration-module.js');
    }

    protected function initializeAction(): void
    {
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $this->createMenu();
    }

    /**
     * Index action to show all configurations and tokens
     *
     * @param bool $activeTokenTab
     */
    public function indexAction($activeTokenTab = false): ResponseInterface
    {
        $tokens = $this->findAllByRepository($this->tokenRepository);
        $this->moduleTemplate->assignMultiple([
            'tokens' => $tokens,
            'configurations' => $this->findAllByRepository($this->configurationRepository),
            'activeTokenTab' => $activeTokenTab,
            'isTokensValid' => $this->isTokensValid($tokens),
            'isAdmin' => $GLOBALS['BE_USER']->isAdmin(),
        ]);

        return $this->moduleTemplate->renderResponse('Administration/Index');
    }

    /**
     * Edit token form
     *
     * @param Token|null $tokenToEdit
     */
    public function editTokenAction(Token $tokenToEdit = null, int $type = Token::FACEBOOK): ResponseInterface
    {
        $token = $tokenToEdit;
        $isNew = !$token instanceof \Pixelant\PxaSocialFeed\Domain\Model\Token;

        if (!$isNew) {
            $type = $token->getType();
        }

        $availableTypes = [];

        if ($isNew) {
            foreach (Token::getAvailableTokensTypes() as $availableTokensType) {
                $availableTypes[$availableTokensType] = $this->translate('type.' . $availableTokensType);
            }
        }

        $this->moduleTemplate->assignMultiple(['token' => $token, 'type' => $type, 'isNew' => $isNew, 'availableTypes' => $availableTypes]);
        $this->assignBEGroups();

        return $this->moduleTemplate->renderResponse('Administration/EditToken');
    }

    /**
     * Save token changes
     */
    #[Extbase\Validate(['validator' => TokenValidator::class, 'param' => 'tokenToEdit'])]
    public function updateTokenAction(Token $tokenToEdit): RedirectResponse
    {
        $isNew = $tokenToEdit->getUid() === null;

        $this->tokenRepository->{$isNew ? 'add' : 'update'}($tokenToEdit);

        $this->addFlashMessage(
            $this->translate('action_changes_saved'),
            '',
            ContextualFeedbackSeverity::INFO,
        );

        return new RedirectResponse($this->uriBuilder->reset()->uriFor('index', [], 'Administration', 'PxaSocialFeed') . '&activeTokenTab=1');
    }

    /**
     * Reset access token
     *
     * @param Token $token
     */
    public function resetAccessTokenAction(Token $resetToken): RedirectResponse
    {
        $resetToken->setAccessToken('');
        $this->tokenRepository->update($resetToken);

        $this->addFlashMessage(
            'Access token was reset',
            '',
            ContextualFeedbackSeverity::INFO,
        );

        return new RedirectResponse($this->uriBuilder->reset()->uriFor('index', [], 'Administration', 'PxaSocialFeed') . '&activeTokenTab=1');
    }

    /**
     * Delete token
     */
    public function deleteTokenAction(Token $tokenToDelete): RedirectResponse
    {
        $tokenConfigurations = $this->configurationRepository->findConfigurationByToken($tokenToDelete);

        if ($tokenConfigurations->count() === 0) {
            $this->tokenRepository->remove($tokenToDelete);

            if ($tokenToDelete->getType() === Token::FACEBOOK) {
                // Remove all page access tokens created by this token
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getConnectionForTable('tx_pxasocialfeed_domain_model_token');
                $queryBuilder->delete('tx_pxasocialfeed_domain_model_token', ['parent_token' => $tokenToDelete->getUid()]);
            }

            $this->addFlashMessage(
                $this->translate('action_delete'),
                '',
                ContextualFeedbackSeverity::INFO,
            );

            return new RedirectResponse($this->uriBuilder->reset()->uriFor('index', [], 'Administration', 'PxaSocialFeed') . '&activeTokenTab=1');
        }

        $this->addFlashMessage(
            $this->translate(
                'error_token_configuration_exist',
                [$tokenConfigurations->getFirst()->getName()],
            ),
            '',
            ContextualFeedbackSeverity::ERROR,
        );

        return new RedirectResponse($this->uriBuilder->reset()->uriFor('index', [], 'Administration', 'PxaSocialFeed') . '&activeTokenTab=1');
    }

    /**
     * Edit configuration
     */
    public function editConfigurationAction(Configuration $configuration = null): ResponseInterface
    {
        $tokens = $this->findAllByRepository($this->tokenRepository);

        $this->moduleTemplate->assignMultiple(['configuration' => $configuration, 'tokens' => $tokens]);
        $this->assignBEGroups();

        return $this->moduleTemplate->renderResponse('Administration/EditConfiguration');
    }

    /**
     * Update configuration
     */
    #[Extbase\Validate(['validator' => ConfigurationValidator::class, 'param' => 'configuration'])]
    public function updateConfigurationAction(Configuration $configuration): RedirectResponse
    {
        $isNew = $configuration->getUid() === null;

        // If storage was updated and it's not new configuration, need to migrate existing feed records
        if ($isNew == false && $configuration->_isDirty('storage')) {
            $this->migrateFeedsToNewStorage($configuration, $configuration->getStorage());
        }

        $this->configurationRepository->{$isNew ? 'add' : 'update'}($configuration);

        if ($isNew) {
            // Save first, so we can pass it as argument
            GeneralUtility::makeInstance(PersistenceManagerInterface::class)->persistAll();

            // Redirect back to edit view, so user can now provide social ID according to selected token
            return new RedirectResponse($this->uriBuilder->reset()->uriFor('editConfiguration', ['configuration' => $configuration], 'Administration', 'PxaSocialFeed'));
        }

        $this->addFlashMessage(
            $this->translate('action_changes_saved'),
            '',
            ContextualFeedbackSeverity::OK,
        );

        return new RedirectResponse($this->uriBuilder->reset()->uriFor('index', [], 'Administration', 'PxaSocialFeed'));
    }

    /**
     * Delete configuration and feed items
     */
    public function deleteConfigurationAction(Configuration $configuration): RedirectResponse
    {
        // Remove all feeds
        $feeds = $this->feedRepository->findBy(['configuration' => $configuration]);

        foreach ($feeds as $feed) {
            $this->feedRepository->remove($feed);
        }

        $this->configurationRepository->remove($configuration);

        $this->addFlashMessage(
            $this->translate('action_delete'),
            '',
            ContextualFeedbackSeverity::WARNING,
        );

        return new RedirectResponse($this->uriBuilder->reset()->uriFor('index', [], 'Administration', 'PxaSocialFeed'));
    }

    /**
     * Test run of import configuration
     */
    public function runConfigurationAction(Configuration $configuration): RedirectResponse
    {
        $importService = GeneralUtility::makeInstance(ImportFeedsTaskService::class);
        try {
            $importService->import([$configuration->getUid()]);
        } catch (\Exception $exception) {
            $this->addFlashMessage(
                $exception->getMessage(),
                '',
                ContextualFeedbackSeverity::ERROR,
            );
        }

        $this->addFlashMessage(
            $this->translate('single_import_end'),
            '',
            ContextualFeedbackSeverity::WARNING,
        );

        return new RedirectResponse($this->uriBuilder->reset()->uriFor('index', [], 'Administration', 'PxaSocialFeed'));
    }

    /**
     * Check if editor restriction feature is enabled
     * If so find all with backend group access restriction
     */
    protected function findAllByRepository(AbstractBackendRepository $repository): QueryResultInterface
    {
        return ConfigurationUtility::isFeatureEnabled('editorRestriction')
            ? $repository->findAllBackendGroupRestriction()
            : $repository->findAll();
    }

    /**
     * Assign BE groups to template
     * If admin all are available
     */
    protected function assignBEGroups()
    {
        if (!ConfigurationUtility::isFeatureEnabled('editorRestriction')) {
            return;
        }

        $excludeGroups = $this->getExcludeGroups();

        if ($GLOBALS['BE_USER']->isAdmin()) {
            $groups = $this->backendUserGroupRepository->findAll($excludeGroups);
        } else {
            $groups = array_filter($GLOBALS['BE_USER']->userGroups, fn($group): bool => !in_array($group['uid'], $excludeGroups));
        }

        $this->view->assign('beGroups', $groups);
    }

    /**
     * Shortcut for translate
     *
     * @param array|null $arguments
     * @return string
     */
    protected function translate(string $key, array $arguments = null): ?string
    {
        $key = 'module.' . $key;

        return LocalizationUtility::translate($key, 'PxaSocialFeed', $arguments);
    }

    /**
     * create BE menu
     */
    protected function createMenu(): void
    {
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $uriBuilder->setRequest($this->request);

        $menu = $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier('pxa_social_feed');

        $actions = ['index', 'editConfiguration', 'editToken'];

        foreach ($actions as $action) {
            $item = $menu->makeMenuItem()
                ->setTitle($this->translate($action . 'Action'))
                ->setHref($uriBuilder->reset()->uriFor($action, [], 'Administration'))
                ->setActive($this->request->getControllerActionName() === $action);
            $menu->addMenuItem($item);
        }

        $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
    }

    /**
     * Migrate feed items of configuration if storoge was changed
     */
    protected function migrateFeedsToNewStorage(Configuration $configuration, int $newStorage): void
    {
        $feedItems = $this->feedRepository->findBy(['configuration' => $configuration]);

        /** @var Feed $feedItem */
        foreach ($feedItems as $feedItem) {
            $feedItem->setPid($newStorage);
            $this->feedRepository->update($feedItem);
        }
    }

    /**
     * Check if instagram and facebook tokens has access token
     *
     * @param $tokens
     */
    protected function isTokensValid($tokens): bool
    {
        /** @var Token $token */
        foreach ($tokens as $token) {
            if (($token->getType() === Token::INSTAGRAM || $token->getType() === Token::FACEBOOK) && !$token->isValidFacebookAccessToken()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generate settings for JS
     */
    protected function getInlineSettings(): string
    {
        $uriBuilder = GeneralUtility::makeInstance(BackendUriBuilder::class);

        return json_encode([
            'browserUrl' => (string)$uriBuilder->buildUriFromRoute('wizard_element_browser'),
        ]);
    }

    /**
     * Shortcut to redirect to index on tokens tab with flash message
     *
     * @param string|null $message
     */
    protected function redirectToIndexTokenTab(string $message = null, ContextualFeedbackSeverity $severity = ContextualFeedbackSeverity::OK): RedirectResponse
    {
        if ($message !== null && $message !== '' && $message !== '0') {
            $this->addFlashMessage(
                $message,
                '',
                $severity
            );
        }

        return new RedirectResponse($this->uriBuilder->reset()->uriFor('index', [], 'Administration', 'PxaSocialFeed') . '&activeTokenTab=1');
    }

    /**
     * Shortcut to redirect to index with flash message
     *
     * @param string|null $message
     */
    protected function redirectToIndex(string $message = null, ContextualFeedbackSeverity $severity = ContextualFeedbackSeverity::OK): RedirectResponse
    {
        if ($message !== null && $message !== '' && $message !== '0') {
            $this->addFlashMessage(
                $message,
                '',
                $severity
            );
        }

        return new RedirectResponse($this->uriBuilder->reset()->uriFor('index', [], 'Administration', 'PxaSocialFeed'));
    }

    /**
     * Return exclude user group uids from ext configuration
     */
    protected function getExcludeGroups(): array
    {
        $configuration = ConfigurationUtility::getExtensionConfiguration();
        if (isset($configuration['excludeBackendUserGroups'])) {
            return GeneralUtility::intExplode(',', $configuration['excludeBackendUserGroups'], true);
        }

        return [];
    }
}
