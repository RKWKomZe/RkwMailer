<?php
return [
	'ctrl' => [
		'title'	=> 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail',
		'label' => 'subject',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'dividers2tabs' => true,
		'hideTable' => true,

		'sortby' => 'sorting',
		'searchFields' => 'status, type, pipeline, queue_recipients, from_name, from_address, reply_address, return_path, subject, body_text, settings_pid, campaign_parameter priority, plaintext_template, html_template, calendar_template, tstamp_fav_sending, tstamp_real_sending, tstamp_send_finish',
		'iconfile' => 'EXT:rkw_mailer/Resources/Public/Icons/tx_rkwmailer_domain_model_queuemail.gif'
	],
	'interface' => [
		'showRecordFieldList' => 'status, type, pipeline, queue_recipients, from_name, from_address, reply_address, return_path, subject, body_text, settings_pid, campaign_parameter priority, plaintext_template, html_template, calendar_template, tstamp_fav_sending, tstamp_real_sending, tstamp_send_finish',
	],
	'types' => [
		'1' => ['showitem' => 'subject, body_text, queue_recipients, --div--;LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.tabs.from, from_name, from_address, reply_address, return_path, --div--;LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.tabs.settings, settings_pid, plaintext_template, html_template, calendar_template, campaign_parameter, priority, --div--;LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.tabs.sending, tstamp_fav_sending, tstamp_real_sending, tstamp_send_finish, status, type, pipeline'],
	],
	'palettes' => [
		'1' => ['showitem' => ''],
	],
	'columns' => [

        'status' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.status',
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
                    ['LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.status.I.draft', 1],
                    ['LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.status.I.waiting', 2],
                    ['LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.status.I.sending', 3],
                    ['LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.status.I.sent', 4],
                    ['LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.status.I.error', 99],
                ],
            ],
        ],

        'type' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'size' => 1,
                'eval' => 'int',
                'minitems' => 0,
                'maxitems' => 1,
                'default' => 0,
                'readOnly' => 1,
                'items' => [
                    ['LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.type.I.0', 0],
                    ['LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.type.I.1', 1],
                    ['LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.type.I.2', 2],
                ],
            ],

        ],
        'pipeline' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.pipeline',
            'config' => [
                'type' => 'check',
            ],
        ],

		'from_name' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.from_name',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			],
		],
		'from_address' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.from_address',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,email'
			],
		],
		'reply_address' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.reply_address',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,email'
			],
		],
		'return_path' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.return_path',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,email'
			],
		],
		'subject' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.subject',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			],
		],
        'body_text' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.body_text',
			'config' => [
				'type' => 'text',
                'rows' => 40,
			],
		],
        'attachment' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.attachment',
			'config' => [
                'type' => 'passthrough',
			],
		],
        'attachment_type' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.attachment_type',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'attachment_name' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.attachment_name',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'plaintext_template' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.plaintext_template',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'html_template' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.html_template',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'calendar_template' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.calendar_template',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
		'layout_paths' => [
			'exclude' => 0,
			'config' => [
				'type' => 'passthrough',
			],
		],
		'partial_paths' => [
			'exclude' => 0,
			'config' => [
				'type' => 'passthrough',
			],
		],
        'template_paths' => [
            'exclude' => 0,
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'category' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.category',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'campaign_parameter' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.campaign_parameter',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
		'priority' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.priority',
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
                    ['LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.priority.I.highest', 1],
                    ['LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.priority.I.high', 2],
                    ['LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.priority.I.default', 3],
                    ['LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.priority.I.low', 4],
                    ['LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.priority.I.lowest', 5],
                ],
			],
		],
        'settings_pid' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.settings_pid',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'max' => '256',
                'eval' => 'int,required',
            ],
        ],
		'tstamp_fav_sending' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.tstamp_fav_sending',
			'config' => [
				'type' => 'input',
                'renderType' => 'inputDateTime',
				'size' => 13,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => [
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y')),
				],
			],
		],
		'tstamp_real_sending' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.tstamp_real_sending',
			'config' => [
				'type' => 'input',
                'renderType' => 'inputDateTime',
				'size' => 13,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
                'readOnly' =>1,
				'range' => [
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y')),
				],
			],
		],
		'tstamp_send_finish' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail.tstamp_send_finish',
			'config' => [
				'type' => 'input',
                'renderType' => 'inputDateTime',
				'size' => 13,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
                'readOnly' =>1,
				'range' => [
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y')),
				],
			],
		],
        'total' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'sent' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'successful' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'failed' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'deferred' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'bounced' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'opened' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'clicked' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
    ],
];
