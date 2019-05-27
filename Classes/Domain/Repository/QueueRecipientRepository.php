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
 * QueueRecipientRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class QueueRecipientRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    public function initializeObject()
    {

        $this->defaultQuerySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');
        $this->defaultQuerySettings->setRespectStoragePage(false);
    }


    /**
     * findAllByMailWithStatusSending
     * TypoScript status-settings:
     * draft = 1
     * waiting = 2
     * sending = 3
     * sent = 4
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @param integer $limit
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|NULL
     * @todo: Write Testing
     */
    public function findAllByQueueMailWithStatusWaiting(\RKW\RkwMailer\Domain\Model\QueueMail $queueMail, $limit = 25)
    {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('queueMail', intval($queueMail->getUid())),
                $query->equals('status', '2')
            )
        );

        if ($limit > 0) {
            $query->setLimit(intval($limit));
        }

        return $query->execute();
        //====
    }


    /**
     *  findOneByUidAndQueueMai
     *
     * @param int $uid
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return \RKW\RkwMailer\Domain\Model\QueueRecipient|NULL
     * @todo: Write Testing
     */
    public function findOneByUidAndQueueMail($uid, \RKW\RkwMailer\Domain\Model\QueueMail $queueMail)
    {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('uid', intval($uid)),
                $query->equals('queueMail', intval($queueMail->getUid()))
            )
        );


        return $query->execute()->getFirst();
        //====
    }



    /**
     * findAllLastBounced
     *
     * @param int $limit
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|NULL
     */
    public function findAllLastBounced($limit = 100)
    {

        $query = $this->createQuery();
        $query->statement('
            SELECT tx_rkwmailer_domain_model_queuerecipient.* FROM tx_rkwmailer_domain_model_queuerecipient
            LEFT JOIN tx_rkwmailer_domain_model_queuemail 
                ON tx_rkwmailer_domain_model_queuerecipient.queue_mail = tx_rkwmailer_domain_model_queuemail.uid
            LEFT JOIN tx_rkwmailer_domain_model_bouncemail 
                ON tx_rkwmailer_domain_model_queuerecipient.email = tx_rkwmailer_domain_model_bouncemail.email
                AND tx_rkwmailer_domain_model_queuerecipient.crdate < tx_rkwmailer_domain_model_bouncemail.crdate
                AND tx_rkwmailer_domain_model_bouncemail.status = 0
            WHERE tx_rkwmailer_domain_model_bouncemail.type = "hard"
            AND tx_rkwmailer_domain_model_queuerecipient.status = 4
            AND tx_rkwmailer_domain_model_queuemail.status IN (3,4)
            AND tx_rkwmailer_domain_model_queuemail.type > 0
            AND tx_rkwmailer_domain_model_queuerecipient.tstamp = (
                SELECT MAX(recipient_sub.tstamp) FROM tx_rkwmailer_domain_model_queuerecipient as recipient_sub WHERE
                recipient_sub.status = 4 AND 
                recipient_sub.email = tx_rkwmailer_domain_model_queuerecipient.email
            )
            LIMIT ' . intval ($limit) . '
        ');


        return $query->execute();
        //====
    }

}