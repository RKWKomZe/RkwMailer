<?php
return [
	'ctrl' => [
		'title'	=> 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_statisticopening',
		'label' => 'mail_id',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'dividers2tabs' => true,
		'hideTable' => true,

		'searchFields' => 'pixel, click_count',
		'iconfile' => 'EXT:rkw_mailer/Resources/Public/Icons/tx_rkwmailer_domain_model_statisticopening.gif'
	],
	'interface' => [
		'showRecordFieldList' => 'pixel, click_count',
	],
	'types' => [
		'1' => ['showitem' => 'pixel, click_count'],
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
		'queue_recipient' => [
            'config' => [
                'type' => 'passthrough',
                'foreign_table' => 'tx_rkwmailer_domain_model_queuerecipient',
            ],
		],
        'link' => [
            'config' => [
                'type' => 'passthrough',
                'foreign_table' => 'tx_rkwmailer_domain_model_link',
            ],
        ],
		'pixel' => [
			'exclude' => 1,
			'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_statisticopening.pixel',
			'config' => [
				'type' => 'input',
				'size' => 4,
				'eval' => 'int'
			],
		],

		'click_count' => [
			'exclude' => 1,
			'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_statisticopening.click_count',
			'config' => [
				'type' => 'input',
				'size' => 4,
				'eval' => 'int'
			],
		],
	],
];
