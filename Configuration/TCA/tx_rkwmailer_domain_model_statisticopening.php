<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_rkwmailer_domain_model_statisticopening', 'EXT:rkw_mailer/Resources/Private/Language/locallang_csh_tx_rkwmailer_domain_model_statisticopening.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_rkwmailer_domain_model_statisticopening');
$GLOBALS['TCA']['tx_rkwmailer_domain_model_statisticopening'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_statisticopening',
		'label' => 'mail_id',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'dividers2tabs' => true,
		'hideTable' => true,

		'searchFields' => 'pixel, click_count',
		'iconfile' => 'EXT:rkw_mailer/Resources/Public/Icons/tx_rkwmailer_domain_model_statisticopening.gif'
	),
	'interface' => array(
		'showRecordFieldList' => 'pixel, click_count',
	),
	'types' => array(
		'1' => array('showitem' => 'pixel, click_count'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(

        'queue_mail' => array(
            'config' => array(
                'type' => 'passthrough',
                'foreign_table' => 'tx_rkwmailer_domain_model_queuemail',
            ),
        ),
		'queue_recipient' => array(
            'config' => array(
                'type' => 'passthrough',
                'foreign_table' => 'tx_rkwmailer_domain_model_queuerecipient',
            ),
		),
        'link' => array(
            'config' => array(
                'type' => 'passthrough',
                'foreign_table' => 'tx_rkwmailer_domain_model_link',
            ),
        ),
		'pixel' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_statisticopening.pixel',
			'config' => array(
				'type' => 'input',
				'size' => 4,
				'eval' => 'int'
			),
		),

		'click_count' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_statisticopening.click_count',
			'config' => array(
				'type' => 'input',
				'size' => 4,
				'eval' => 'int'
			)
		),
	),
);
