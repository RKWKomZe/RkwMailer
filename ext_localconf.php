<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'RKW.' . $_EXTKEY,
	'Rkwmailer',
	array(
		'Link' => 'redirect, confirmation',
	),
	// non-cacheable actions
	array(
		'Link' => 'redirect, confirmation',
	)
);

// register command controller (cronjob)
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'RKW\\RkwMailer\\Controller\\MailerCommandController';

// set logger
$GLOBALS['TYPO3_CONF_VARS']['LOG']['RKW']['RkwMailer']['writerConfiguration'] = array(

	// configuration for WARNING severity, including all
	// levels with higher severity (ERROR, CRITICAL, EMERGENCY)
	\TYPO3\CMS\Core\Log\LogLevel::DEBUG => array(
		// add a FileWriter
		'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => array(
			// configuration for the writer
			'logFile' => 'typo3temp/logs/tx_rkwmailer.log'
		)
	),
);