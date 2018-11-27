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
 * QueueMailRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class QueueMailRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    public function initializeObject()
    {
        $this->defaultQuerySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');
        $this->defaultQuerySettings->setRespectStoragePage(false);
    }


    protected $defaultOrderings = array(
        'sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
    );


    /**
     * findByStatusWaitingOrSending
     * ordered by tstampRealSending and sorting and then priority
     *
     * @param integer $limit
     * @return \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findByStatusWaitingOrSending($limit)
    {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->logicalOr(
                    $query->equals('status', '2'),
                    $query->equals('status', '3')
                ),
                $query->lessThanOrEqual('tstampRealSending', time()))
        )
            ->setOrderings(
                array(
                    'pipeline'          => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
                    'tstampRealSending' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
                    'sorting'           => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
                    'priority'          => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
                )
            );

        if ($limit > 0) {
            $query->setLimit(intval($limit));
        }

        return $query->execute();
        //====
    }


    /**
     * finds all queue mails that are older than $cleanupTimestamp
     *
     * @param integer $cleanupTimestamp
     * @param array $type
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findAllOldMails($cleanupTimestamp, $type = array())
    {

        $query = $this->createQuery();

        if (
            (is_array($type))
            && (!empty($type))
        ) {

            $query->matching(
                $query->logicalAnd(
                    $query->lessThanOrEqual('tstampSendFinish', $cleanupTimestamp),
                    $query->logicalAnd(
                        $query->greaterThanOrEqual('status', 4),
                        $query->lessThan('status', 99),
                        $query->equals('pipeline', 0)
                    ),
                    $query->in('type', array($type))
                )
            );

        } else {

            $query->matching(
                $query->logicalAnd(
                    $query->lessThanOrEqual('tstampSendFinish', $cleanupTimestamp),
                    $query->logicalAnd(
                        $query->greaterThanOrEqual('status', 4),
                        $query->lessThan('status', 99),
                        $query->equals('pipeline', 0)
                    )
                )
            );
        }

        return $query->execute();
        //===
    }

    /**
     * findAllByCreateDateAndType
     *
     * @param array $spaceOfTime
     * @param integer $mailType
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findAllByCreateDateAndType($spaceOfTime = null, $mailType = -1)
    {

        $query = $this->createQuery();
        if (
            ($mailType == -1)
            && ($spaceOfTime)
        ) {
            $query->matching(
                $query->logicalAnd(
                    $query->greaterThanOrEqual('crdate', $spaceOfTime['from']),
                    $query->lessThanOrEqual('crdate', $spaceOfTime['to'])
                )
            );


        } elseif (
            ($mailType > -1)
            && ($spaceOfTime)
        ) {

            $query->matching(
                $query->logicalAnd(
                    $query->greaterThanOrEqual('crdate', $spaceOfTime['from']),
                    $query->lessThanOrEqual('crdate', $spaceOfTime['to']),
                    $query->equals('type', $mailType)
                )
            );


        } elseif ($mailType > -1) {
            $query->matching(
                $query->equals('type', $mailType)
            );
        }

        $query->setOrderings(
            array(
                'status'            => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
                'tstampFavSending'  => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
                'tstampRealSending' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
            )
        );

        return $query->execute();
        //===
    }


    /**
     * countAllSentMailsGroupByType
     * fetch all with status = 4 for "sent"
     *
     * @param array $spaceOfTime
     * @return array $queueMailCount
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function countAllSentMailsGroupByType($spaceOfTime = null)
    {

        // iterate all types from typoscript and count matches by findAll on mails
        $queueMailCountByType = array();
        foreach (array(0, 1, 2) as $type) {

            $query = $this->createQuery();

            if ($spaceOfTime) {
                $query->matching(
                    $query->logicalAnd(
                        $query->greaterThanOrEqual('tstampRealSending', $spaceOfTime['from']),
                        $query->lessThanOrEqual('tstampRealSending', $spaceOfTime['to']),
                        $query->equals('type', intval($type)),
                        $query->logicalOr(
                            $query->equals('status', '3'),
                            $query->equals('status', '4')
                        )
                    )
                );
            }

            $typeCount = $query->execute()->count();
            $queueMailCountByType[$type] = $typeCount;
        }

        return $queueMailCountByType;
        //====
    }


}