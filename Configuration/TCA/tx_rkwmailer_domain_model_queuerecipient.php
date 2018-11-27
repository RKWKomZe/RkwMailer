<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_rkwmailer_domain_model_queuerecipient', 'EXT:rkw_mailer/Resources/Private/Language/locallang_csh_tx_rkwmailer_domain_model_queuerecipient.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_rkwmailer_domain_model_queuerecipient');
$GLOBALS['TCA']['tx_rkwmailer_domain_model_queuerecipient'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient',
		'label' => 'email',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => true,
		'hideTable' => true,

		'searchFields' => 'frontend_user, email, first_name, last_name, subject, status, language_code, plaintext_body, html_body, calendar_body',
		'iconfile' => 'EXT:rkw_mailer/Resources/Public/Icons/tx_rkwmailer_domain_model_queuerecipient.gif'
	),
	'interface' => array(
		'showRecordFieldList' => 'frontend_user, email, first_name, last_name, subject, status, language_code, plaintext_body, html_body, calendar_body',
	),
	'types' => array(
		'1' => array('showitem' => 'frontend_user, email, first_name, last_name, subject, status, language_code, plaintext_body, html_body, calendar_body'),
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
        'statistic_openings' => array(
            'exclude' => 0,
            'config' => array(
                'type' => 'passthrough',
                'foreign_table' => 'tx_rkwmailer_domain_model_statisticopening',
                'foreign_field' => 'queue_recipient'
            )
        ),
        'frontend_user' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.frontend_user',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'fe_users',
                'minitems' => 0,
                'maxitems' => 1,
                'readOnly' => 1
            ),
        ),
		'email' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.email',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,email'
			),
		),
        'title' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.title',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ),
        ),
        'salutation' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.salutation',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
                'default' => 99,
                'items' => array(
                    array('LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.salutation.I.man', '0'),
                    array('LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.salutation.I.woman', '1'),
                    array('LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.salutation.I.neutral', '99'),

                )
            ),
        ),
		'first_name' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.first_name',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'last_name' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.last_name',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
        'subject' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.subject',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ),
        ),
        'marker' => array(
            'config' => array(
                'type' => 'passthrough',
            ),
        ),
        'status' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.status',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'size' => 1,
                'eval' => 'int',
                'minitems' => 0,
                'maxitems' => 1,
                'default' => 2,
                'readOnly' => 1,
                'items' => array(
                    array('LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.status.I.draft', 1),
                    array('LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.status.I.waiting', 2),
                    array('LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.status.I.sending', 3),
                    array('LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.status.I.sent', 4),
                    array('LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.status.I.error', 99),
                ),
            )
        ),
        'language_code' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.language_code',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ),
        ),

        'plaintext_body' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.plaintext_body',
            'config' => array(
                'type' => 'text',
                'rows' => 40,
            ),
        ),
        'html_body' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.html_body',
            'config' => array(
                'type' => 'text',
                'rows' => 40,
            ),
        ),
        'calendar_body' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.calendar_body',
            'config' => array(
                'type' => 'text',
                'rows' => 40,
            ),
        ),

	),
);
