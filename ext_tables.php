<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

if (TYPO3_MODE === 'BE') {

	/**
	 * Registers a Backend Module
	 */
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		'RKW.' . $_EXTKEY,
		'tools',	 // Make module a submodule of 'Web'
		'mailadministration',	// Submodule key
		'',						// Position
		array(
			'Backend' => 'statistics, clickStatistics, list, pause, continue, delete, reset',
		),
		array(
			'access' => 'user,group',
			'icon'   => 'EXT:' . $_EXTKEY . '/ext_icon.gif',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_backend.xlf',
		)
	);

}


\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'RKW.' . $_EXTKEY,
	'Rkwmailer',
	'RKW Mailer'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'RKW Mailer');


//=================================================================
// Add tables
//=================================================================
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_rkwmailer_domain_model_link', 'EXT:rkw_mailer/Resources/Private/Language/locallang_csh_tx_rkwmailer_domain_model_link.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_rkwmailer_domain_model_link');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_rkwmailer_domain_model_link', 'EXT:rkw_mailer/Resources/Private/Language/locallang_csh_tx_rkwmailer_domain_model_link.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_rkwmailer_domain_model_link');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_rkwmailer_domain_model_queuemail', 'EXT:rkw_mailer/Resources/Private/Language/locallang_csh_tx_rkwmailer_domain_model_queuemail.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_rkwmailer_domain_model_queuemail');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_rkwmailer_domain_model_queuerecipient', 'EXT:rkw_mailer/Resources/Private/Language/locallang_csh_tx_rkwmailer_domain_model_queuerecipient.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_rkwmailer_domain_model_queuerecipient');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_rkwmailer_domain_model_statisticopening', 'EXT:rkw_mailer/Resources/Private/Language/locallang_csh_tx_rkwmailer_domain_model_statisticopening.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_rkwmailer_domain_model_statisticopening');
