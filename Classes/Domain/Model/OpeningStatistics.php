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
 * OpeningStatistics
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OpeningStatistics extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    
    /**
     * queueMail
     *
     * @var \RKW\RkwMailer\Domain\Model\QueueMail
     */
    protected $queueMail;

    /**
     * queueRecipient
     *
     * @var \RKW\RkwMailer\Domain\Model\QueueRecipient
     */
    protected $queueRecipient;
    
    /**
     * hash
     *
     * @var string
     */
    protected $hash = '';

    /**
     * counter
     *
     * @var integer
     */
    protected $counter = 0;
    
    
    /**
     * Returns the queueMail
     *
     * @return \RKW\RkwMailer\Domain\Model\QueueMail
     */
    public function getQueueMail()
    {
        return $this->queueMail;
    }

    /**
     * Sets the queueMail
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return void
     */
    public function setQueueMail(\RKW\RkwMailer\Domain\Model\QueueMail $queueMail): void
    {
        $this->queueMail = $queueMail;
    }

    
    /**
     * Returns the queueRecipient
     *
     * @return \RKW\RkwMailer\Domain\Model\QueueRecipient
     */
    public function getQueueRecipient()
    {
        return $this->queueRecipient;
    }

    /**
     * Sets the queueRecipient
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @return void
     */
    public function setQueueRecipient(\RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient): void
    {
        $this->queueRecipient = $queueRecipient;
    }
    
    
    /**
     * Returns the hash
     *
     * @return string $hash
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * Sets the hash
     *
     * @param string $hash
     * @return void
     */
    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }


    /**
     * Returns the counter
     *
     * @return int $counter
     */
    public function getCounter(): int
    {
        return $this->counter;
    }

    /**
     * Sets the counter
     *
     * @param int $counter
     * @return void
     */
    public function setCounter(int $counter): void
    {
        $this->counter = $counter;
    }
}