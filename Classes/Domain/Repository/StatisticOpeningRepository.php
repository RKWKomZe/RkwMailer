<?php

namespace RKW\RkwMailer\Domain\Repository;

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
 * StatisticOpeningRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class StatisticOpeningRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    public function initializeObject()
    {

        $this->defaultQuerySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');
        $this->defaultQuerySettings->setRespectStoragePage(false);
    }


    /**
     * findRedirectByMailIdAndPixel
     *
     * @param \RKW\RkwMailer\Domain\Model\queueMail $queueMail
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @return \RKW\RkwMailer\Domain\Model\StatisticOpening
     */
    public function findOneByQueueMailAndQueueRecipientAndPixel(\RKW\RkwMailer\Domain\Model\QueueMail $queueMail, \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient)
    {

        $query = $this->createQuery();

        $query->matching(
            $query->logicalAnd(
                $query->equals('queueMail', intval($queueMail->getUid())),
                $query->equals('queueRecipient', intval($queueRecipient->getUid())),
                $query->equals('pixel', 1)
            )
        );

        return $query->execute()->getFirst();
        //===
    }


    /**
     * findOneByLinkAndQueueRecipient
     *
     * @param \RKW\RkwMailer\Domain\Model\Link $link
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @return \RKW\RkwMailer\Domain\Model\StatisticOpening
     */
    public function findOneByLinkAndQueueRecipient(\RKW\RkwMailer\Domain\Model\Link $link, \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient = null)
    {

        $query = $this->createQuery();
        $constraints = [
            $query->equals('link', intval($link->getUid()))
        ];

        if ($queueRecipient) {
            $constraints[] = $query->equals('queueRecipient', intval($queueRecipient->getUid()));
        } else {
            $constraints[] = $query->equals('queueRecipient', 0);
        }

        $query->matching(
            $query->logicalAnd(
                $constraints
            )
        );

        return $query->execute()->getFirst();
        //===
    }


    /**
     * findAllClicksByQueueMail
     *
     * @param array $spaceOfTime
     * @param \RKW\RkwMailer\Domain\Model\StatisticMail $statisticMail
     * @return integer $openedMails
     */
    public function findAllClicksByStatisticMail(\RKW\RkwMailer\Domain\Model\StatisticMail $statisticMail, $spaceOfTime = null)
    {
        $query = $this->createQuery();

        $query->statement('
            SELECT SUM(opening.click_count) AS count, link.url
            FROM tx_rkwmailer_domain_model_statisticopening AS opening
            LEFT JOIN tx_rkwmailer_domain_model_link AS link
            ON link.uid = opening.link
            WHERE opening.link > 0 and opening.queue_mail = ' . intval($statisticMail->getQueueMail()->getUid()) . '
            AND opening.crdate >= ' . intval($spaceOfTime['from']) . '
			AND opening.crdate <= ' . intval($spaceOfTime['to']) . '
            GROUP BY opening.link
            ORDER BY count DESC
        ');

        return $query->execute(true);
        //===
    }


    /**
     * countAllClicksOnLinksByMail
     *
     * @param array $spaceOfTime
     * @param \RKW\RkwMailer\Domain\Model\StatisticMail $statisticMail
     * @return integer
     */
    public function countAllClicksOnLinks($statisticMail, $spaceOfTime)
    {

        $query = $this->createQuery();

        $query->statement("
			SELECT SUM(click_count) AS clickCountTotal
			FROM tx_rkwmailer_domain_model_statisticopening statisticopening
			JOIN tx_rkwmailer_domain_model_queuemail queuemail ON queuemail.uid = statisticopening.queue_mail
			WHERE (queuemail.status = 4 OR queuemail.status = 3)
			AND queuemail.uid = " . intval($statisticMail->getQueueMail()->getUid()) . "
			AND queuemail.tstamp_fav_sending >= " . intval($spaceOfTime['from']) . "
			AND queuemail.tstamp_fav_sending <= " . intval($spaceOfTime['to']) . "
			AND statisticopening.pixel = 0
		");

        $clicks = $query->execute(true);

        if (!$clicks[0]['clickCountTotal']) {
            $clicks = 0;
        } else {
            $clicks = $clicks[0]['clickCountTotal'];
        }

        return intval($clicks);
        //===
    }


    /**
     * countAllOpenedMails
     *
     * @param \RKW\RkwMailer\Domain\Model\StatisticMail $statisticMail
     * @param array $spaceOfTime
     * @return integer
     */
    public function countAllOpenedMails(\RKW\RkwMailer\Domain\Model\StatisticMail $statisticMail, $spaceOfTime)
    {

        $query = $this->createQuery();
        $query->statement("
			SELECT *
			FROM tx_rkwmailer_domain_model_statisticopening statisticopening
			JOIN tx_rkwmailer_domain_model_queuemail queuemail ON queuemail.uid = statisticopening.queue_mail
			WHERE (queuemail.status = 4 OR queuemail.status = 3)
			AND queuemail.uid = " . intval($statisticMail->getQueueMail()->getUid()) . "
			AND queuemail.tstamp_fav_sending >= " . intval($spaceOfTime['from']) . "
			AND queuemail.tstamp_fav_sending <= " . intval($spaceOfTime['to']) . "
			AND statisticopening.pixel = 1
		");

        return count($query->execute(true));
        //===

    }


    /**
     * countAllRecipientsWhichClickInMail
     *
     * @param \RKW\RkwMailer\Domain\Model\StatisticMail $statisticMail
     * @param array $spaceOfTime
     * @return integer $recipientClickCount
     */
    public function countAllRecipientsWhichClickInMail(\RKW\RkwMailer\Domain\Model\StatisticMail $statisticMail, $spaceOfTime)
    {

        $query = $this->createQuery();
        $query->statement("
			SELECT *
			FROM tx_rkwmailer_domain_model_statisticopening statisticopening
			JOIN tx_rkwmailer_domain_model_queuemail queuemail ON queuemail.uid = statisticopening.queue_mail
			WHERE (queuemail.status = 4 OR queuemail.status = 3)
			AND queuemail.uid = " . intval($statisticMail->getQueueMail()->getUid()) . "
			AND queuemail.tstamp_fav_sending >= " . intval($spaceOfTime['from']) . "
			AND queuemail.tstamp_fav_sending <= " . intval($spaceOfTime['to']) . "
			AND statisticopening.pixel = 0
			GROUP BY statisticopening.queue_recipient
		");

        return count($query->execute(true));
        //===
    }


    /**
     * removeAllByQueueMail
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return void
     */
    public function removeAllByQueueMail(\RKW\RkwMailer\Domain\Model\QueueMail $queueMail)
    {

        // workaround: createQuery-Statement doesn't work
        // http://www.typo3.net/forum/thematik/zeige/thema/116600/
        $GLOBALS['TYPO3_DB']->sql_query('
			DELETE FROM tx_rkwmailer_domain_model_statisticopening
			WHERE queue_mail = ' . intval($queueMail->getUid()) . '
		');

        return;
        //===
    }

}