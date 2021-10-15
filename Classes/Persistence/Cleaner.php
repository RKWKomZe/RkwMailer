<?php
namespace RKW\RkwMailer\Persistence;

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

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * A class to cleanup the database
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @api
 */
class Cleaner
{

    /**
     * queueMailRepository
     *
     * @var \RKW\RkwMailer\Domain\Repository\QueueMailRepository
     * @inject
     */
    protected $queueMailRepository;

    /**
     * queueRecipientRepository
     *
     * @var \RKW\RkwMailer\Domain\Repository\QueueRecipientRepository
     * @inject
     */
    protected $queueRecipientRepository;


    /**
     * openingStatisticsRepository
     *
     * @var \RKW\RkwMailer\Domain\Repository\OpeningStatisticsRepository
     * @inject
     */
    protected $openingStatisticsRepository;


    /**
     * clickStatisticsRepository
     *
     * @var \RKW\RkwMailer\Domain\Repository\ClickStatisticsRepository
     * @inject
     */
    protected $clickStatisticsRepository;



    /**
     * mailingStatisticsRepository
     *
     * @var \RKW\RkwMailer\Domain\Repository\MailingStatisticsRepository
     * @inject
     */
    protected $mailingStatisticsRepository;


    /**
     * logger
     *
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;


    
    /**
     * cleanup 
     *
     * @param int $daysAfterSendingFinished  Defines how many days after its sending has been finished an queueMail will be
     *     deleted (default: 30 days)
     * @param array $types Defines which types of mails the cleanup should look for (Default: only type "0")
     * @param bool $includingStatistics
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function cleanup(
        int $daysAfterSendingFinished = 30,
        array $types = [],
        bool $includingStatistics = false
    ): bool {
        
        if (
            ($queueMails = $this->queueMailRepository->findByTstampFinishedSendingAndTypes(
                $daysAfterSendingFinished,
                $types
            ))
            && (count($queueMails))
            ) {

            /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
            foreach ($queueMails as $queueMail) {

                $this->deleteQueueMail($queueMail);
                $this->deleteQueueRecipients($queueMail);
                if ($includingStatistics) {
                    $this->deleteStatistics($queueMail);
                }
                
                $this->getLogger()->log(
                    \TYPO3\CMS\Core\Log\LogLevel::INFO, 
                    sprintf(
                        'Cleanup for queueMail with uid %s finished successfully.', 
                        $queueMail->getUid()
                    )
                );
            }
            
            return true;
        }

        return false;
    }


    /**
     * delete queueMail by queueMail-object
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return int
     */
    public function deleteQueueMail (
         \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
    ): int {
            
        $result = $this->queueMailRepository->deleteByQueueMail($queueMail);
        $this->getLogger()->log(
            \TYPO3\CMS\Core\Log\LogLevel::INFO,
            sprintf(
                'Deleted queueMail with uid %s.',
                $queueMail->getUid()
            )
        );
        
        return $result;
    }

    
    /**
     * delete queueRecipients by queueMail-object
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return int
     */
    public function deleteQueueRecipients(
         \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
    ): int {

        $result = $this->queueRecipientRepository->deleteByQueueMail($queueMail);
       
        $this->getLogger()->log(
            \TYPO3\CMS\Core\Log\LogLevel::INFO,
            sprintf(
                'Deleted %s queueRecipients of queueMail with uid %s.',
                $result,
                $queueMail->getUid()
            )
        );

        return $result;
    }


    /**
     * delete statistics by queueMail-object
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return int
     */
    public function deleteStatistics(
        \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
    ): int {

        $result = $this->mailingStatisticsRepository->deleteByQueueMail($queueMail);
        $result += $this->openingStatisticsRepository->deleteByQueueMail($queueMail);
        $result += $this->clickStatisticsRepository->deleteByQueueMail($queueMail);

        $this->getLogger()->log(
            \TYPO3\CMS\Core\Log\LogLevel::INFO,
            sprintf(
                'Deleted %s statistic-datasets of queueMail with uid %s.',
                $result,
                $queueMail->getUid()
            )
        );

        return $result;
    }
    

    /**
     * Returns logger instance
     *
     * @return Logger
     */
    protected function getLogger(): Logger
    {
        if (!$this->logger instanceof Logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }
}
