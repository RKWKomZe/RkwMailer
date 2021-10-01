<?php
namespace RKW\RkwMailer\Updates;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use RKW\RkwMailer\Utility\StatisticsUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;

/**
 * Class UpdateMigrateStatistics
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwTemplates
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */

class MigrateStatisticsWizard extends \RKW\RkwBasics\Updates\AbstractUpdate
{


    /**
     * @var string
     */
    protected $extensionKey = 'rkwMailer';


    /**
     * @var string
     */
    protected $title = 'Migrate statistics to new version. Maybe this wizard has to be executed multiple times until all data is migrated. When the migration was successful, the wizward is marked as done automatically.';


    /**
     * Checks whether updates are required.
     *
     * @param string $description The description for the update
     * @return bool Whether an update is required (TRUE) or not (FALSE)
     */
    public function checkForUpdate(&$description)
    {

        $currentVersion = VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
        if ($currentVersion < 8000000) {
            return false;
        }

        if ($this->isWizardDone()) {
            return false;
        }

        return true;
    }

    
    /**
     * Performs the required update.
     *
     * @param array $databaseQueries Queries done in this update
     * @param string $customMessage Custom message to be displayed after the update process finished
     * @return bool Whether everything went smoothly or not
     */
    public function performUpdate(array &$databaseQueries, &$customMessage)
    {
        
        // flag for wizard
        $migrationFinished = true;

        /** @var  \TYPO3\CMS\Core\Database\Connection $connectionStatisticOpening */
        $connectionStatisticOpening = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_rkwmailer_domain_model_statisticopening');
        
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilderStatisticOpening */
        $queryBuilderStatisticOpening = $connectionStatisticOpening->createQueryBuilder();
        $queryBuilderStatisticOpening->getRestrictions()
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class)
            ->removeByType(HiddenRestriction::class)
            ->removeByType(DeletedRestriction::class);

