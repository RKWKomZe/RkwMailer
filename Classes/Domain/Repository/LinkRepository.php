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
 * LinkRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class LinkRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    public function initializeObject()
    {

        $this->defaultQuerySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');
        $this->defaultQuerySettings->setRespectStoragePage(false);
    }




    /**
     * findAllLastBounced
     *
     * @param
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|NULL
     */
    public function findAllWithStatistics()
    {

        // SELECT SUM(`click_count`) FROM `tx_rkwmailer_domain_model_statisticopening` WHERE link = 4

        $query = $this->createQuery();
        $query->statement('
            SELECT tx_rkwmailer_domain_model_link.url, SUM(click_count) FROM tx_rkwmailer_domain_model_statisticopening 
            LEFT JOIN tx_rkwmailer_domain_model_link
                ON tx_rkwmailer_domain_model_link.uid = tx_rkwmailer_domain_model_statisticopening.link
            WHERE tx_rkwmailer_domain_model_statisticopening.pixel = 0
            GROUP BY tx_rkwmailer_domain_model_statisticopening.link
        ');


        return $query->execute();
        //====
    }

}