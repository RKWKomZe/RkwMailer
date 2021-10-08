<?php
return [
	'ctrl' => [
		'title'	=> 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_openingstatistics',
		'label' => 'uid',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'dividers2tabs' => true,
		'hideTable' => true,

		'searchFields' => 'hash',
		'iconfile' => 'EXT:rkw_mailer/Resources/Public/Icons/tx_rkwmailer_domain_model_openingstatistics.gif'
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
        
		'hash' => [
			'config' => [
                'type' => 'passthrough',
            ],
		],
        'counter' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'queue_mail' => [
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_rkwmailer_domain_model_queuemail',
                'maxitems' => 1
            ],
        ],
        'queue_recipient' => [
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_rkwmailer_domain_model_queuerecipient',
                'maxitems' => 1
            ],
        ]        
	],
];
