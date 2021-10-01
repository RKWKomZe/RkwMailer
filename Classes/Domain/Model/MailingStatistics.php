<?php

namespace RKW\RkwMailer\Domain\Model;

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
 * MailingStatistics
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MailingStatistics extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
  
    /**
     * queueMail
     *
     * @var \RKW\RkwMailer\Domain\Model\QueueMail
     */
    protected $queueMail;

    /**
     * subject
     *
     * @var string
     */
    protected $subject;
    

    /**
     * total
     *
     * @var integer
     */
    protected $totalRecipients = 0;

    /**
     * totalSent
     *
     * @var integer
     */
    protected $totalSent = 0;

    
    /**
     * sendingSuccessful
     *
     * @var integer
     */
    protected $sendingSuccessful = 0;

    
    /**
     * sendingFailed
     *
     * @var integer
     */
    protected $sendingFailed = 0;

    
    /**
     * sendingDeferred
     *
     * @var integer
     */
    protected $sendingDeferred = 0;


    /**
     * sendingBounced
     *
     * @var integer
     */
    protected $sendingBounced = 0;
    
    
    /**
     * Returns the queueMail
     *
     * @return \RKW\RkwMailer\Domain\Model\QueueMail
     */
    public function getQueueMail(): \RKW\RkwMailer\Domain\Model\QueueMail
    {
        return $this->queueMail;
    }

    /**
     * Sets the queueMail
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return void
     */
    public function setQueueMail($queueMail): void
    {
        $this->queueMail = $queueMail;
    }

    
    /**
     * Returns the totalRecipients
     *
     * @return int $totalRecipients
     */
    public function getTotalRecipients()
    {
        return $this->totalRecipients;
    }

    /**
     * Sets the totalRecipients
     *
     * @param int $count
     * @return void
     */
    public function setTotalRecipients($totalRecipients)
    {
        $this->totalRecipients = $totalRecipients;
    }
}