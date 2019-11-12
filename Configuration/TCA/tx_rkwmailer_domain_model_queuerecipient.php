<?php
return [
	'ctrl' => [
		'title'	=> 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient',
		'label' => 'email',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => true,
		'hideTable' => true,

		'searchFields' => 'frontend_user, email, first_name, last_name, subject, status, language_code, plaintext_body, html_body, calendar_body',
		'iconfile' => 'EXT:rkw_mailer/Resources/Public/Icons/tx_rkwmailer_domain_model_queuerecipient.gif'
	],
	'interface' => [
		'showRecordFieldList' => 'frontend_user, email, first_name, last_name, subject, status, language_code, plaintext_body, html_body, calendar_body',
	],
	'types' => [
		'1' => ['showitem' => 'frontend_user, email, first_name, last_name, subject, status, language_code, plaintext_body, html_body, calendar_body'],
	],
	'palettes' => [
		'1' => ['showitem' => ''],
	],
	'columns' => [

        'queue_mail' => [
            'config' => [
                'type' => 'passthrough',
                'foreign_table' => 'tx_rkwmailer_domain_model_queuemail',
            ],
        ],

        'frontend_user' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.frontend_user',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'fe_users',
                'minitems' => 0,
                'maxitems' => 1,
                'readOnly' => 1
            ],
        ],
		'email' => [
			'exclude' => 1,
			'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.email',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,email'
			],
		],
        'title' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'salutation' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.salutation',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
                'default' => 99,
                'items' => [
                    ['LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.salutation.I.man', '0'],
                    ['LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.salutation.I.woman', '1'],
                    ['LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.salutation.I.neutral', '99'],

                ],
            ],
        ],
		'first_name' => [
			'exclude' => 1,
			'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.first_name',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			],
		],
		'last_name' => [
			'exclude' => 1,
			'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.last_name',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			],
		],
        'subject' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.subject',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'marker' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'status' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.status',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'size' => 1,
                'eval' => 'int',
                'minitems' => 0,
                'maxitems' => 1,
                'default' => 2,
                'readOnly' => 1,
                'items' => [
                    ['LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.status.I.draft', 1],
                    ['LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.status.I.waiting', 2],
                    ['LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.status.I.sending', 3],
                    ['LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.status.I.sent', 4],
                    ['LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.status.I.deferred', 97],
                    ['LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.status.I.bounce', 98],
                    ['LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.status.I.error', 99],
                ],
            ],
        ],
        'language_code' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuerecipient.language_code',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
	],
];
