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
 * StatisticMailRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class StatisticMailRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    public function initializeObject()
    {

        $this->defaultQuerySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');
        $this->defaultQuerySettings->setRespectStoragePage(false);
    }


    /**
     * findAllOfSentMails
     * order by mail_id
     *
     * @param array $spaceOfTime
     * @param integer $mailType
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findAllSentMails($spaceOfTime, $mailType = -1)
    {
        $query = $this->createQuery();

        $filterConstraint[] = $query->logicalAnd(
            $query->logicalOr(
                $query->equals('queueMail.status', 3),
                $query->equals('queueMail.status', 4)
            ),
            $query->logicalAnd(
                $query->greaterThanOrEqual('queueMail.tstamp_real_sending', intval($spaceOfTime['from'])),
                $query->lessThanOrEqual('queueMail.tstamp_real_sending', intval($spaceOfTime['to']))
            )
        );

        if ($mailType >= 0) {
            $filterConstraint[] = $query->logicalAnd(
                $query->equals('queueMail.type', intval($mailType))
            );
        }

        $query->matching(
            $query->logicalAnd($filterConstraint)
        );

        $query->setOrderings(
            array(
                'queueMail.status'              => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
                'queueMail.tstamp_fav_sending'  => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
                'queueMail.tstamp_real_sending' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
            )
        );

        return $query->execute();
        //===

    }


}