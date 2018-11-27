<?php

namespace RKW\RkwMailer\Controller;

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

/**
 * BackendController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class BackendController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * statisticMailRepository
     *
     * @var \RKW\RkwMailer\Domain\Repository\StatisticMailRepository
     * @inject
     */
    protected $statisticMailRepository;

    /**
     * statisticOpeningRepository
     *
     * @var \RKW\RkwMailer\Domain\Repository\StatisticOpeningRepository
     * @inject
     */
    protected $statisticOpeningRepository;

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
     * LinkRepository
     *
     * @var \RKW\RkwMailer\Domain\Repository\LinkRepository
     * @inject
     */
    protected $linkRepository;


    /**
     * Shows statistics
     *
     * @param integer $timeFrame
     * @param integer $mailType
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function statisticsAction($timeFrame = 0, $mailType = -1)
    {

        $timePeriodeHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwMailer\\Helper\\TimePeriod');
        $spaceOfTime = $timePeriodeHelper->getTimePeriod($timeFrame);

        //===========================================
        // Listings
        //===========================================
        // get mail statistics (order by mail_id)
        $mailStatistics = $this->statisticMailRepository->findAllSentMails($spaceOfTime, $mailType);

        //===========================================
        // Counting
        //===========================================
        // 1. get mail-count group by mailType
        $countMailsTotalByType = $this->queueMailRepository->countAllSentMailsGroupByType($spaceOfTime);

        // 2. get link clicks per mail total
        $mailClickCounts = array();
        foreach ($mailStatistics as $statisticMail) {
            $mailClickCounts[] = $this->statisticOpeningRepository->countAllClicksOnLinks($statisticMail, $spaceOfTime);
        }

        // 3. get all sendings which was opened by recipient per mail
        $mailOpenedCounts = array();
        foreach ($mailStatistics as $statisticMail) {
            $mailOpenedCounts[] = $this->statisticOpeningRepository->countAllOpenedMails($statisticMail, $spaceOfTime);
        }

        // 4. get count of recipients which has click in mail
        $recipientsWhichClickInMail = array();
        foreach ($mailStatistics as $statisticMail) {
            $recipientsWhichClickInMail[] = $this->statisticOpeningRepository->countAllRecipientsWhichClickInMail($statisticMail, $spaceOfTime);
        }

        // 5. get all links of mail ordered by clicks
        $linkClicksInMail = array();
        foreach ($mailStatistics as $statisticMail) {
            $linkClicksInMail[] = $this->statisticOpeningRepository->findAllClicksByStatisticMail($statisticMail, $spaceOfTime);
        }

        $mailTypeList = array();
        if (is_array($this->settings['types'])) {
            foreach ($this->settings['types'] as $key => $value)
                $mailTypeList[$value] = ucFirst($key);
        }

        $this->view->assignMultiple(
            array(
                'countMailsTotal'            => intval(count($mailStatistics)),
                'countMailsTotalByType'      => $countMailsTotalByType,
                'sentMails'                  => $mailStatistics,
                'mailStatistics'             => $mailStatistics->toArray(),
                'mailClickCounts'            => $mailClickCounts,
                'mailOpenedCounts'           => $mailOpenedCounts,
                'recipientsWhichClickInMail' => $recipientsWhichClickInMail,
                'linkClicksInMail'           => $linkClicksInMail,
                'timeFrame'                  => $timeFrame,
                'mailTypeList'               => $mailTypeList,
                'mailType'                   => $mailType,
            )
        );
    }


    /**
     * Lists all e-mails in queue
     *
     * @param integer $timeFrame
     * @param integer $mailType
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function listAction($timeFrame = 0, $mailType = -1)
    {

        /** @var \RKW\RkwMailer\Helper\TimePeriod $timePeriodeHelper */
        $timePeriodeHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwMailer\\Helper\\TimePeriod');
        $spaceOfTime = $timePeriodeHelper->getTimePeriod($timeFrame);

        $mailTypeList = array();
        if (is_array($this->settings['types'])) {
            foreach ($this->settings['types'] as $key => $value)
                $mailTypeList[$value] = ucFirst($key);
        }

        $this->view->assignMultiple(
            array(
                'mailList'     => $this->queueMailRepository->findAllByCreateDateAndType($spaceOfTime, $mailType),
                'timeFrame'    => $timeFrame,
                'mailTypeList' => $mailTypeList,
                'mailType'     => $mailType,
            )
        );
    }

    /**
     * Pauses given queueMail
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function pauseAction(\RKW\RkwMailer\Domain\Model\QueueMail $queueMail)
    {

        $queueMail->setStatus(1);
        $queueMail->setTstampRealSending(0);
        $queueMail->setTstampSendFinish(0);
        $this->queueMailRepository->update($queueMail);

        $this->redirect('list');
        //===

    }

    /**
     * Continues given queueMail
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException*
     */
    public function continueAction(\RKW\RkwMailer\Domain\Model\QueueMail $queueMail)
    {

        $queueMail->setStatus(2);
        $queueMail->setTstampRealSending(0);
        $queueMail->setTstampSendFinish(0);
        $this->queueMailRepository->update($queueMail);

        $this->redirect('list');
        //===

    }

    /**
     * Resets given queueMail
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function resetAction(\RKW\RkwMailer\Domain\Model\QueueMail $queueMail)
    {

        // set mail-values
        $queueMail->setStatus(2);
        $queueMail->setTstampRealSending(0);
        $queueMail->setTstampSendFinish(0);
        $this->queueMailRepository->update($queueMail);

        // reset all recipients
        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $recipient */
        foreach ($queueMail->getQueueRecipients() as $recipient) {
            $recipient->setStatus(2);
            $this->queueRecipientRepository->update($recipient);
        }

        // reset statistics for mail
        /** @var \RKW\RkwMailer\Domain\Model\StatisticMail $statisticMail */
        if ($statisticMail = $queueMail->getStatisticMail()) {
            $statisticMail->setContactedCount(0);
            $statisticMail->setBouncesCount(0);
            $statisticMail->setErrorCount(0);
            $this->statisticMailRepository->update($statisticMail);

            /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
            $objectStorage = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage');

            // reset statistics for openings
            // @todo: loop through objectStorage does not work
            $this->statisticOpeningRepository->removeAllByQueueMail($queueMail);
            $queueMail->setStatisticOpenings($objectStorage);
            $this->queueMailRepository->update($queueMail);
        }

        $this->redirect('list');
        //===

    }

    /**
     * Deletes given queueMail and it's children
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function deleteAction(\RKW\RkwMailer\Domain\Model\QueueMail $queueMail)
    {

        // dependent objects are deleted by cascade
        $this->queueMailRepository->remove($queueMail);
        $this->redirect('list');
        //===

    }


}