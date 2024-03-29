<?php
return [
	'ctrl' => [
		'title'	=> 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_queuemail',
		'label' => 'uid',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'dividers2tabs' => true,
		'hideTable' => true,

		'searchFields' => 'from_name, from_address, subject',
		'iconfile' => 'EXT:rkw_mailer/Resources/Public/Icons/tx_rkwmailer_domain_model_queuemail.gif'
	],
    'interface' => [
        'showRecordFieldList' => '',
    ],
    'types' => [
        '1' => ['showitem' => ''],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
	'columns' => [

        'status' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],

        'type' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'pipeline' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],

		'from_name' => [
            'config' => [
                'type' => 'passthrough',
            ],
		],
		'from_address' => [
            'config' => [
                'type' => 'passthrough',
            ],
		],
        'reply_to_name' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
		'reply_to_address' => [
            'config' => [
                'type' => 'passthrough',
            ],
		],
		'return_path' => [
            'config' => [
                'type' => 'passthrough',
            ],
		],
		'subject' => [
            'config' => [
                'type' => 'passthrough',
            ],
		],
        'body_text' => [
            'config' => [
                'type' => 'passthrough',
            ],
		],
        'attachment_paths' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'attachment' => [
            'config' => [
                'type' => 'passthrough',
            ],
		],
        'attachment_type' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'attachment_name' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'plaintext_template' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'html_template' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'calendar_template' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
		'layout_paths' => [
            'config' => [
                'type' => 'passthrough',
            ],
		],
		'partial_paths' => [
            'config' => [
                'type' => 'passthrough',
            ],
		],
        'template_paths' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'category' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'campaign_parameter' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
		'priority' => [
            'config' => [
                'type' => 'passthrough',
            ],
		],
        'settings_pid' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
		'tstamp_fav_sending' => [
            'config' => [
                'type' => 'passthrough',
            ],
		],
		'tstamp_real_sending' => [
            'config' => [
                'type' => 'passthrough',
            ],
		],
		'tstamp_send_finish' => [
            'config' => [
                'type' => 'passthrough',
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
        'mailing_statistics' => [
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_rkwmailer_domain_model_mailingstatistics',
                'foreign_field' => 'queue_mail',
                'maxitems' => 1
            ],
        ]
    ],
];
