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
     * findAllWithStatistics
     *
     * @param \RKW\RkwMailer\Domain\Model\Queuemail $queueMail
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|NULL
     */
    public function findByQueueMailWithStatistics($queueMail)
    {

        $query = $this->createQuery();
        $query->statement('
            SELECT tx_rkwmailer_domain_model_link.url as url, SUM(click_count) as clicked FROM tx_rkwmailer_domain_model_statisticopening 
            RIGHT JOIN tx_rkwmailer_domain_model_link 
                ON tx_rkwmailer_domain_model_link.uid = tx_rkwmailer_domain_model_statisticopening.link  
            WHERE tx_rkwmailer_domain_model_statisticopening.pixel = 0   
            AND tx_rkwmailer_domain_model_statisticopening.queue_mail = ' . intval($queueMail->getUid()) . '
            AND tx_rkwmailer_domain_model_statisticopening.queue_mail = tx_rkwmailer_domain_model_link.queue_mail
            GROUP BY tx_rkwmailer_domain_model_statisticopening.link
            ORDER BY tx_rkwmailer_domain_model_link.url
        ');


        return $query->execute(true);
        //====
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