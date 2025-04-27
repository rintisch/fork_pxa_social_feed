<?php

defined('TYPO3') || die('Access denied.');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'pxa_social_feed',
    'Showfeed',
    'Pxa Social Feed'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', '--div--;Configuration,pi_flexform,', 'pxasocialfeed_showfeed', 'after:subheader');
// @codingStandardsIgnoreEnd

// Add flexform
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:pxa_social_feed/Configuration/FlexForm/SocialFeed.xml',
    'pxasocialfeed_showfeed'
);
