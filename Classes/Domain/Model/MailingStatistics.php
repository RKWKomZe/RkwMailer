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
     * status
     *
     * @var integer
     */
    protected $status = 0;

    
    /**
     * type
     *
     * @var integer
     */
    protected $type = 0;

    
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
     * delivered
     *
     * @var integer
     */
    protected $delivered = 0;

    
    /**
     * failed
     *
     * @var integer
     */
    protected $failed = 0;

    
    /**
     * deferred
     *
     * @var integer
     */
    protected $deferred = 0;


    /**
     * sendingBounced
     *
     * @var integer
     */
    protected $bounced = 0;


    /**
     * tstampFavSending
     *
     * @var integer
     */
    protected $tstampFavSending = 0;

    
    /**
     * tstampRealSending
     *
     * @var integer
     */
    protected $tstampRealSending = 0;

    
    /**
     * tstampFinishedSending
     *
     * @var integer
     */
    protected $tstampFinishedSending = 0;
    
    
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
     * Returns the status
     *
     * @return int $status
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Sets the status
     *
     * @param int $status
     * @return void
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }


    /**
     * Returns the type
     *
     * @return int $type
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * Sets the type
     *
     * @param int $type
     * @return void
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }
    
    
    /**
     * Returns the totalRecipients
     *
     * @return int $totalRecipients
     */
    public function getTotalRecipients(): int
    {
        return $this->totalRecipients;
    }

    /**
     * Sets the totalRecipients
     *
     * @param int $totalRecipients
     * @return void
     */
    public function setTotalRecipients(int $totalRecipients): void
    {
        $this->totalRecipients = $totalRecipients;
    }

    /**
     * Returns the totalSent
     *
     * @return int $totalSent
     */
    public function getTotalSent(): int
    {
        return $this->totalSent;
    }

    /**
     * Sets the totalSent
     *
     * @param int $totalSent
     * @return void
     */
    public function setTotalSent(int $totalSent): void
    {
        $this->totalSent = $totalSent;
    }

    
    /**
     * Returns the delivered
     *
     * @return int $delivered
     */
    public function getDelivered(): int
    {
        return $this->delivered;
    }

    /**
     * Sets the delivered
     *
     * @param int $delivered
     * @return void
     */
    public function setDelivered(int $delivered): void
    {
        $this->delivered = $delivered;
    }
    

    /**
     * Returns the failed
     *
     * @return int $failed
     */
    public function getFailed(): int
    {
        return $this->failed;
    }

    /**
     * Sets the failed
     *
     * @param int $failed
     * @return void
     */
    public function setFailed(int $failed): void
    {
        $this->failed = $failed;
    }

    
    /**
     * Returns the deferred
     *
     * @return int $deferred
     */
    public function getDeferred(): int
    {
        return $this->deferred;
    }

    /**
     * Sets the deferred
     *
     * @param int $deferred
     * @return void
     */
    public function setDeferred(int $deferred): void
    {
        $this->deferred = $deferred;
    }


    /**
     * Returns the bounced
     *
     * @return int $bounced
     */
    public function getBounced(): int
    {
        return $this->bounced;
    }

    /**
     * Sets the bounced
     *
     * @param int $bounced
     * @return void
     */
    public function setBounced(int $bounced): void
    {
        $this->bounced = $bounced;
    }


    /**
     * Returns the tstampFavSending
     *
     * @return int $tstampFavSending
     */
    public function getTstampFavSending(): int
    {
        return $this->tstampFavSending;
    }

    /**
     * Sets the tstampFavSending
     *
     * @param integer $tstampFavSending
     * @return void
     */
    public function setTstampFavSending(int $tstampFavSending): void
    {
        $this->tstampFavSending = $tstampFavSending;
    }

    
    /**
     * Returns the tstampRealSending
     *
     * @return int $tstampRealSending
     */
    public function getTstampRealSending(): int
    {
        return $this->tstampRealSending;
    }

    /**
     * Sets the tstampRealSending
     *
     * @param int $tstampRealSending
     * @return void
     */
    public function setTstampRealSending(int $tstampRealSending): void
    {
        $this->tstampRealSending = $tstampRealSending;
    }

    
    /**
     * Returns the tstampFinishedSending
     *
     * @return int $tstampFinishedSending
     */
    public function getTstampFinishedSending(): int
    {
        return $this->tstampFinishedSending;
    }

    /**
     * Sets the tstampFinishedSending
     *
     * @param integer $tstampFinishedSending
     * @return void
     */
    public function setTstampFinishedSending (int $tstampFinishedSending): void
    {
        $this->tstampFinishedSending = $tstampFinishedSending;
    }

}