<?php
return [
	'ctrl' => [
		'title'	=> 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_bouncemail',
		'label' => 'uid',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'dividers2tabs' => true,
		'hideTable' => true,

		'searchFields' => 'hash, url',
		'iconfile' => 'EXT:rkw_mailer/Resources/Public/Icons/tx_rkwmailer_domain_model_bouncemail.gif'
	],
	'interface' => [
		'showRecordFieldList' => 'status, type, email, subject, rule_number, rule_category, header, body, header_full, body_full',
	],
	'types' => [
		'1' => ['showitem' => 'status, type, email, subject, rule_number, rule_category, header, body, header_full, body_full'],
	],
	'palettes' => [
		'1' => ['showitem' => ''],
	],
	'columns' => [

        'status' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_bouncemail.status',
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
                    ['LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_bouncemail.status.I.new', 0],
                    ['LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_bouncemail.status.I.processed', 100],
                ],
            ],
        ],

		'type' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_bouncemail.type',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			],
		],

        'email' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_bouncemail.email',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'subject' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_bouncemail.subject',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'rule_number' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_bouncemail.rule_number',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],

        'rule_category' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_bouncemail.rule_category',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'header' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_bouncemail.header',
            'config' => [
                'type' => 'text',
                'rows' => 40,
            ],
        ],
        'body' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_bouncemail.body',
            'config' => [
                'type' => 'text',
                'rows' => 40,
            ],
        ],
        'header_full' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_bouncemail.header_full',
            'config' => [
                'type' => 'text',
                'rows' => 40,
            ],
        ],
        'body_full' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_bouncemail.body_full',
            'config' => [
                'type' => 'text',
                'rows' => 40,
            ],
        ],
	],
];
