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
 * QueueRecipient
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MailCache
{


    /**
     * Cache
     *
     * @var \TYPO3\CMS\Core\Cache\CacheManager
     * @inject
     */
    protected $cache;


    /**
     * Logger
     *
     * @var \TYPO3\CMS\Core\Log\LogManager
     * @inject
     */
    protected $logger;


    /**
     * plaintextBody
     *
     * @var string
     */
    protected $plaintextBody = '';

    /**
     * htmlBody
     *
     * @var string
     */
    protected $htmlBody = '';

    /**
     * calendarBody
     *
     * @var string
     */
    protected $calendarBody = '';



    /**
     * Constructor
     *
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     */
    public function __construct()
    {
        $this->cache = $this->cache->getCache('rkw_mailer');
        $this->logger = $this->logger->getLogger(__CLASS__);
    }


    /**
     * Returns the plaintextBody
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @return string $plaintextBody
     */
    public function getPlaintextBody($queueMail, $queueRecipient)
    {
        return $this->plaintextBody;
    }

    /**
     * Sets the plaintextBody
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @param string $plaintextBody
     * @return void
     */
    public function setPlaintextBody($queueMail, $queueRecipient, $plaintextBody)
    {
        $this->plaintextBody = $plaintextBody;
    }


    /**
     * Returns the htmlBody
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @return string $htmlBody
     */
    public function getHtmlBody($queueMail, $queueRecipient)
    {
        return $this->htmlBody;
    }


    /**
     * Sets the htmlBody
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @param string $htmlBody
     * @return void
     */
    public function setHtmlBody($queueMail, $queueRecipient, $htmlBody)
    {
        $this->htmlBody = $htmlBody;
    }


    /**
     * Returns the calendarBody
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @return string $calendarBody
     */
    public function getCalendarBody($queueMail, $queueRecipient)
    {
        return $this->calendarBody;
    }

    /**
     * Sets the calendarBody
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @param string $calendarBody
     * @return void
     */
    public function setCalendarBody($queueMail, $queueRecipient, $calendarBody)
    {
        $this->calendarBody = $calendarBody;
    }



    /**
     * Returns CacheIdentifier
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @param string $property
     * @return string
     */
    protected function getCacheIdentifier($queueMail, $queueRecipient, $property)
    {
        return sha1(intval($queueMail->getUid()) . '_' . intval($queueRecipient->getUid()) . '_' . $property);
        //===
    }


    /**
     * Returns cached content
     *
     * @param string $cacheIdentifier
     * @return string | null
     */
    protected function getCache($cacheIdentifier)
    {

        if ($this->cache->has($cacheIdentifier)) {

            // get cached content
            $this->logger->log(\TYPO3\CMS\Core\Log\LogLevel::DEBUG, sprintf('Getting MailBody-Cache for identifier "%s".', $cacheIdentifier));
            return $this->cache->get($cacheIdentifier);
            //===
        }

        return null;
        //===

    }

    /**
     * Returns cached content
     *
     * @param string $cacheIdentifier
     * @param mixed $value
     * @return string | null
     */
    protected function setCache($cacheIdentifier, $value)
    {

        $this->logger->log(\TYPO3\CMS\Core\Log\LogLevel::DEBUG, sprintf('Setting MailBody-Cache for identifier "%s".', $cacheIdentifier));
        $this->cache->set(
            $cacheIdentifier,
            $value,
            array(
                'tx_rkwmailer_mailbody',
            ),
            86400
        );

    }

}