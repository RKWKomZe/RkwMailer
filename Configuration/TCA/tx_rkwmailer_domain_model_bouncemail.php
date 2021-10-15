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
            'config' => [
                'type' => 'passthrough',
            ],
        ],

		'type' => [
            'config' => [
                'type' => 'passthrough',
            ],
		],

        'email' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'subject' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'rule_number' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],

        'rule_category' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'header' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'body' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'header_full' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'body_full' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
	],
];
