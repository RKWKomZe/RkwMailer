<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function(string $extKey)
    {
        //=================================================================
        // Configure Plugin
        //=================================================================
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'Rkwmailer',
            array(
                'Link' => 'redirect, confirmation', // deprecated
                'Tracking' => 'redirect, opening',
            ),
            // non-cacheable actions
            array(
                'Link' => 'redirect, confirmation', // deprecated
                'Tracking' => 'redirect, opening',
            )
        );

        //=================================================================
        // Register CommandController
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'RKW\\RkwMailer\\Controller\\MailerCommandController';

        //=================================================================
        // Register Caching
        //=================================================================
        if( !is_array($GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$extKey] ) ) {
            $GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$extKey] = array();
        }
        if( !isset($GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$extKey]['frontend'] ) ) {
            $GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$extKey]['frontend'] = 'TYPO3\\CMS\\Core\\Cache\\Frontend\\VariableFrontend';
        }
        if( !isset($GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$extKey]['backend'] ) ) {
            $GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$extKey]['backend'] = 'TYPO3\\CMS\\Core\\Cache\\Backend\\SimpleFileBackend';
        }

        //=================================================================
        // Register xClass
        //=================================================================
        /*$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager::class] = [
            'className' => RKW\RkwMailer\Xclass\BackendConfigurationManager::class
        ];*/

        //=================================================================
        // Register Logger
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['LOG']['RKW']['RkwMailer']['writerConfiguration'] = array(

            // configuration for WARNING severity, including all
            // levels with higher severity (ERROR, CRITICAL, EMERGENCY)
            \TYPO3\CMS\Core\Log\LogLevel::DEBUG => array(
                // add a FileWriter
                'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => array(
                    // configuration for the writer
                    'logFile' => 'typo3temp/var/logs/tx_rkwmailer.log'
                )
            ),
        );
        
        //=================================================================
        // register update wizard
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update'][\RKW\RkwMailer\Updates\MigrateStatisticsWizard::class] = \RKW\RkwMailer\Updates\MigrateStatisticsWizard::class;
    },
    'rkw_mailer'
);

