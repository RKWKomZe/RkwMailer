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

        $period = \RKW\RkwMailer\Utility\TimePeriodUtility::getTimePeriod($timeFrame);
        $sentMails = $this->queueMailRepository->findAllSentOrSendingWithStatistics($period['from'], $period['to'], $mailType);

        $mailTypeList = array();
        if (is_array($this->settings['types'])) {
            foreach ($this->settings['types'] as $key => $value)
                $mailTypeList[$value] = ucFirst($key);
        }

        $this->view->assignMultiple(
            array(
                'sentMails' => $sentMails,
                'sentMailListItem' => $sentMails,
                'timeFrame' => $timeFrame,
                'mailTypeList' => $mailTypeList,
                'mailType' => $mailType,
            )
        );
    }


    /**
     * Shows statistics
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function clickedLinksAction(\RKW\RkwMailer\Domain\Model\QueueMail $queueMail)
    {


        $period = \RKW\RkwMailer\Utility\TimePeriodUtility::getTimePeriod($timeFrame);
        $sentMails = $this->queueMailRepository->findAllSentOrSendingWithStatistics($period['from'], $period['to'], $mailType);

        $mailTypeList = array();
        if (is_array($this->settings['types'])) {
            foreach ($this->settings['types'] as $key => $value)
                $mailTypeList[$value] = ucFirst($key);
        }

        $this->view->assignMultiple(
            array(
                'sentMails' => $sentMails,
                'sentMailListItem' => $sentMails,
                'timeFrame' => $timeFrame,
                'mailTypeList' => $mailTypeList,
                'mailType' => $mailType,
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

        $spaceOfTime = \RKW\RkwMailer\Utility\TimePeriodUtility::getTimePeriod($timeFrame);

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
        foreach ($this->queueRecipientRepository->findByQueueMail($queueMail) as $recipient) {
            $recipient->setStatus(2);
            $this->queueRecipientRepository->update($recipient);
        }


        // reset statistics for openings
        $this->statisticOpeningRepository->removeAllByQueueMail($queueMail);

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