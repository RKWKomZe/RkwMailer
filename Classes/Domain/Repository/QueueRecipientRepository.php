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

use RKW\RkwMailer\Utility\QueueRecipientUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
     * findAllByQueueMailWithStatusWaiting
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @param integer $limit
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|NULL
     * @comment implicitly tested
     */
    public function findAllByQueueMailWithStatusWaiting(
        \RKW\RkwMailer\Domain\Model\QueueMail $queueMail, 
        int $limit = 25
    ) {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('queueMail', intval($queueMail->getUid())),
                $query->equals('status', QueueRecipientUtility::STATUS_WAITING)
            )
        );

        if ($limit > 0) {
            $query->setLimit(intval($limit));
        }

        return $query->execute();
    }


    /**
     *  findOneByUidAndQueueMail
     *
     * @param int $uid
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return \RKW\RkwMailer\Domain\Model\QueueRecipient|NULL
     * @comment implicitly tested
     */
    public function findOneByUidAndQueueMail(
        int $uid, 
        \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
    )
    {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('uid', intval($uid)),
                $query->equals('queueMail', intval($queueMail->getUid()))
            )
        );
        
        return $query->execute()->getFirst();
    }

    /**
     * findByEmailAndQueueMail
     *
     * @param string $email
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return \RKW\RkwMailer\Domain\Model\QueueRecipient|NULL
     * @comment implicitly tested
     */
    public function findOneByEmailAndQueueMail(
        string $email, 
        \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
    ) {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('email', $email),
                $query->equals('queueMail', $queueMail)
            )
        );

        return $query->execute()->getFirst();
    }


    /**
     * countTotalRecipientsByQueueMail
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return int
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @comment implicitly tested
     */
    public function countTotalRecipientsByQueueMail(
        \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
    ): int {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('queueMail', $queueMail),
                $query->greaterThanOrEqual('status', QueueRecipientUtility::STATUS_WAITING)
            )
        );

        return $query->execute()->count();
    }

    
    /**
     * countTotalSentByQueueMail
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return int
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @comment implicitly tested
     */
    public function countTotalSentByQueueMail(
        \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
    ): int {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('queueMail', $queueMail),
                $query->greaterThanOrEqual('status', QueueRecipientUtility::STATUS_FINISHED),
                $query->logicalNot(
                    $query->equals('status', QueueRecipientUtility::STATUS_DEFERRED)
                )
            )
        );

        return $query->execute()->count();
    }


    /**
     * countDeliveredByQueueMail
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return int
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @comment implicitly tested
     */
    public function countDeliveredByQueueMail(
        \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
    ): int {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('queueMail', $queueMail),
                $query->equals('status', QueueRecipientUtility::STATUS_FINISHED)
            )
        );

        return $query->execute()->count();
    }

    
    /**
     * countFailedByQueueMail
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return int
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @comment implicitly tested
     */
    public function countFailedByQueueMail(
        \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
    ): int {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('queueMail', $queueMail),
                $query->equals('status', QueueRecipientUtility::STATUS_ERROR)
            )
        );

        return $query->execute()->count();
    }

    /**
     * countDeferredByQueueMail
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return int
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @comment implicitly tested
     */
    public function countDeferredByQueueMail(
        \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
    ): int {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('queueMail', $queueMail),
                $query->equals('status', QueueRecipientUtility::STATUS_DEFERRED)
            )
        );

        return $query->execute()->count();
    }


    /**
     * countBouncedByQueueMail
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return int
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @comment implicitly tested
     */
    public function countBouncedByQueueMail(
        \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
    ): int {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('queueMail', $queueMail),
                $query->equals('status', QueueRecipientUtility::STATUS_BOUNCED)
            )
        );

        return $query->execute()->count();
    }
    
    
    /**
     * findAllLastBounced
     *
     * @param int $limit
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|NULL
     * @toDo: rework
     * @toDo: write tests
     */
    public function findAllLastBounced($limit = 100)
    {

        $query = $this->createQuery();
        $query->statement('
            SELECT tx_rkwmailer_domain_model_queuerecipient.* FROM tx_rkwmailer_domain_model_queuerecipient
            LEFT JOIN tx_rkwmailer_domain_model_queuemail 
                ON tx_rkwmailer_domain_model_queuerecipient.queue_mail = tx_rkwmailer_domain_model_queuemail.uid
            LEFT JOIN tx_rkwmailer_domain_model_bouncemail 
                ON tx_rkwmailer_domain_model_bouncemail.email = tx_rkwmailer_domain_model_queuerecipient.email
                AND tx_rkwmailer_domain_model_bouncemail.crdate > tx_rkwmailer_domain_model_queuerecipient.crdate
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
    }


    /**
     * deleteByQueueMail
     * We use a straight-forward approach here because it may be a lot of data to delete!
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return int
     * @comment implicitly tested
     */
    public function deleteByQueueMail(
        \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
    ): int {

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_rkwmailer_domain_model_queuerecipient');
        
        return $queryBuilder
            ->delete('tx_rkwmailer_domain_model_queuerecipient')
            ->where(
                $queryBuilder->expr()->eq(
                    'queue_mail',
                    $queryBuilder->createNamedParameter($queueMail->getUid(), \PDO::PARAM_INT))
            )
            ->execute();
    }

}