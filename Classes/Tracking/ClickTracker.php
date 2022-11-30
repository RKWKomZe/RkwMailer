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

use RKW\RkwMailer\Domain\Model\ClickStatistics;
use RKW\RkwMailer\Utility\StatisticsUtility;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ClickTracker
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ClickTracker
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
     * LinkRepository
     *
     * @var \RKW\RkwMailer\Domain\Repository\LinkRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $linkRepository;


    /**
     * clickStatisticsRepository
     *
     * @var \RKW\RkwMailer\Domain\Repository\ClickStatisticsRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $clickStatisticsRepository;

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
     * Tracks the opening of a link
     *
     * @param int $queueMailId
     * @param string $string
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function track(
        int $queueMailId = 0,
        string $string = ''
    ): bool {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        if (
            ($queueMailId)
            && ($queueMail = $this->queueMailRepository->findByUid($queueMailId))
            && ($string)
        ) {
            $this->persistTrackingData($queueMail, $string);
            return true;
        }

        return false;
    }


    /**
     * Get the redirect url with all relevant parameters
     *
     * @param string $url
     * @param int $queueMailId
     * @param int $queueMailRecipientId
     * @return string
     */
    public function getRedirectUrl (
        string $url,
        int $queueMailId = 0,
        int $queueMailRecipientId = 0
    ): string {

        // decode url (just to be sure)
        $url = urldecode($url);

        // additional params
        $additionalParams = [];

        // check for queueMail
        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        if ($queueMail = $this->queueMailRepository->findByUid($queueMailId)) {

            // set queueMail as additional param
            $additionalParams[] = 'tx_rkwmailer[mid]=' . $queueMail->getUid();

            // check additionally for corresponding queueRecipient
            /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueMailRecipient */
            if (
                ($queueMailRecipientId)
                && ($queueMailRecipient = $this->queueRecipientRepository->findOneByUidAndQueueMail($queueMailRecipientId, $queueMail))
            ) {
                $additionalParams[] = 'tx_rkwmailer[uid]=' . $queueMailRecipient->getUid();
            }
        }

        return StatisticsUtility::addParamsToUrl($url, $additionalParams);
    }



    /**
     * Get getUrl by Hash
     *
     * @param string $hash
     * @param int $queueMailId
     * @param int $queueMailRecipientId
     * @return string
     * @deprecated
     */
    public function getPlainUrlByHash(string $hash): string {

        /** @var \RKW\RkwMailer\Domain\Model\Link $link */
        if ($link = $this->linkRepository->findOneByHash($hash)) {
            return $link->getUrl();
        }

        return '';
    }


    /**
     * Persists tracking-data to database
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @param string $hash
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    protected function persistTrackingData (
        \RKW\RkwMailer\Domain\Model\QueueMail $queueMail,
        string $url
    ): void
    {
        // decode url (just to be sure)
        $url = urldecode($url);

        // generate hash from url
        $hash = StatisticsUtility::generateLinkHash($url);

        // check if this hash-value already exists for this queueMail
        if ($clickStatistic = $this->clickStatisticsRepository->findOneByHashAndQueueMail($hash, $queueMail)) {

            $clickStatistic->setCounter($clickStatistic->getCounter() +1);
            $this->clickStatisticsRepository->update($clickStatistic);
            $this->persistenceManager->persistAll();

            $this->getLogger()->log(
                LogLevel::INFO,
                sprintf(
                    'Updating clickStatistic with uid %s for url %s of queueMail with uid %s.',
                    $clickStatistic->getUid(),
                    $url,
                    $queueMail->getUid()
                )
            );

        } else {

            /** @var \RKW\RkwMailer\Domain\Model\ClickStatistics $clickStatistic */
            $clickStatistic = GeneralUtility::makeInstance(ClickStatistics::class);
            $clickStatistic->setQueueMail($queueMail);
            $clickStatistic->setHash($hash);
            $clickStatistic->setUrl($url);
            $clickStatistic->setCounter(1);

            $this->clickStatisticsRepository->add($clickStatistic);
            $this->persistenceManager->persistAll();

            $this->getLogger()->log(
                LogLevel::INFO,
                sprintf(
                    'Adding new clickStatistic for url %s of queueMail with uid %s.',
                    $url,
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
