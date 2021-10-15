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
    protected $title = 'Migrate statistics of "rkw_mailer" to new version. Maybe this wizard has to be executed multiple times until all data is migrated. When the migration was successful, the wizward is marked as done automatically.';


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
        if (
            ($this->deleteOrphanedRecipients($databaseQueries))
            && ($this->deleteOrphanedStatisticOpenings($databaseQueries))
            && ($this->migrateStatistics($databaseQueries))
        ){
            $this->markWizardAsDone();
        }
                
        return true;
    }

    /**
     * Update statistics
     *
     * @param array $databaseQueries Queries done in this update
     * @return bool
     */
    protected function migrateStatistics(array &$databaseQueries)
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
                $queryBuilderStatisticOpening->expr()->lt('migrated',2)
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
                    'queue_mail_uid' => $openedLink['queue_mail'],
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
                ->set('migrated', 2)
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
                $queryBuilderStatisticOpening->expr()->lt('migrated',2)
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
                'queue_mail_uid' => $opening['queue_mail'],
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
                ->set('migrated', 2)
                ->where(
                    $updateQueryBuilder2->expr()->eq('uid',
                        $updateQueryBuilder2->createNamedParameter($opening['uid'], \PDO::PARAM_INT)
                    )
                );
            $databaseQueries[] = $updateQueryBuilder2->getSQL();
            $updateQueryBuilder2->execute();

        }
        
        return $migrationFinished;
    }


    /**
     * Delete orphaned recipients
     *
     * @param array $databaseQueries Queries done in this update
     * @return bool
     */
    protected function deleteOrphanedRecipients(array &$databaseQueries)
    {
        // flag for wizard
        $migrationFinished = true;

        /** @var  \TYPO3\CMS\Core\Database\Connection $connectionQueueMail */
        $connectionQueueMail = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_rkwmailer_domain_model_queuemail');

        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilderQueueMail */
        $queryBuilderQueueMail = $connectionQueueMail->createQueryBuilder();
        $queryBuilderQueueMail->getRestrictions()
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class)
            ->removeByType(HiddenRestriction::class)
            ->removeByType(DeletedRestriction::class);

        /** @var  \TYPO3\CMS\Core\Database\Connection $connectionQueueRecipient */
        $connectionQueueRecipient = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_rkwmailer_domain_model_queuerecipient');

        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilderQueueRecipient */
        $queryBuilderQueueRecipient = $connectionQueueRecipient->createQueryBuilder();
        $queryBuilderQueueRecipient->getRestrictions()
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class)
            ->removeByType(HiddenRestriction::class)
            ->removeByType(DeletedRestriction::class);

        // ============================================================
        // Now we check of queueRecipients without existing queueMail!
        // ============================================================ 
        $statement = $queryBuilderQueueRecipient ->select('*')
            ->from('tx_rkwmailer_domain_model_queuerecipient')
            ->where(
                $queryBuilderQueueRecipient ->expr()->lt('migrated',1)
            )
            ->setMaxResults(50000)
            ->execute();

        // get all queueMails
        $allQueueMailUids = $queryBuilderQueueMail
            ->select('uid')
            ->from('tx_rkwmailer_domain_model_queuemail')
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        $allQueueMailUids = array_flip($allQueueMailUids);
        
        // go through all openings
        while ($queueRecipient = $statement->fetch()) {

            // if there are still some entries left, set flag accordingly
            $migrationFinished = false;

            // delete existing dataset
            if (! isset($allQueueMailUids[$queueRecipient['queue_mail']])) {

                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getQueryBuilderForTable('tx_rkwmailer_domain_model_queuerecipient');

                $queryBuilder->delete('tx_rkwmailer_domain_model_queuerecipient')
                    ->where(
                        $queryBuilder->expr()->eq(
                            'uid',
                            $queryBuilder->createNamedParameter($queueRecipient['uid'], \PDO::PARAM_INT))
                    );
                $databaseQueries[] = $queryBuilder->getSQL();
                $queryBuilder->execute();
                
            } else {
                
                /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $updateQueryBuilder */
                $updateQueryBuilder = $connectionQueueRecipient->createQueryBuilder();
                $updateQueryBuilder->update('tx_rkwmailer_domain_model_queuerecipient')
                    ->set('migrated', 1)
                    ->where(
                        $updateQueryBuilder->expr()->eq('uid',
                            $updateQueryBuilder->createNamedParameter($queueRecipient['uid'], \PDO::PARAM_INT)
                        )
                    );
                $databaseQueries[] = $updateQueryBuilder->getSQL();
                $updateQueryBuilder->execute();
            }
        }
        
        return $migrationFinished;
    }


    /**
     * Delete orphaned statisticOpenings
     *
     * @param array $databaseQueries Queries done in this update
     * @return bool
     */
    protected function deleteOrphanedStatisticOpenings(array &$databaseQueries)
    {
        // flag for wizard
        $migrationFinished = true;

        /** @var  \TYPO3\CMS\Core\Database\Connection $connectionQueueMail */
        $connectionQueueMail = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_rkwmailer_domain_model_queuemail');

        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilderQueueMail */
        $queryBuilderQueueMail = $connectionQueueMail->createQueryBuilder();
        $queryBuilderQueueMail->getRestrictions()
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class)
            ->removeByType(HiddenRestriction::class)
            ->removeByType(DeletedRestriction::class);

        /** @var  \TYPO3\CMS\Core\Database\Connection $connectionStatisticOpenings */
        $connectionStatisticOpenings = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_rkwmailer_domain_model_statisticopening');

        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilderStatisticOpenings */
        $queryBuilderStatisticOpenings = $connectionStatisticOpenings->createQueryBuilder();
        $queryBuilderStatisticOpenings->getRestrictions()
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class)
            ->removeByType(HiddenRestriction::class)
            ->removeByType(DeletedRestriction::class);

        // ============================================================
        // Now we check of statisticOpenings without existing queueMail!
        // ============================================================ 
        $statement = $queryBuilderStatisticOpenings ->select('*')
            ->from('tx_rkwmailer_domain_model_statisticopening')
            ->where(
                $queryBuilderStatisticOpenings ->expr()->lt('migrated',1)
            )
            ->setMaxResults(50000)
            ->execute();

        // get all queueMails
        $allQueueMailUids = $queryBuilderQueueMail
            ->select('uid')
            ->from('tx_rkwmailer_domain_model_queuemail')
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        $allQueueMailUids = array_flip($allQueueMailUids);

        // go through all openings
        while ($statisticOpenings = $statement->fetch()) {

            // if there are still some entries left, set flag accordingly
            $migrationFinished = false;

            // delete existing dataset
            if (! isset($allQueueMailUids[$statisticOpenings['queue_mail']])) {

                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getQueryBuilderForTable('tx_rkwmailer_domain_model_statisticopening');

                $queryBuilder->delete('tx_rkwmailer_domain_model_statisticopening')
                    ->where(
                        $queryBuilder->expr()->eq(
                            'uid',
                            $queryBuilder->createNamedParameter($statisticOpenings['uid'], \PDO::PARAM_INT))
                    );
                $databaseQueries[] = $queryBuilder->getSQL();
                $queryBuilder->execute();

            } else {

                /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $updateQueryBuilder */
                $updateQueryBuilder = $connectionStatisticOpenings->createQueryBuilder();
                $updateQueryBuilder->update('tx_rkwmailer_domain_model_statisticopening')
                    ->set('migrated', 1)
                    ->where(
                        $updateQueryBuilder->expr()->eq('uid',
                            $updateQueryBuilder->createNamedParameter($statisticOpenings['uid'], \PDO::PARAM_INT)
                        )
                    );
                $databaseQueries[] = $updateQueryBuilder->getSQL();
                $updateQueryBuilder->execute();
            }
        }

        return $migrationFinished;
    }
}
