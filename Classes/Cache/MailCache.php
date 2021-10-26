<?php

namespace RKW\RkwMailer\Cache;

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
 * MailCache
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MailCache extends AbstractCache
{
    
    /**
     * Returns the plaintextBody
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @return string $plaintextBody
     * @throws \RKW\RkwMailer\Exception
     */
    public function getPlaintextBody(
        \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
    ): string {

        $cacheIdentifier = $this->getIdentifier($queueRecipient, 'plaintext');
        return $this->getContent($cacheIdentifier);
    }


    /**
     * Sets the plaintextBody
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @param string                                     $plaintextBody
     * @return void
     * @throws \RKW\RkwMailer\Exception
     */
    public function setPlaintextBody(
        \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient, 
        string $plaintextBody
    ): void {
        
        $cacheIdentifier = $this->getIdentifier($queueRecipient, 'plaintext');
        $this->setContent($cacheIdentifier, $plaintextBody);
    }


    /**
     * Returns the htmlBody
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @return string $htmlBody
     * @throws \RKW\RkwMailer\Exception
     */
    public function getHtmlBody(\RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient): string 
    {
        $cacheIdentifier = $this->getIdentifier($queueRecipient, 'html');
        return $this->getContent($cacheIdentifier);
    }


    /**
     * Sets the htmlBody
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @param string                                     $htmlBody
     * @return void
     * @throws \RKW\RkwMailer\Exception
     */
    public function setHtmlBody(
        \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient, 
        string $htmlBody): void {
        
        $cacheIdentifier = $this->getIdentifier($queueRecipient, 'html');
        $this->setContent($cacheIdentifier, $htmlBody);
    }


    /**
     * Returns the calendarBody
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @return string $calendarBody
     * @throws \RKW\RkwMailer\Exception
     */
    public function getCalendarBody(\RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient): string
    {
        $cacheIdentifier = $this->getIdentifier($queueRecipient, 'calendar');
        return $this->getContent($cacheIdentifier);
    }


    /**
     * Sets the calendarBody
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @param string $calendarBody
     * @return void
     * @throws \RKW\RkwMailer\Exception
     */
    public function setCalendarBody(
        \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient, 
        string $calendarBody
    ): void {
        
        $cacheIdentifier = $this->getIdentifier($queueRecipient, 'calendar');
        $this->setContent($cacheIdentifier, $calendarBody);
    }



    /**
     * Returns cacheIdentifier
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @param string $property
     * @return string
     * @throws \RKW\RkwMailer\Exception
     */
    public function getIdentifier(
        \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient, 
        string $property
    ) : string {

        if ($queueRecipient->_isNew()) {
            throw new \RKW\RkwMailer\Exception (
                'The queueRecipient-object has to be persisted before it can be used.',
                1634308452
            );
        }
        
        return 'MailCache_' . intval($queueRecipient->getUid()) . '_' . $property;
    }


    

}