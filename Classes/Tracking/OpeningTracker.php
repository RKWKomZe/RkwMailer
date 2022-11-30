<?php

namespace RKW\RkwMailer\Tracking;

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

use RKW\RkwMailer\Domain\Model\OpeningStatistics;
use RKW\RkwMailer\Utility\StatisticsUtility;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * OpeningTracker
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OpeningTracker
{

    /**
     * QueueMailRepository
     *
     * @var \RKW\RkwMailer\Domain\Repository\QueueMailRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $queueMailRepository;

    /**
     * QueueRecipientRepository
     *
     * @var \RKW\RkwMailer\Domain\Repository\QueueRecipientRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $queueRecipientRepository;

    /**
     * clickStatisticsRepository
     *
     * @var \RKW\RkwMailer\Domain\Repository\OpeningStatisticsRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $openingStatisticsRepository;

    /**
     * Persistence Manager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $persistenceManager;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;


    /**
     * Tracks the opening of the email
     *
     * @param int $queueMailId
     * @param int $queueMailRecipientId
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function track(
        int $queueMailId = 0,
        int $queueMailRecipientId = 0
    ): bool {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        if (
            ($queueMailId)
            && ($queueMailRecipientId)
            && ($queueMail = $this->queueMailRepository->findByUid($queueMailId))
            && ($queueRecipient = $this->queueRecipientRepository->findByUid($queueMailRecipientId))
        ) {

            $this->persistTrackingData($queueMail, $queueRecipient);
            return true;
        }

        return false;
    }


    /**
     * Persists tracking-data to database
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    protected function persistTrackingData (
        \RKW\RkwMailer\Domain\Model\QueueMail $queueMail,
        \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
    ): void
    {

        // generate hash from recipient
        $hash = StatisticsUtility::generateRecipientHash($queueRecipient);

        // check if this hash-value already exists for this queueMail
        if ($openingStatistic = $this->openingStatisticsRepository->findOneByHashAndQueueMail($hash, $queueMail)) {

            $openingStatistic->setCounter($openingStatistic->getCounter() +1);
            $this->openingStatisticsRepository->update($openingStatistic);
            $this->persistenceManager->persistAll();

            $this->getLogger()->log(
                LogLevel::INFO,
                sprintf(
                    'Updating openingStatistic with uid=%s for queueRecipient with uid %s and queueMail with uid %s.',
                    $openingStatistic->getUid(),
                    $queueRecipient->getUid(),
                    $queueMail->getUid()
                )
            );

        } else {

            /** @var \RKW\RkwMailer\Domain\Model\OpeningStatistics $openingStatistic */
            $openingStatistic = GeneralUtility::makeInstance(OpeningStatistics::class);
            $openingStatistic->setQueueMail($queueMail);
            $openingStatistic->setQueueRecipient($queueRecipient);
            $openingStatistic->setHash($hash);
            $openingStatistic->setCounter(1);

            $this->openingStatisticsRepository->add($openingStatistic);
            $this->persistenceManager->persistAll();

            $this->getLogger()->log(
                LogLevel::INFO,
                sprintf(
                    'Adding new openingStatistic for queueRecipient with uid %s and queueMail with uid %s.',
                    $queueRecipient->getUid(),
                    $queueMail->getUid()
                )
            );
        }
    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {
        if (!$this->logger instanceof Logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }
}