        /** @var  \TYPO3\CMS\Core\Database\Connection $connectionLinks */
        $connectionLinks = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_rkwmailer_domain_model_link');

        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilderLinks */
        $queryBuilderLinks = $connectionLinks->createQueryBuilder();
        $queryBuilderLinks->getRestrictions()
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class)
            ->removeByType(HiddenRestriction::class)
            ->removeByType(DeletedRestriction::class);

        /** @var  \TYPO3\CMS\Core\Database\Connection $connectionClickStatistics */
        $connectionClickStatistics = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_rkwmailer_domain_model_clickstatistics');

        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilderClickStatistics */
        $queryBuilderClickStatistics = $connectionClickStatistics->createQueryBuilder();
        $queryBuilderClickStatistics->getRestrictions()
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class)
            ->removeByType(HiddenRestriction::class)
            ->removeByType(DeletedRestriction::class);

        /** @var  \TYPO3\CMS\Core\Database\Connection $connectionOpeningStatistics */
        $connectionOpeningStatistics = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_rkwmailer_domain_model_openingstatistics');

        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilderOpeningStatistics */
        $queryBuilderOpeningStatistics = $connectionOpeningStatistics->createQueryBuilder();
        $queryBuilderOpeningStatistics->getRestrictions()
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class)
            ->removeByType(HiddenRestriction::class)
            ->removeByType(DeletedRestriction::class);        
        
        // ============================================================
        // First we migrate the links
        // ============================================================
        $statement = $queryBuilderStatisticOpening->select('*')
            ->from('tx_rkwmailer_domain_model_statisticopening')
            ->where(
                $queryBuilderStatisticOpening->expr()->gt('link',0),
                $queryBuilderStatisticOpening->expr()->gt('queue_mail',0),
                $queryBuilderStatisticOpening->expr()->gt('click_count',0),
                $queryBuilderStatisticOpening->expr()->lt('migrated',1)
            )
            ->setMaxResults(10000)
            ->execute();
        
        // go through all opened links
        while ($openedLink = $statement->fetch()) {

            // if there are still some entries left, set flag accordingly
            $migrationFinished = false;
            
            // and get link data
            $statementTwo = $queryBuilderLinks->select('*')
                ->from('tx_rkwmailer_domain_model_link')
                ->where(
                    $queryBuilderLinks->expr()->eq('uid',
                        $queryBuilderLinks->createNamedParameter($openedLink['link'], \PDO::PARAM_INT)
                    )
                )
                ->execute();

            // get link data
            while ($link = $statementTwo->fetch()) {

                $newLink = [
                    'pid' => $link['pid'],
                    'url' => $link['url'],
                    'link_hash' => $link['hash'],
                    'counter' => $openedLink['click_count'],
                    'queue_mail' => $openedLink['queue_mail'],
                    'tstamp' => $openedLink['tstamp'],
                    'crdate' => $openedLink['crdate'],
                    'hash' => StatisticsUtility::generateLinkHash($link['url']),
                    'comment' => 'Migrated from link-uid=' . $link['uid'] . ' and statisticOpening-uid=' . $openedLink['uid']
                ];
                
                // check if dataset already exists because there may be multiple entries in statisticopening-table
                // for the same queueMail-Link-combination
                $existingClickStatistics = $queryBuilderClickStatistics
                    ->select('*')
                    ->from('tx_rkwmailer_domain_model_clickstatistics')
                    ->where(
                        $queryBuilderClickStatistics->expr()->eq('hash',
                            $queryBuilderClickStatistics->createNamedParameter($newLink['hash'], \PDO::PARAM_STR)
                        ),
                        $queryBuilderClickStatistics->expr()->eq('queue_mail',
                            $queryBuilderClickStatistics->createNamedParameter($newLink['queue_mail'], \PDO::PARAM_INT)
                        )                    
                    )
                    ->execute()
                    ->fetch();
                
                // update existing dataset
                if ($existingClickStatistics) {
                    
                    /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $updateQueryBuilder */
                    $updateQueryBuilder = $connectionClickStatistics->createQueryBuilder();
                    $updateQueryBuilder->update('tx_rkwmailer_domain_model_clickstatistics')
                        ->set('counter', $existingClickStatistics['counter'] + $openedLink['click_count'])
                        ->set('comment', $existingClickStatistics['comment'] ."\n" . 'Updated by statisticOpening-uid=' . $openedLink['uid'] . ' (Counter +' . $openedLink['click_count'] . ')')
                        ->where(
                            $updateQueryBuilder->expr()->eq('uid',
                                $updateQueryBuilder->createNamedParameter($existingClickStatistics['uid'], \PDO::PARAM_INT)
                            )
                        );
                    $databaseQueries[] = $updateQueryBuilder->getSQL();
                    $updateQueryBuilder->execute();
                    
                } else {
                    
                    /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $insertQueryBuilder */
                    $insertQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                        ->getQueryBuilderForTable('tx_rkwmailer_domain_model_clickstatistics');
                    $insertQueryBuilder->insert('tx_rkwmailer_domain_model_clickstatistics')->values($newLink)->execute();
                    $databaseQueries[] = $insertQueryBuilder->getSQL();                    
                }
            }

            /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $updateQueryBuilder2 */
            $updateQueryBuilder2 = $connectionStatisticOpening->createQueryBuilder();
            $updateQueryBuilder2->update('tx_rkwmailer_domain_model_statisticopening')
                ->set('migrated', 1)
                ->where(
                    $updateQueryBuilder2->expr()->eq('uid',
                        $updateQueryBuilder2->createNamedParameter($openedLink['uid'], \PDO::PARAM_INT)
                    )
                );
            $databaseQueries[] = $updateQueryBuilder2->getSQL();
            $updateQueryBuilder2->execute();
        }

        // ============================================================
        // Now we migrate the openings
        // ============================================================
        $statement = $queryBuilderStatisticOpening->select('*')
            ->from('tx_rkwmailer_domain_model_statisticopening')
            ->where(
                $queryBuilderStatisticOpening->expr()->gt('pixel',0),
                $queryBuilderStatisticOpening->expr()->gt('queue_mail',0),
                $queryBuilderStatisticOpening->expr()->gt('queue_recipient',0),
                $queryBuilderStatisticOpening->expr()->gt('click_count',0),
                $queryBuilderStatisticOpening->expr()->lt('migrated',1)
            )
            ->setMaxResults(10000)
            ->execute();

        // go through all openings
        while ($opening = $statement->fetch()) {

            // if there are still some entries left, set flag accordingly
            $migrationFinished = false;
            
            $newOpening = [
                'pid' => $opening['pid'],
                'counter' => $opening['click_count'],
                'queue_mail' => $opening['queue_mail'],
                'queue_recipient' => $opening['queue_recipient'],
                'hash' => sha1($opening['queue_recipient']),
                'tstamp' => $opening['tstamp'],
                'crdate' => $opening['crdate'],                
                'comment' => 'Migrated from statisticOpening-uid=' . $opening['uid']
            ];

            // check if dataset already exists because there may be multiple entries in statisticopening-table
            // for the same queueMail-queueRecipient-combination
            $existingOpeningStatistics = $queryBuilderOpeningStatistics
                ->select('*')
                ->from('tx_rkwmailer_domain_model_openingstatistics')
                ->where(
                    $queryBuilderOpeningStatistics->expr()->eq('hash',
                        $queryBuilderOpeningStatistics->createNamedParameter($newOpening['hash'], \PDO::PARAM_STR)
                    ),
                    $queryBuilderOpeningStatistics->expr()->eq('queue_mail',
                        $queryBuilderOpeningStatistics->createNamedParameter($newOpening['queue_mail'], \PDO::PARAM_INT)
                    )
                )
                ->execute()
                ->fetch();

            // update existing dataset
            if ($existingOpeningStatistics) {

                /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $updateQueryBuilder */
                $updateQueryBuilder = $connectionOpeningStatistics->createQueryBuilder();
                $updateQueryBuilder->update('tx_rkwmailer_domain_model_openingstatistics')
                    ->set('counter', $existingOpeningStatistics['counter'] + $opening['click_count'])
                    ->set('comment', $existingOpeningStatistics['comment'] ."\n" . 'Updated by statisticOpening-uid=' . $opening['uid'] . ' (Counter +' . $opening['click_count'] . ')')
                    ->where(
                        $updateQueryBuilder->expr()->eq('uid',
                            $updateQueryBuilder->createNamedParameter($existingOpeningStatistics['uid'], \PDO::PARAM_INT)
                        )
                    );
                $databaseQueries[] = $updateQueryBuilder->getSQL();
                $updateQueryBuilder->execute();

            } else {

                /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $insertQueryBuilder */
                $insertQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getQueryBuilderForTable('tx_rkwmailer_domain_model_openingstatistics');
                $insertQueryBuilder->insert('tx_rkwmailer_domain_model_openingstatistics')->values($newOpening)->execute();
                $databaseQueries[] = $insertQueryBuilder->getSQL();
            }


            /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $updateQueryBuilder2 */
            $updateQueryBuilder2 = $connectionStatisticOpening->createQueryBuilder();
            $updateQueryBuilder2->update('tx_rkwmailer_domain_model_statisticopening')
                ->set('migrated', 1)
                ->where(
                    $updateQueryBuilder2->expr()->eq('uid',
                        $updateQueryBuilder2->createNamedParameter($opening['uid'], \PDO::PARAM_INT)
                    )
                );
            $databaseQueries[] = $updateQueryBuilder2->getSQL();
            $updateQueryBuilder2->execute();
            
        }
        
        var_dump($migrationFinished);
       //  $this->markWizardAsDone();
        return true;

    }

    /**
     * Update configuration
     *
     * @param array $databaseQueries Queries done in this update
     */
    protected function migrateConfiguration(array &$databaseQueries)
    {

        if ($this->hasLock(__FUNCTION__)){
            return;
        }

        /** @var  \TYPO3\CMS\Core\Database\Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_template');

        // find relevant root sys_templates
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder->getRestrictions()
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class)
            ->removeByType(HiddenRestriction::class)
            ->removeByType(DeletedRestriction::class);

        $statement = $queryBuilder->select('*')
            ->from('sys_template')
            ->where(
                $queryBuilder->expr()->eq('root',
                    $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT)
                )
            )
            ->execute();


        // go through all sys_templates
        while ($record = $statement->fetch()) {

            //  'Themes/Kompetenzzentrum/Configuration/TsConfig/_Websites/RkwThueringen/TsConfig.typoscript);
            $search = [
                '#EXT:rkw_template\/Configuration\/TypoScript\/Kompetenzzentrum#i',
                '#EXT:rkw_template\/Configuration\/TypoScript\/WePstra#i',
                '#EXT:rkw_template\/Configuration\/Themes\/Kompetenzzentrum\/TypoScript#i',
                '#EXT:rkw_template\/Configuration\/Themes\/WePstra\/TypoScript#i',
                '#EXT:rkw_template\/Themes\/Kompetenzzentrum\/Configuration\/TypoScript\/(_Websites|_Microsites)\/([^\/]+)#i',
                '#EXT:rkw_template\/Themes\/Kompetenzzentrum\/Configuration\/TypoScript#i',
                '#EXT:rkw_template\/Themes\/_Microsites\/Gem\/#i',
                '#EXT:rkw_template\/Themes\/_Websites\/Eanpc\/#i',
                '#EXT:css_styled_content\/Configuration/TypoScript/,#i',
                '#EXT:rtehtmlarea\/static\/clickenlarge/,#i',
            ];
            $replace = [
                'EXT:rkw_template/Themes/Kompetenzzentrum/Configuration/TypoScript',
                'EXT:rkw_template/Themes/WePstra/Configuration/TypoScript',
                'EXT:rkw_template/Themes/Kompetenzzentrum/Configuration/TypoScript',
                'EXT:rkw_template/Themes/WePstra/Configuration/TypoScript',
                'EXT:rkw_template/Themes/$1/$2/Configuration/TypoScript',
                'EXT:rkw_template/Themes/Kompetenzzentrum2016/Configuration/TypoScript',
                'EXT:rkw_template/Themes/_Microsites/GEM/',
                'EXT:rkw_template/Themes/_Websites/EANPC/',
                '',
                '',
            ];
            $record['include_static_file'] = preg_replace($search, $replace, $record['include_static_file']);

            // add default templates
            if (strpos($record['include_static_file'], 'EXT:fluid_styled_content/Configuration/TypoScript/') === false) {
                $record['include_static_file'] =  'EXT:fluid_styled_content/Configuration/TypoScript/,EXT:fluid_styled_content/Configuration/TypoScript/Styling/,EXT:gridelements/Configuration/TypoScript/,EXT:rkw_template/Configuration/TypoScript,'
                    . $record['include_static_file'];
            }

            // add cookie-opt-in and remove rkw_overlay - but not on WePstra!
            if (strpos($record['include_static_file'], 'Themes/WePstra') !== false) {

                // simply remove rkw_info_layer
                $record['include_static_file'] = str_replace('EXT:rkw_info_layer/Configuration/TypoScript,', '', $record['include_static_file']);

            } else {

                if (strpos($record['include_static_file'], 'EXT:sg_cookie_optin/Configuration/TypoScript/Frontend') === false) {

                    // add cookie opt in and remove rkw_info_layer
                    $record['include_static_file'] = str_replace('EXT:rkw_template/Configuration/TypoScript,', 'EXT:sg_cookie_optin/Configuration/TypoScript/Frontend,EXT:rkw_template/Configuration/TypoScript,', $record['include_static_file']);
                    $record['include_static_file'] = str_replace('EXT:rkw_info_layer/Configuration/TypoScript,', '', $record['include_static_file']);

                    // add rkw_privacy-extension
                    if (strpos($record['include_static_file'], 'EXT:rkw_privacy/Configuration/TypoScript') === false) {
                        $record['include_static_file'] = str_replace(',EXT:rkw_template/Themes/', ',EXT:rkw_privacy/Configuration/TypoScript,EXT:rkw_template/Themes/', $record['include_static_file']);
                    }
                }

            }

            // Replace bm_pdf2content
            $record['include_static_file'] = str_replace('EXT:bm_pdf2content/Configuration/TypoScript,', 'EXT:rkw_pdf2content/Configuration/TypoScript,', $record['include_static_file']);
            
            // update
            /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $updateQueryBuilder */
            $updateQueryBuilder = $connection->createQueryBuilder();
            $updateQueryBuilder->update('sys_template')
                ->set('include_static_file', $record['include_static_file'])
                ->where(
                    $updateQueryBuilder->expr()->eq('uid',
                        $updateQueryBuilder->createNamedParameter($record['uid'], \PDO::PARAM_INT)
                    )
                );
            $databaseQueries[] = $updateQueryBuilder->getSQL();
            $updateQueryBuilder->execute();
        }


        /** @var  \TYPO3\CMS\Core\Database\Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');

        // find go through all pages
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder->getRestrictions()
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class)
            ->removeByType(HiddenRestriction::class)
            ->removeByType(DeletedRestriction::class);

        $statement = $queryBuilder->select('*')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('is_siteroot',
                    $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT)
                )
            )
            ->execute();

        // go through all pages
        while ($record = $statement->fetch()) {

            $search = [
                '#.ts#',
                '#EXT:rkw_template\/Configuration\/TsConfig\/Kompetenzzentrum#i',
                '#EXT:rkw_template\/Configuration\/TsConfig\/WePstra#i',
                '#EXT:rkw_template\/Configuration\/Themes\/Kompetenzzentrum\/TsConfig#i',
                '#EXT:rkw_template\/Configuration\/Themes\/WePstra\/TsConfig#i',
                '#EXT:rkw_template\/Themes\/Kompetenzzentrum\/Configuration\/TsConfig\/(_Websites|_Microsites)\/([^\/]+)\/#i',
                '#EXT:rkw_template\/Themes\/Kompetenzzentrum\/Configuration\/TsConfig#i'
            ];
            $replace = [
                '.typoscript',
                'EXT:rkw_template/Themes/Kompetenzzentrum/Configuration/TsConfig',
                'EXT:rkw_template/Themes/WePstra/Configuration/TsConfig',
                'EXT:rkw_template/Themes/Kompetenzzentrum/Configuration/TsConfig',
                'EXT:rkw_template/Themes/WePstra/Configuration/TsConfig',
                'EXT:rkw_template/Themes/$1/$2/Configuration/TsConfig/',
                'EXT:rkw_template/Themes/Kompetenzzentrum2016/Configuration/TsConfig',
            ];

            $record['tsconfig_includes'] = preg_replace($search, $replace, $record['tsconfig_includes']);

            // add default template
            if (strpos($record['tsconfig_includes'], 'EXT:rkw_template/Configuration/TsConfig/TsConfig.typoscript') === false) {
                $record['tsconfig_includes'] = 'EXT:rkw_template/Configuration/TsConfig/TsConfig.typoscript,' . $record['tsconfig_includes'];
            }

            // update
            /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $updateQueryBuilder */
            $updateQueryBuilder = $connection->createQueryBuilder();
            $updateQueryBuilder->update('pages')
                ->set('tsconfig_includes', $record['tsconfig_includes'])
                ->where(
                    $updateQueryBuilder->expr()->eq('uid',
                        $updateQueryBuilder->createNamedParameter($record['uid'], \PDO::PARAM_INT)
                    )
                );

            $databaseQueries[] = $updateQueryBuilder->getSQL();
            $updateQueryBuilder->execute();

        }

        /** @var  \TYPO3\CMS\Core\Database\Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('be_groups');

        // find relevant root sys_templates
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder->getRestrictions()
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class)
            ->removeByType(HiddenRestriction::class)
            ->removeByType(DeletedRestriction::class);

        $statement = $queryBuilder->select('*')
            ->from('be_groups')
            ->execute();

        // go through all sys_templates
        while ($record = $statement->fetch()) {

            $search = [
                '#EXT:rkw_template\/Configuration\/TsConfigBeGroups\/_Core\/#i',
                '#EXT:rkw_template\/Configuration\/TsConfigBeGroups\/Kompetenzzentrum\/#i',
                '#EXT:rkw_template\/Configuration\/TsConfigBeGroups\/WePstra\/#i',

            ];
            $replace = [
                'EXT:rkw_template/Themes/_Core/Configuration/TsConfigBeGroups/',
                'EXT:rkw_template/Themes/Kompetenzzentrum2016/Configuration/TsConfigBeGroups/',
                'EXT:rkw_template/Themes/WePstra/Configuration/TsConfigBeGroups/',
            ];
            $record['TSconfig'] = preg_replace($search, $replace, $record['TSconfig']);

            // update
            /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $updateQueryBuilder */
            $updateQueryBuilder = $connection->createQueryBuilder();
            $updateQueryBuilder->update('be_groups')
                ->set('TSconfig', $record['TSconfig'])
                ->where(
                    $updateQueryBuilder->expr()->eq('uid',
                        $updateQueryBuilder->createNamedParameter($record['uid'], \PDO::PARAM_INT)
                    )
                );
            $databaseQueries[] = $updateQueryBuilder->getSQL();
            $updateQueryBuilder->execute();
        }


        // insert TsConfig to datasets
        $pidArray = [
            11 => 'EXT:rkw_template/Configuration/TsConfig/TsConfig.typoscript,EXT:rkw_template/Themes/_Core/Configuration/TsConfig/TsConfig.typoscript',
            3231 => 'EXT:rkw_template/Themes/Kompetenzzentrum2016/Configuration/TsConfig/TsConfig.typoscript',
            5085 => 'EXT:rkw_template/Themes/_Websites/RkwBadenWuerttemberg/Configuration/TsConfig/TsConfig.typoscript',
            4786 => 'EXT:rkw_template/Themes/_Websites/RkwBremen/Configuration/TsConfig/TsConfig.typoscript',
            4364 => 'EXT:rkw_template/Themes/_Websites/RkwBundesverein/Configuration/TsConfig/TsConfig.typoscript',
            3618 => 'EXT:rkw_template/Themes/_Websites/RkwNord/Configuration/TsConfig/TsConfig.typoscript',
            3232 => 'EXT:rkw_template/Themes/_Websites/RkwSachsen/Configuration/TsConfig/TsConfig.typoscript',
            3466 => 'EXT:rkw_template/Themes/_Websites/RkwSachsenAnhalt/Configuration/TsConfig/TsConfig.typoscript',
            3649 => 'EXT:rkw_template/Themes/_Websites/RkwThueringen/Configuration/TsConfig/TsConfig.typoscript',
            3904 => 'EXT:rkw_template/Themes/_Websites/EANPC/Configuration/TsConfig/TsConfig.typoscript',
        ];

        foreach ($pidArray as $pid => $config) {

            // update
            /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $updateQueryBuilder */
            $updateQueryBuilder = $connection->createQueryBuilder();
            $updateQueryBuilder->update('pages')
                ->set('tsconfig_includes', $config)
                ->where(
                    $updateQueryBuilder->expr()->eq('uid',
                        $updateQueryBuilder->createNamedParameter($pid, \PDO::PARAM_INT)
                    )
                );

            $databaseQueries[] = $updateQueryBuilder->getSQL();
            $updateQueryBuilder->execute();

        }

        
        // Fix for sorting of root-pages!
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $updateQueryBuilder */
        $updateQueryBuilder = $connection->createQueryBuilder();
        $updateQueryBuilder->update('pages')
            ->set('sorting', 255)
            ->where(
                $updateQueryBuilder->expr()->eq('uid',
                    $updateQueryBuilder->createNamedParameter(1, \PDO::PARAM_INT)
                )
            );

        $databaseQueries[] = $updateQueryBuilder->getSQL();
        $updateQueryBuilder->execute();

        $this->setLock(__FUNCTION__);

        
   
    }


    /**
     * Update cropping for header image elements
     *
     * @param array $databaseQueries Queries done in this update
     */
    protected function migrateCropping(array &$databaseQueries)
    {
        if ($this->hasLock(__FUNCTION__)){
            return;
        }

        // first of all we need all pages with a certain backendLayout and all pages
        // which inherit this certain backendLayout

        // multiple cases per BE-Layout!!!!!!
        $backendLayoutArray = [
            'pagets__topicPages' => [
                'media' => [
                    'field' => 'media',
                    'fieldTarget' => 'media',
                    'cropTarget' => 'topicDesktop',
                    'sortingTarget' => $this->sortIntervals,
                ],
                'tx_rkwbasics_article_image' => [
                    'field' => 'txRkwBasicsArticleImage',
                    'fieldTarget' => 'media',
                    'cropTarget' => 'articleDesktop',
                    'sortingTarget' => $this->sortIntervals * 2,
                ],
                'tx_rkwbasics_teaser_image' => [
                    'field' => 'txRkwBasicsTeaserImage',
                    'fieldTarget' => 'txRkwBasicsTeaserImage',
                    'cropTarget' => 'teaser',
                    'sortingTarget' => $this->sortIntervals,
                ],
            ],
            'pagets__contentPages' => [
                'media' => [
                    'field' => 'media',
                    'fieldTarget' => 'media',
                    'cropTarget' => 'topicDesktop',
                    'sortingTarget' => $this->sortIntervals * 2,
                ],
                'tx_rkwbasics_article_image' => [
                    'field' => 'txRkwBasicsArticleImage',
                    'fieldTarget' => 'media',
                    'cropTarget' => 'articleDesktop',
                    'sortingTarget' => $this->sortIntervals,
                ],
                'tx_rkwbasics_teaser_image' => [
                    'field' => 'txRkwBasicsTeaserImage',
                    'fieldTarget' => 'txRkwBasicsTeaserImage',
                    'cropTarget' => 'teaser',
                    'sortingTarget' => $this->sortIntervals,
                ],
            ],
            'pagets__publicationPages' => [
                'media' => [
                    'field' => 'media',
                    'fieldTarget' => 'media',
                    'cropTarget' => 'topicDesktop',
                    'sortingTarget' => $this->sortIntervals * 2,
                ],
                'tx_rkwbasics_article_image' => [
                    'field' => 'txRkwBasicsArticleImage',
                    'fieldTarget' => 'media',
                    'cropTarget' => 'publicationDesktop',
                    'sortingTarget' => $this->sortIntervals,
                ],
                'tx_rkwbasics_teaser_image' => [
                    'field' => 'txRkwBasicsTeaserImage',
                    'fieldTarget' => 'txRkwBasicsTeaserImage',
                    'cropTarget' => 'teaser',
                    'sortingTarget' => $this->sortIntervals,
                ],
            ],
        ];


        /** @var  \TYPO3\CMS\Core\Database\Connection $connectionPages */
        $connectionPages = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');

        /** @var  \TYPO3\CMS\Core\Database\Connection $connectionReference */
        $connectionReference = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_file_reference');

        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilderPages */
        $queryBuilderPages = $connectionPages->createQueryBuilder();
        $queryBuilderPages->getRestrictions()
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class)
            ->removeByType(HiddenRestriction::class)
            ->removeByType(DeletedRestriction::class);

        $statement = $queryBuilderPages->select('*')
            ->from('pages')
            ->orWhere(
                $queryBuilderPages->expr()->in('backend_layout',
                    $queryBuilderPages->createNamedParameter(array_keys($backendLayoutArray), Connection::PARAM_STR_ARRAY)
                ),
                $queryBuilderPages->expr()->in('backend_layout',
                    $queryBuilderPages->createNamedParameter(0,  \PDO::PARAM_INT)
                )
            )
            ->execute();


        // go through all elements
        while ($record = $statement->fetch()) {

            $recordBackendLayout = $record['backend_layout'];

            // if there is no backendLayout in the current record, we check for inheritance
            if (empty($recordBackendLayout)) {

                $rootline = \TYPO3\CMS\Backend\Utility\BackendUtility::BEgetRootLine($record['uid']);
                foreach ($rootline as $rootlinePage) {
                    if ($rootlinePage['uid'] == $record['uid']) {
                        continue;
                    }

                    if ($recordBackendLayout = $rootlinePage['backend_layout_next_level']) {
                        break;
                    }
                }
            }

            // now check for backendLayout and work through all pages with valid backendLayout
            if (in_array($recordBackendLayout, array_keys($backendLayoutArray))) {

                foreach ($backendLayoutArray[$recordBackendLayout] as $dbField => $config) {

                    if ($record[$dbField]) {

                        // get all sys_file_references of current page
                        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilderReference */
                        $queryBuilderReference = $connectionReference->createQueryBuilder();
                        $queryBuilderReference->getRestrictions()
                            ->removeByType(StartTimeRestriction::class)
                            ->removeByType(EndTimeRestriction::class)
                            ->removeByType(HiddenRestriction::class)
                            ->removeByType(DeletedRestriction::class);

                        $statementReference = $queryBuilderReference->select('*')
                            ->from('sys_file_reference')
                            ->where(
                                $queryBuilderReference->expr()->in('tablenames',
                                    $queryBuilderReference->createNamedParameter('pages',  \PDO::PARAM_STR)
                                ),
                                $queryBuilderReference->expr()->in('fieldname',
                                    $queryBuilderReference->createNamedParameter($config['field'],  \PDO::PARAM_STR)
                                ),
                                $queryBuilderReference->expr()->in('uid_foreign',
                                    $queryBuilderReference->createNamedParameter($record['uid'],  \PDO::PARAM_INT)
                                )
                            )
                            ->execute();

                        while ($reference = $statementReference->fetch()) {

                            // update reference elements
                            /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $updateQueryBuilder */
                            $updateQueryBuilder = $connectionReference->createQueryBuilder();
                            $updateQueryBuilder->update('sys_file_reference')
                                ->set('tablenames', 'pages')
                                ->set('fieldname', $config['fieldTarget'])
                                ->set('sorting', $config['sortingTarget'])
                                ->where(
                                    $updateQueryBuilder->expr()->eq('uid',
                                        $updateQueryBuilder->createNamedParameter(intval($reference['uid']), \PDO::PARAM_INT)
                                    )
                                );


                            if ($reference['crop']) {

                                // updating cropName
                                $referenceCrop = json_decode($reference['crop'], true);
                                if ($referenceCrop['default']) {
                                    $cropNew = [
                                        $config['cropTarget'] => $referenceCrop['default'],
                                    ];

                                    // now update the crop configuration in the reference
                                    $updateQueryBuilder->set('crop', json_encode($cropNew));
                                }
                            }

                            $databaseQueries[] = $updateQueryBuilder->getSQL();
                            $updateQueryBuilder->execute();
                        }
                    }
                }
            }
        }

        $this->setLock(__FUNCTION__);
    }


    /**
     * Update publication date handling
     *
     * @param array $databaseQueries Queries done in this update
     */
    protected function migrateFieldPublicationDate(array &$databaseQueries)
    {
        if ($this->hasLock(__FUNCTION__)){
            return;
        }


        /** @var  \TYPO3\CMS\Core\Database\Connection $connectionPages */
        $connectionPages = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');

        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilderPages */
        $queryBuilderPages = $connectionPages->createQueryBuilder();
        $queryBuilderPages->getRestrictions()
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class)
            ->removeByType(HiddenRestriction::class)
            ->removeByType(DeletedRestriction::class);

        $statement = $queryBuilderPages->select('*')
            ->from('pages')
            ->where(
                $queryBuilderPages->expr()->neq('tx_rkwsearch_pubdate',
                    $queryBuilderPages->createNamedParameter('',  \PDO::PARAM_STR)
                )

            )
            ->execute();


        // go through all elements
        while ($record = $statement->fetch()) {


            // update record elements
            /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $updateQueryBuilder */
            $updateQueryBuilder = $connectionPages->createQueryBuilder();
            $updateQueryBuilder->update('pages')
                ->set('lastUpdated', $record['tx_rkwsearch_pubdate'])
                ->where(
                    $updateQueryBuilder->expr()->eq('uid',
                        $updateQueryBuilder->createNamedParameter(intval($record['uid']), \PDO::PARAM_INT)
                    )
                );

            $databaseQueries[] = $updateQueryBuilder->getSQL();
            $updateQueryBuilder->execute();

        }

        $this->setLock(__FUNCTION__);
    }


    /**
     * Update publication date handling
     *
     * @param array $databaseQueries Queries done in this update
     */
    protected function migrateFieldInformation(array &$databaseQueries)
    {
        if ($this->hasLock(__FUNCTION__)){
            return;
        }

        /** @var  \TYPO3\CMS\Core\Database\Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content');

        /** @var  \TYPO3\CMS\Core\Database\Connection $connectionPages */
        $connectionPages = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');

        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilderPages */
        $queryBuilderPages = $connectionPages->createQueryBuilder();
        $queryBuilderPages->getRestrictions()
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class)
            ->removeByType(HiddenRestriction::class)
            ->removeByType(DeletedRestriction::class);

        $statement = $queryBuilderPages->select('*')
            ->from('pages')
            ->where(
                $queryBuilderPages->expr()->eq('tx_rkwpdf2content_is_import',
                    $queryBuilderPages->createNamedParameter(1,  \PDO::PARAM_INT)
                ),
                $queryBuilderPages->expr()->eq('tx_rkwpdf2content_is_import_sub',
                    $queryBuilderPages->createNamedParameter(0,  \PDO::PARAM_INT)
                )
            )
            ->execute();


        // go through all elements
        while ($record = $statement->fetch()) {

            if (
                (! $record['abstract'])
                && (!$record['tx_rkwbasics_information'])
            ) {
                continue;
            }

            // update sorting for existing content elements
            $queryBuilder = $connection->createQueryBuilder();
            $subStatement = $queryBuilder->select('*')
                ->from('tt_content')
                ->where(
                    $queryBuilder->expr()->eq('pid',
                        $queryBuilder->createNamedParameter($record['uid'], \PDO::PARAM_INT)
                    ),
                    $queryBuilder->expr()->eq('colPos',
                        $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                    )
                )
                ->execute();

            // go through all sub-elements
            while ($contentRecord = $subStatement->fetch()) {

                $updateQueryBuilder = $connection->createQueryBuilder();
                $updateQueryBuilder->update('tt_content')
                    ->set('sorting', intval($this->sortIntervals + $contentRecord['sorting']))
                    ->where(
                        $updateQueryBuilder->expr()->eq('uid',
                            $updateQueryBuilder->createNamedParameter($contentRecord['uid'], \PDO::PARAM_INT)
                        )
                    );
                $databaseQueries[] = $updateQueryBuilder->getSQL();
                $updateQueryBuilder->execute();
            }


            // create new content element with page content
            $preparedAbstract = preg_replace('#\r#', "", $record['abstract']);
            $preparedAbstract = preg_replace('#\n\n#', '</p><p>', $preparedAbstract);
            $preparedAbstract = preg_replace('#\n#', ' ', $preparedAbstract);

            $bodyText = '<p>' . $preparedAbstract . '</p>' . $record['tx_rkwbasics_information'];
            $newElement = [
                'pid' => intval($record['uid']),
                'sorting' => 0,
                'colPos' => 0,
                'tstamp' => time(),
                'crdate' => time(),
                'CType' => 'text',
                'header' => '',
                'bodytext' => $bodyText,
            ];

            /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $insertQueryBuilder */
            $insertQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
            $insertQueryBuilder->insert('tt_content')->values($newElement)->execute();
            $databaseQueries[] = $insertQueryBuilder->getSQL();
        }

        $this->setLock(__FUNCTION__);
    }



    /**
     * Update slider elements
     *
     * @param array $databaseQueries Queries done in this update
     */
    protected function migrateSliderElements(array &$databaseQueries)
    {
        if ($this->hasLock(__FUNCTION__)){
            return;
        }

        /** @var  \TYPO3\CMS\Core\Database\Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content');

        /*
         * Changes for slider-elements
         *
         * UPDATE tt_content SET CType = 'rkwtemplate_slider', subheader = bodytext, bodytext = '' WHERE colPos = 4;
         * UPDATE tt_content SET header_position = 'left' WHERE colPos = 4 AND rowDescription LIKE '%slide--left%';
         * UPDATE tt_content SET header_layout = 1 WHERE colPos = 4 AND rowDescription LIKE '%slide--gradient%' AND NOT rowDescription LIKE '%slide--white%';
         * UPDATE tt_content SET header_layout = 2 WHERE colPos = 4 AND NOT rowDescription LIKE '%slide--gradient%' AND rowDescription LIKE '%slide--white%';
         * UPDATE tt_content SET header_layout = 3 WHERE colPos = 4 AND rowDescription LIKE '%slide--gradient%' AND rowDescription LIKE '%slide--white%';
         * UPDATE tt_content SET rowDescription = tx_rkwbasics_header_link_caption WHERE colPos = 4;
        */
        $updateQueryBuilder = $connection->createQueryBuilder();
        $updateQueryBuilder->update('tt_content')
            ->set('CType', 'rkwtemplate_slider')
            ->set('subheader', 'bodytext', false)
            ->set('bodytext', '')
            ->where(
                $updateQueryBuilder->expr()->eq('colPos',
                    $updateQueryBuilder->createNamedParameter(4, \PDO::PARAM_INT)
                )
            );
        $databaseQueries[] = $updateQueryBuilder->getSQL();
        $updateQueryBuilder->execute();

        $updateQueryBuilder = $connection->createQueryBuilder();
        $updateQueryBuilder->update('tt_content')
            ->set('header_position', 'left')
            ->where(
                $updateQueryBuilder->expr()->eq('colPos',
                    $updateQueryBuilder->createNamedParameter(4, \PDO::PARAM_INT)
                ),
                $updateQueryBuilder->expr()->like('rowDescription',
                    $updateQueryBuilder->expr()->literal('%slide--left%')
                )
            );
        $databaseQueries[] = $updateQueryBuilder->getSQL();
        $updateQueryBuilder->execute();


        $updateQueryBuilder = $connection->createQueryBuilder();
        $updateQueryBuilder->update('tt_content')
            ->set('header_layout', '1')
            ->where(
                $updateQueryBuilder->expr()->eq('colPos',
                    $updateQueryBuilder->createNamedParameter(4, \PDO::PARAM_INT)
                ),
                $updateQueryBuilder->expr()->like('rowDescription',
                    $updateQueryBuilder->expr()->literal('%slide--gradient%')
                ),
                $updateQueryBuilder->expr()->notLike('rowDescription',
                    $updateQueryBuilder->expr()->literal('%slide--white%')
                )
            );
        $databaseQueries[] = $updateQueryBuilder->getSQL();
        $updateQueryBuilder->execute();


        $updateQueryBuilder = $connection->createQueryBuilder();
        $updateQueryBuilder->update('tt_content')
            ->set('header_layout', '2')
            ->where(
                $updateQueryBuilder->expr()->eq('colPos',
                    $updateQueryBuilder->createNamedParameter(4, \PDO::PARAM_INT)
                ),
                $updateQueryBuilder->expr()->notLike('rowDescription',
                    $updateQueryBuilder->expr()->literal('%slide--gradient%')
                ),
                $updateQueryBuilder->expr()->like('rowDescription',
                    $updateQueryBuilder->expr()->literal('%slide--white%')
                )
            );
        $databaseQueries[] = $updateQueryBuilder->getSQL();
        $updateQueryBuilder->execute();


        $updateQueryBuilder = $connection->createQueryBuilder();
        $updateQueryBuilder->update('tt_content')
            ->set('header_layout', '3')
            ->where(
                $updateQueryBuilder->expr()->eq('colPos',
                    $updateQueryBuilder->createNamedParameter(4, \PDO::PARAM_INT)
                ),
                $updateQueryBuilder->expr()->like('rowDescription',
                    $updateQueryBuilder->expr()->literal('%slide--gradient%')
                ),
                $updateQueryBuilder->expr()->like('rowDescription',
                    $updateQueryBuilder->expr()->literal('%slide--white%')
                )
            );
        $databaseQueries[] = $updateQueryBuilder->getSQL();
        $updateQueryBuilder->execute();


        $updateQueryBuilder = $connection->createQueryBuilder();
        $updateQueryBuilder->update('tt_content')
            ->set('rowDescription', 'tx_rkwbasics_header_link_caption', false)
            ->where(
                $updateQueryBuilder->expr()->eq('colPos',
                    $updateQueryBuilder->createNamedParameter(4, \PDO::PARAM_INT)
                )
            );
        $databaseQueries[] = $updateQueryBuilder->getSQL();
        $updateQueryBuilder->execute();

        // move elements into a grid element-wrapper
        $this->moveElementsFromColToGridContainer(
            [4 => 10],
            4,
            'sliderContainer',
            100,
            [],
            $databaseQueries,
            '',
            'rkwtemplate_slider'
        );

        $this->setLock(__FUNCTION__);
    }


    /**
     * Update mission statement elements
     *
     * @param array $databaseQueries Queries done in this update
     */
    protected function migrateMissionStatementElements(array &$databaseQueries)
    {

        if ($this->hasLock(__FUNCTION__)){
            return;
        }

        /** @var  \TYPO3\CMS\Core\Database\Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content');


        /**
         * Updates for mission statement
         *
         * UPDATE tt_content SET CType = 'rkwtemplate_mission' WHERE colPos = 12;
         * UPDATE tt_content SET subheader = header, header = 'Herzlich Willkommen auf dem RKW Portal!' WHERE uid = 7701;
         */
        $updateQueryBuilder = $connection->createQueryBuilder();
        $updateQueryBuilder->update('tt_content')
            ->set('CType', 'rkwtemplate_mission')
            ->where(
                $updateQueryBuilder->expr()->eq('colPos',
                    $updateQueryBuilder->createNamedParameter(12, \PDO::PARAM_INT)
                )
            );
        $databaseQueries[] = $updateQueryBuilder->getSQL();
        $updateQueryBuilder->execute();


        // Check for sub-headlines and transfer them to the subheader
        // find go through all pages
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder->getRestrictions()
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class)
            ->removeByType(HiddenRestriction::class)
            ->removeByType(DeletedRestriction::class);

        $statement = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('colPos',
                    $queryBuilder->createNamedParameter(12, \PDO::PARAM_INT)
                )
            )
            ->execute();

        // go through all elements
        while ($record = $statement->fetch()) {

            // <h2>Menschen. Unternehmen. Zukunft!</h2>
            $pattern = '#<h[0-9]+>([^<]+)</h[0-9]+>#';
            preg_match($pattern, $record['bodytext'], $matches, PREG_OFFSET_CAPTURE);
            if (
                ($matches)
                && ($matches[1][0])
            ){

                $record['bodytext'] = preg_replace($pattern, '', $record['bodytext']);
                $record['subheader'] = $matches[1][0];

                $updateQueryBuilder = $connection->createQueryBuilder();
                $updateQueryBuilder->update('tt_content')
                    ->set('bodytext', $record['bodytext'])
                    ->set('subheader', $record['subheader'])
                    ->where(
                        $updateQueryBuilder->expr()->eq('uid',
                            $updateQueryBuilder->createNamedParameter($record['uid'] , \PDO::PARAM_INT)
                        )
                    );
                $databaseQueries[] = $updateQueryBuilder->getSQL();
                $updateQueryBuilder->execute();
            }
        }


        $updateQueryBuilder = $connection->createQueryBuilder();
        $updateQueryBuilder->update('tt_content')
            ->set('subheader', 'header', false)
            ->set('header', 'Herzlich Willkommen auf dem RKW Portal!')
            ->where(
                $updateQueryBuilder->expr()->eq('uid',
                    $updateQueryBuilder->createNamedParameter(7701, \PDO::PARAM_INT)
                )
            );
        $databaseQueries[] = $updateQueryBuilder->getSQL();
        $updateQueryBuilder->execute();

        $this->setLock(__FUNCTION__);
    }


    /**
     * Update topic elements
     *
     * @param array $databaseQueries Queries done in this update
     */
    protected function migrateTopicElements(array &$databaseQueries)
    {
        if ($this->hasLock(__FUNCTION__)){
            return;
        }

        /** @var  \TYPO3\CMS\Core\Database\Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content');

        // find all topic elements and move css class from header_link to header_layout
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder->getRestrictions()
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class)
            ->removeByType(HiddenRestriction::class)
            ->removeByType(DeletedRestriction::class);

        $statement = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('colPos',
                    $queryBuilder->createNamedParameter(13, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->neq('pid',
                    $queryBuilder->createNamedParameter(4347, \PDO::PARAM_INT)
                )
            )
            ->execute();

        // go through all elements
        while ($record = $statement->fetch()) {

            $updateRecord = [];

            if ($headerLink = $record['header_link']) {

                $pattern = '/ ((topic|project)-([0-9a-z]+)|(feature-box--no-color))/';
                preg_match($pattern, $headerLink, $matches, PREG_OFFSET_CAPTURE);
                if (
                    ($matches)
                    && (isset($matches[2]))
                    && (isset($matches[3]))
                ) {

                    if (strpos(trim($matches[0][0]), 'topic') === 0) {
                        if ($topicId = intval($matches[3][0])) {
                            if ($topicId > 1) {
                                $updateRecord['header_layout'] = $topicId;
                            }
                        }

                    } else if (strpos(trim($matches[0][0]), 'project') === 0) {
                        $updateRecord['header_layout'] = 5;

                    } else if (strpos(trim($matches[0][0]), 'feature-box--no-color') === 0) {
                        $updateRecord['header_layout'] = 6;
                    }

                    $updateRecord['header_link'] = preg_replace($pattern, ' -', $headerLink);

                    // do update!
                    $updateQueryBuilder = $connection->createQueryBuilder();
                    $updateQueryBuilder->update('tt_content')
                        ->set('header_layout', $updateRecord['header_layout'])
                        ->set('header_link', $updateRecord['header_link'])
                        ->where(
                            $updateQueryBuilder->expr()->eq('uid',
                                $updateQueryBuilder->createNamedParameter($record['uid'], \PDO::PARAM_INT)
                            )
                        );
                    $databaseQueries[] = $updateQueryBuilder->getSQL();
                    $updateQueryBuilder->execute();
                }
            }
        }

        /**
         * UPDATE tt_content SET CType = 'rkwtemplate_topic' WHERE colPos = 13;
         */
        $updateQueryBuilder = $connection->createQueryBuilder();
        $updateQueryBuilder->update('tt_content')
            ->set('CType', 'rkwtemplate_topic')
            ->where(
                $updateQueryBuilder->expr()->eq('colPos',
                    $updateQueryBuilder->createNamedParameter(13, \PDO::PARAM_INT)
                ),
                $updateQueryBuilder->expr()->neq('pid',
                    $updateQueryBuilder->createNamedParameter(4347, \PDO::PARAM_INT)
                )
            );

        $databaseQueries[] = $updateQueryBuilder->getSQL();
        $updateQueryBuilder->execute();

        $labels = [
            '_default' => 'Unsere Themen',
            5062 => 'Unsere Schwerpunkte',
            4763 => 'Unsere Schwerpunkte',
            3595 => 'Unsere Schwerpunkte',
            3471 => 'Unsere Schwerpunkte',
            3757 => 'Unsere Leistungen im Überblick',
            3865 => 'Unsere Schwerpunkte',
            4367 => 'Schnelleinstieg',
            5027 => '',
            5011 => 'Schnelleinstieg',
            3684 => 'Schnelleinstieg',
            5043 => 'Schnelleinstieg',
            3673 => '',
        ];

        // move elements into a grid element-wrapper
        $this->moveElementsFromColToGridContainer(
            [13 => 20],
            13,
            'topicContainer',
            2,
            $labels,
            $databaseQueries,
            'pagets__homePages',
            'rkwtemplate_topic'
        );

        $this->setLock(__FUNCTION__);
    }



    /**
     * Update logo elements
     *
     * @param array $databaseQueries Queries done in this update
     */
    protected function migrateLogoElements(array &$databaseQueries)
    {
        if ($this->hasLock(__FUNCTION__)){
            return;
        }

        $colPosIds = [5,14,8];

        /** @var  \TYPO3\CMS\Core\Database\Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content');

        /** @var  \TYPO3\CMS\Core\Database\Connection $connectionPage */
        $connectionPage = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');

        // find all shortcuts in given cols
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = $connection->createQueryBuilder();
        $statement = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('CType',
                    $queryBuilder->createNamedParameter('shortcut', \PDO::PARAM_STR)
                ),
                $queryBuilder->expr()->in('colPos',
                    $queryBuilder->createNamedParameter($colPosIds, Connection::PARAM_INT_ARRAY)
                )
            )
            ->execute();


        // go through all elements
        while ($record = $statement->fetch()) {

            $headerLayout = 3;
            $defaultHeader = 'Referenzen';
            if ($record['colPos'] == 14) {
                $headerLayout = 4;
                $defaultHeader = 'Zertifizierungen &amp; Akkreditierungen';

            } else if ($record['colPos'] == 8) {
                $headerLayout = 4;
                $defaultHeader = 'Kooperationen';

            } else {

                // Check if we are on a rootpage
                /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
                $countQueryBuilder = $connectionPage->createQueryBuilder();
                $isSiteRoot = $countQueryBuilder->select('*')
                    ->from('pages')
                    ->where(
                        $countQueryBuilder->expr()->eq('is_siteroot',
                            $countQueryBuilder->createNamedParameter(1, \PDO::PARAM_INT)
                        ),
                        $countQueryBuilder->expr()->eq('uid',
                            $countQueryBuilder->createNamedParameter($record['pid'], \PDO::PARAM_INT)
                        )
                    )
                    ->execute()
                    ->fetchColumn(0);

                if ($isSiteRoot) {
                    $defaultHeader = 'Initiativen &amp; Partner';
                }
            }

            // move shortcut elements into a grid element-wrapper
            $this->moveElementsFromShortCutToGridContainer($record, 30, 'rkwtemplate_logo', 'logoContainer', $headerLayout, $defaultHeader, $databaseQueries);
        }

        $this->setLock(__FUNCTION__);
    }



    /**
     * Update logo elements
     *
     * @param array $databaseQueries Queries done in this update
     */
    protected function migratePromoterElements(array &$databaseQueries)
    {
        if ($this->hasLock(__FUNCTION__)){
            return;
        }

        /** @var  \TYPO3\CMS\Core\Database\Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content');

        // find all shortcuts in given cols
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = $connection->createQueryBuilder();
        $statement = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('CType',
                    $queryBuilder->createNamedParameter('shortcut', \PDO::PARAM_STR)
                ),
                $queryBuilder->expr()->eq('colPos',
                    $queryBuilder->createNamedParameter(19, \PDO::PARAM_INT)
                )
            )
            ->execute();


        // go through all elements
        while ($record = $statement->fetch()) {

            // move shortcut elements into a grid element-wrapper
            $this->moveElementsFromShortCutToGridContainer($record, 40, 'rkwtemplate_promoter', 'promoterContainer', 0, 'Förderer', $databaseQueries);
        }

        $this->setLock(__FUNCTION__);
    }


    /**
     * Update shortlink elements
     *
     * @param array $databaseQueries Queries done in this update
     */
    protected function migrateShortlinkElements(array &$databaseQueries)
    {
        if ($this->hasLock(__FUNCTION__)){
            return;
        }

        /** @var  \TYPO3\CMS\Core\Database\Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content');

        // find all elements in given cols
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder->getRestrictions()
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class)
            ->removeByType(HiddenRestriction::class)
            ->removeByType(DeletedRestriction::class);

        $statement = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->in('CType',
                    $queryBuilder->createNamedParameter(['header', 'text', 'textpic'], Connection::PARAM_INT_ARRAY)
                ),
                $queryBuilder->expr()->eq('colPos',
                    $queryBuilder->createNamedParameter(2, \PDO::PARAM_INT)
                )
            )
            ->execute();


        // go through all elements
        while ($record = $statement->fetch()) {

            // do update!
            $updateQueryBuilder = $connection->createQueryBuilder();
            $updateQueryBuilder->update('tt_content')
                ->set('header_layout', $record['layout'])
                ->set('CType', 'rkwtemplate_shortlink')
                ->where(
                    $updateQueryBuilder->expr()->eq('uid',
                        $updateQueryBuilder->createNamedParameter($record['uid'], \PDO::PARAM_INT)
                    )
                );
            $databaseQueries[] = $updateQueryBuilder->getSQL();
            $updateQueryBuilder->execute();
        }

        $this->setLock(__FUNCTION__);
    }


    /**
     * Update overview elements
     *
     * @param array $databaseQueries Queries done in this update
     */
    protected function migrateOverviewElements(array &$databaseQueries)
    {

        if ($this->hasLock(__FUNCTION__)){
            return;
        }

        /** @var  \TYPO3\CMS\Core\Database\Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content');

        /**
         * Updates for overviews
         **/
        $updateQueryBuilder = $connection->createQueryBuilder();
        $updateQueryBuilder->update('tt_content')
            ->set('CType', 'rkwtemplate_overview')
            ->where(
                $updateQueryBuilder->expr()->eq('colPos',
                    $updateQueryBuilder->createNamedParameter(13, \PDO::PARAM_INT)
                ),
                $updateQueryBuilder->expr()->eq('pid',
                    $updateQueryBuilder->createNamedParameter(4347, \PDO::PARAM_INT)
                )
            );
        $databaseQueries[] = $updateQueryBuilder->getSQL();
        $updateQueryBuilder->execute();


        // Check for button labels and transfer them to the subheader
        // find go through all pages
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder->getRestrictions()
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class)
            ->removeByType(HiddenRestriction::class)
            ->removeByType(DeletedRestriction::class);

        $statement = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('colPos',
                    $queryBuilder->createNamedParameter(13, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq('pid',
                    $queryBuilder->createNamedParameter(4347, \PDO::PARAM_INT)
                )
            )
            ->execute();

        // go through all elements
        while ($record = $statement->fetch()) {

            $updateQueryBuilder = $connection->createQueryBuilder();
            $updateQueryBuilder->update('tt_content')
                ->set('subheader', $record['tx_rkwbasics_header_link_caption'])
                ->where(
                    $updateQueryBuilder->expr()->eq('uid',
                        $updateQueryBuilder->createNamedParameter($record['uid'] , \PDO::PARAM_INT)
                    )
                );
            $databaseQueries[] = $updateQueryBuilder->getSQL();
            $updateQueryBuilder->execute();
        }


        $this->setLock(__FUNCTION__);
    }

    /**
     * Update table of content elements
     *
     * @param array $databaseQueries Queries done in this update
     */
    protected function migrateTableOfContentsElements(array &$databaseQueries)
    {

        if ($this->hasLock(__FUNCTION__)){
            return;
        }

        /** @var  \TYPO3\CMS\Core\Database\Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content');

        /**
         * Updates for table of contents
         **/
        $updateQueryBuilder = $connection->createQueryBuilder();
        $updateQueryBuilder->update('tt_content')
            ->set('CType', 'rkwtemplate_toc')
            ->where(
                $updateQueryBuilder->expr()->eq('section_frame',
                    $updateQueryBuilder->createNamedParameter(101, \PDO::PARAM_INT)
                )
            );
        $databaseQueries[] = $updateQueryBuilder->getSQL();
        $updateQueryBuilder->execute();


        // Check for headlines
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder->getRestrictions()
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class)
            ->removeByType(HiddenRestriction::class)
            ->removeByType(DeletedRestriction::class);

        $statement = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('CType',
                    $queryBuilder->createNamedParameter('rkwtemplate_toc', \PDO::PARAM_STR)
                ),
                $queryBuilder->expr()->eq('section_frame',
                    $queryBuilder->createNamedParameter(101, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq('header',
                    $queryBuilder->createNamedParameter('', \PDO::PARAM_STR)
                )
            )
            ->execute();

        // go through all elements
        while ($record = $statement->fetch()) {

            $updateQueryBuilder = $connection->createQueryBuilder();
            $updateQueryBuilder->update('tt_content')
                ->set('header', 'Inhalt')
                ->where(
                    $updateQueryBuilder->expr()->eq('uid',
                        $updateQueryBuilder->createNamedParameter($record['uid'] , \PDO::PARAM_INT)
                    )
                );
            $databaseQueries[] = $updateQueryBuilder->getSQL();
            $updateQueryBuilder->execute();
        }


        $this->setLock(__FUNCTION__);
    }



    /**
     * Update collapsed text elements
     *
     * @param array $databaseQueries Queries done in this update
     */
    protected function migrateCollapsedTextElements(array &$databaseQueries)
    {

        if ($this->hasLock(__FUNCTION__)){
            return;
        }

        /** @var  \TYPO3\CMS\Core\Database\Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content');

        /**
         * Updates for collapsed text
         **/
        $updateQueryBuilder = $connection->createQueryBuilder();
        $updateQueryBuilder->update('tt_content')
            ->set('CType', 'rkwtemplate_collapsed')
            ->where(
                $updateQueryBuilder->expr()->eq('section_frame',
                    $updateQueryBuilder->createNamedParameter(100, \PDO::PARAM_INT)
                )
            );
        $databaseQueries[] = $updateQueryBuilder->getSQL();
        $updateQueryBuilder->execute();


        $this->setLock(__FUNCTION__);
    }



    /**
     * Update image list elements
     *
     * @param array $databaseQueries Queries done in this update
     */
    protected function migrateImageListElements(array &$databaseQueries)
    {

        if ($this->hasLock(__FUNCTION__)){
            return;
        }

        /** @var  \TYPO3\CMS\Core\Database\Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content');

        /**
         * Updates for table of contents
         **/
        $updateQueryBuilder = $connection->createQueryBuilder();
        $updateQueryBuilder->update('tt_content')
            ->set('CType', 'rkwtemplate_imagelist')
            ->where(
                $updateQueryBuilder->expr()->eq('CType',
                    $updateQueryBuilder->createNamedParameter('textpic', \PDO::PARAM_STR)
                ),
                $updateQueryBuilder->expr()->eq('imageorient',
                    $updateQueryBuilder->createNamedParameter(18, \PDO::PARAM_INT)
                )
            );
        $databaseQueries[] = $updateQueryBuilder->getSQL();
        $updateQueryBuilder->execute();

        $this->setLock(__FUNCTION__);
    }


    /**
     * Update image text overlay elements
     *
     * @param array $databaseQueries Queries done in this update
     */
    protected function migrateImageTextOverlayElements(array &$databaseQueries)
    {

        if ($this->hasLock(__FUNCTION__)){
            return;
        }

        /** @var  \TYPO3\CMS\Core\Database\Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content');

        /**
         * Updates for table of contents
         **/
        $updateQueryBuilder = $connection->createQueryBuilder();
        $updateQueryBuilder->update('tt_content')
            ->set('CType', 'rkwtemplate_imagetextoverlay')
            ->where(
                $updateQueryBuilder->expr()->eq('CType',
                    $updateQueryBuilder->createNamedParameter('textpic', \PDO::PARAM_STR)
                ),
                $updateQueryBuilder->expr()->eq('imageorient',
                    $updateQueryBuilder->createNamedParameter(26, \PDO::PARAM_INT)
                )
            );
        $databaseQueries[] = $updateQueryBuilder->getSQL();
        $updateQueryBuilder->execute();

        $this->setLock(__FUNCTION__);
    }


    /**
     * Update topic elements
     *
     * @param array $databaseQueries Queries done in this update
     */
    protected function migrateContentsForTopicPages(array &$databaseQueries)
    {
        if ($this->hasLock(__FUNCTION__)){
            return;
        }

        // move elements into a grid element-wrapper
        $this->moveElementsFromColToGridContainer(
            [
                0 => 50,
                2 => 60,
                18 => 70,
            ],
            0,
            'contentContainerTwoCols',
            0,
            [],
            $databaseQueries,
            'pagets__topicPages'
        );

        $this->setLock(__FUNCTION__);
    }


    /**
     * Migrate contents for content pages
     *
     * @param array $databaseQueries Queries done in this update
     */
    protected function migrateContentsForContentPages(array &$databaseQueries)
    {
        if ($this->hasLock(__FUNCTION__)){
            return;
        }

        // move elements into a grid element-wrapper
        $this->moveElementsFromColToGridContainer(
            [
                0 => 50,
                18 => 70,
            ],
            0,
            'contentContainerOneCol',
            0,
            [],
            $databaseQueries,
            'pagets__contentPages'
        );
        $this->setLock(__FUNCTION__);
    }


    /**
     * Update topic elements
     *
     * @param array $databaseQueries Queries done in this update
     */
    protected function migrateContentsForPublicationPages(array &$databaseQueries)
    {
        if ($this->hasLock(__FUNCTION__)){
            return;
        }

        // move elements into a grid element-wrapper
        $this->moveElementsFromColToGridContainer(
            [
                0 => 50,
            ],
            0,
            'contentContainerOneCol',
            0,
            [],
            $databaseQueries,
            'pagets__publicationPages'
        );
        $this->setLock(__FUNCTION__);
    }




    /**
     * Update page layouts
     *
     * @param array $databaseQueries Queries done in this update
     */
    protected function migratePageLayouts(array &$databaseQueries)
    {
        if ($this->hasLock(__FUNCTION__)){
            return;
        }

        $layouts = [
            100 => 99999,   // rise number for consultant template
            6 => 99998,     // rise number for empty results

            210 => 10000,   // rise number for experts details template
            300 => 20000,   // rise number for events details template

            12 => 21,       // renumber tools-template (12) to new tools-template (21)
            11 => 50,       // renumber rkwMap-template (7) to new rkwMap-template (50)
            10 => 9000,      // migrate broken-link-template (3) to new broken-link-template (900)
            8 => 22,        // renumber special-template (8) to new special-template (22)
            7 => 12,        // renumber meinRkw-template (7) to new meinRkw-template (12)
            5 => 11,        // renumber search-template (5) to new pluginOnly-template (11)
            4 => 30,        // renumber publication-template (4) to new publication-template (30)
            3 => 10,        // renumber topic-template (3) to new topic-template (10) (=default)
            2 => 40,        // renumber home-template (2) to new home-template (40)
            1 => 20,        // renumber article-template (1) to new article-template (20)

            9 => 10,        // migrate blog-template (9) to topic-template (10)
            200 => 11,      // migrate expertList-template (200) to plugin-Only-template (11)

        ];

        $excludePageIds = [2854,3229,2856,2878,2879,2855,2876,2875,2877];

        /** @var  \TYPO3\CMS\Core\Database\Connection $connectionPages */
        $connectionPages = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');

        foreach ($layouts as $sourceNumber => $targetNumber) {
            $updateQueryBuilder = $connectionPages->createQueryBuilder();
            $updateQueryBuilder->update('pages')
                ->set('layout', $targetNumber)
                ->where(
                    $updateQueryBuilder->expr()->eq('layout',
                        $updateQueryBuilder->createNamedParameter($sourceNumber, \PDO::PARAM_INT)
                    ),
                    $updateQueryBuilder->expr()->notIn('uid',
                        $updateQueryBuilder->createNamedParameter($excludePageIds, Connection::PARAM_INT_ARRAY)
                    )
                );
            $databaseQueries[] = $updateQueryBuilder->getSQL();
            $updateQueryBuilder->execute();

            $updateQueryBuilder = $connectionPages->createQueryBuilder();
            $updateQueryBuilder->update('pages')
                ->set('tx_rkwbasics_fe_layout_next_level', $targetNumber)
                ->where(
                    $updateQueryBuilder->expr()->eq('tx_rkwbasics_fe_layout_next_level',
                        $updateQueryBuilder->createNamedParameter($sourceNumber, \PDO::PARAM_INT)
                    ),
                    $updateQueryBuilder->expr()->notIn('uid',
                        $updateQueryBuilder->createNamedParameter($excludePageIds, Connection::PARAM_INT_ARRAY)
                    )
                );
            $databaseQueries[] = $updateQueryBuilder->getSQL();
            $updateQueryBuilder->execute();
        }

        $this->setLock(__FUNCTION__);
    }


    /**
     * Merge cols
     *
     * @param array $databaseQueries Queries done in this update
     */
    protected function mergeCols (array &$databaseQueries)
    {
        if ($this->hasLock(__FUNCTION__)){
            return;
        }

        // order is important!
        $colPosList = [4,12,13,15,16,5,14];
        $this->moveElementsFromColsIntoCol($colPosList, 100, $databaseQueries, 'pagets__homePages');

        $colPosList = [7];
        $this->moveElementsFromColsIntoCol($colPosList, 110, $databaseQueries, 'pagets__homePages');

        $colPosList = [19];
        $this->moveElementsFromColsIntoCol($colPosList, 210, $databaseQueries, 'pagets__topicPages');

        $colPosList = [8,9,6,5];
        $this->moveElementsFromColsIntoCol($colPosList, 220, $databaseQueries, 'pagets__topicPages');

        $colPosList = [17];
        $this->moveElementsFromColsIntoCol($colPosList, 310, $databaseQueries, 'pagets__contentPages');

        $colPosList = [3];
        $this->moveElementsFromColsIntoCol($colPosList, 410, $databaseQueries, 'pagets__publicationPages');

        $colPosList = [6];
        $this->moveElementsFromColsIntoCol($colPosList, 500, $databaseQueries, 'pagets__pluginOnlyPages');

        $colPosList = [0];
        $this->moveElementsFromColsIntoCol($colPosList, 600, $databaseQueries, 'pagets__expertPagesDetail');

        $colPosList = [11];
        $this->moveElementsFromColsIntoCol($colPosList, 610, $databaseQueries, 'pagets__expertPagesDetail');

        $colPosList = [6];
        $this->moveElementsFromColsIntoCol($colPosList, 620, $databaseQueries, 'pagets__expertPagesDetail');

        $colPosList = [7];
        $this->moveElementsFromColsIntoCol($colPosList, 710, $databaseQueries, 'pagets__eventsDetail');

        $colPosList = [12];
        $this->moveElementsFromColsIntoCol($colPosList, 810, $databaseQueries, 'pagets__rkwMaps');

        $colPosList = [13];
        $this->moveElementsFromColsIntoCol($colPosList, 820, $databaseQueries, 'pagets__rkwMaps');


        $this->setLock(__FUNCTION__);
    }


    /**
     * Remove RkwSearch
     *
     * @param array $databaseQueries Queries done in this update
     */
    protected function removeRkwSearch (array &$databaseQueries)
    {
        if ($this->hasLock(__FUNCTION__)){
            return;
        }

        /*$packageManager = GeneralUtility::makeInstance(PackageManager::class);
        if (!$packageManager->isPackageAvailable('rkw_search')) {
            return;
        }*/

        /** @var  \TYPO3\CMS\Core\Database\Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content');

        /** @var  \TYPO3\CMS\Core\Database\Connection $connectionPages */
        $connectionPages = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');

        // now delete plugins
        $updateQueryBuilder = $connection->createQueryBuilder();
        $updateQueryBuilder->update('tt_content')
            ->set('deleted', 1)
            ->where(
                $updateQueryBuilder->expr()->eq('CType',
                    $updateQueryBuilder->createNamedParameter('list', \PDO::PARAM_STR)
                ),
                $updateQueryBuilder->expr()->like('list_type',
                    $updateQueryBuilder->createNamedParameter('rkwsearch%', \PDO::PARAM_STR)
                )
            );
        $databaseQueries[] = $updateQueryBuilder->getSQL();
        $updateQueryBuilder->execute();


        // delete pages
        $pageUids = [1368,1371];
        $updateQueryBuilder = $connectionPages->createQueryBuilder();
        $updateQueryBuilder->update('pages')
            ->set('deleted', 1)
            ->where(
                $updateQueryBuilder->expr()->in('uid',
                    $updateQueryBuilder->createNamedParameter($pageUids, Connection::PARAM_INT_ARRAY)
                )
            );
        $databaseQueries[] = $updateQueryBuilder->getSQL();
        $updateQueryBuilder->execute();

        /*
        if ($packageManager->isPackageActive('rkw_search')) {
            $packageManager->deactivatePackage('rkw_search');
        }*/

        $this->setLock(__FUNCTION__);
    }

    /**
     * Remove RkwSearch
     *
     * @param array $databaseQueries Queries done in this update
     */
    protected function removeRkwConsultant (array &$databaseQueries)
    {
        if ($this->hasLock(__FUNCTION__)){
            return;
        }

        /*$packageManager = GeneralUtility::makeInstance(PackageManager::class);
        if (!$packageManager->isPackageAvailable('rkw_consultant')) {
            return;
        }*/

        /** @var  \TYPO3\CMS\Core\Database\Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content');

        /** @var  \TYPO3\CMS\Core\Database\Connection $connectionPages */
        $connectionPages = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');

        /** @var  \TYPO3\CMS\Core\Database\Connection $connectionFeGroups */
        $connectionFeGroups = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('fe_groups');

        // now delete plugins
        $updateQueryBuilder = $connection->createQueryBuilder();
        $updateQueryBuilder->update('tt_content')
            ->set('deleted', 1)
            ->where(
                $updateQueryBuilder->expr()->eq('CType',
                    $updateQueryBuilder->createNamedParameter('list', \PDO::PARAM_STR)
                ),
                $updateQueryBuilder->expr()->like('list_type',
                    $updateQueryBuilder->createNamedParameter('consultant%', \PDO::PARAM_STR)
                )
            );
        $databaseQueries[] = $updateQueryBuilder->getSQL();
        $updateQueryBuilder->execute();


        // delete pages
        $pageUids = [2525,2524,2523,1917,2820,2264,2819,2341,2340,4350,4382];
        $updateQueryBuilder = $connectionPages->createQueryBuilder();
        $updateQueryBuilder->update('pages')
            ->set('deleted', 1)
            ->where(
                $updateQueryBuilder->expr()->in('uid',
                    $updateQueryBuilder->createNamedParameter($pageUids, Connection::PARAM_INT_ARRAY)
                )
            );
        $databaseQueries[] = $updateQueryBuilder->getSQL();
        $updateQueryBuilder->execute();

        $this->setLock(__FUNCTION__);


        // deactivate fe_usergroup as FE-Service
        $updateQueryBuilder = $connectionFeGroups->createQueryBuilder();
        $updateQueryBuilder->update('fe_groups')
            ->set('tx_rkwregistration_is_service', 0)
            ->where(
                $updateQueryBuilder->expr()->in('uid',
                    $updateQueryBuilder->createNamedParameter(3, \PDO::PARAM_INT)
                )
            );
        $databaseQueries[] = $updateQueryBuilder->getSQL();
        $updateQueryBuilder->execute();

        /*if ($packageManager->isPackageActive('rkw_consultant')) {
            $packageManager->deactivatePackage('rkw_consultant');
        }*/

        $this->setLock(__FUNCTION__);

    }

}
