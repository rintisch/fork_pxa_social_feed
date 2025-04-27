<?php

defined('TYPO3') || die();

(function (): void {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'PxaSocialFeed',
        'Showfeed',
        [
            \Pixelant\PxaSocialFeed\Controller\FeedsController::class => 'list, loadFeedAjax, listAjax',
        ],
        // non-cacheable actions
        [
            \Pixelant\PxaSocialFeed\Controller\FeedsController::class => 'list, loadFeedAjax',
        ],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    $ll = 'LLL:EXT:pxa_social_feed/Resources/Private/Language/locallang_be.xlf:';

    // @codingStandardsIgnoreStart
    // Import task
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Pixelant\PxaSocialFeed\Task\ImportTask::class] = [
        'extension'        => 'pxa_social_feed',
        'title' => $ll . 'task.import.name',
        'description' => $ll . 'task.import.description',
        'additionalFields' => \Pixelant\PxaSocialFeed\Task\ImportTaskAdditionalFieldProvider::class,
    ];

    // hook for extension BE view
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['pxasocialfeed_showfeed']['pxa_social_feed'] =
        \Pixelant\PxaSocialFeed\Hooks\PageLayoutView::class . '->getExtensionInformation';

    // Register eID to obtain access token
    $eID = \Pixelant\PxaSocialFeed\Controller\EidController::IDENTIFIER;
    $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include'][$eID] =
        \Pixelant\PxaSocialFeed\Controller\EidController::class . '::addFbAccessTokenAction';
})();
