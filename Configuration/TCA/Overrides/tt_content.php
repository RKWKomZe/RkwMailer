<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function () {

        //=================================================================
        // Register Plugin
        //=================================================================
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'RKW.RkwMailer',
            'Rkwmailer',
            'RKW Mailer Tracking'
        );
    }
);