<?php
return [
	'ctrl' => [
		'title'	=> 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_link',
		'label' => 'uid',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'dividers2tabs' => true,
		'hideTable' => true,

		'searchFields' => 'hash, url',
		'iconfile' => 'EXT:rkw_mailer/Resources/Public/Icons/tx_rkwmailer_domain_model_link.gif'
	],
	'interface' => [
		'showRecordFieldList' => 'hash, url',
	],
	'types' => [
		'1' => ['showitem' => 'hash, url'],
	],
	'palettes' => [
		'1' => ['showitem' => ''],
	],
	'columns' => [

		
		'hash' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_link.hash',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			],
		],
		'url' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_mailer/Resources/Private/Language/locallang_db.xlf:tx_rkwmailer_domain_model_link.url',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			],
		],

        'queue_mail' => [
            'config' => [
                'type' => 'passthrough',
                'foreign_table' => 'tx_rkwmailer_domain_model_queuemail',
            ],
        ],
	],
];
