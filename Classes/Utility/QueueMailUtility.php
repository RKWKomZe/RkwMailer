<?php

namespace RKW\RkwMailer\Utility;

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

use RKW\RkwMailer\Domain\Model\MailingStatistics;
use RKW\RkwMailer\Domain\Model\QueueMail;
use RKW\RkwMailer\Domain\Model\QueueRecipient;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\BackendUser;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * QueueMailUtility
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class QueueMailUtility
{

    /**
     * Get a QueueMail-object with all initial properties set
     *
     * @param int $storagePid
     * @return \RKW\RkwMailer\Domain\Model\QueueMail
     */
    public static function initQueueMail (
        int $storagePid = 0 
    ): QueueMail {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail*/
        $queueMail = GeneralUtility::makeInstance(QueueMail::class);
        
        /** @var \RKW\RkwMailer\Domain\Model\MailingStatistics $mailingStatistics */
        $mailingStatistics = GeneralUtility::makeInstance(MailingStatistics::class);
        
        $queueMail->setPid($storagePid);
        $queueMail->setSettingsPid(intval($GLOBALS['TSFE']->id));
        $queueMail->setMailingStatistics($mailingStatistics);
        $queueMail->getMailingStatistics()->setTstampFavSending(time());

        // set defaults
        $queueMail->setFromName($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName']);
        $queueMail->setFromAddress($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress']);
        $queueMail->setReplyAddress(
            $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailReplyAddress'] ? 
            $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailReplyAddress'] : 
            $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress']
        );
        $queueMail->setReturnPath(
            $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailReturnAddress'] ? 
            $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailReturnAddress'] : 
            $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress']
        );

        return $queueMail;
    }
  
    
}