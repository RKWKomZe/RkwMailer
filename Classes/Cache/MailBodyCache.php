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
 * MailBodyCache
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MailBodyCache
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
    protected $logManager;

    /**
     * Logger
     *
     * @var \TYPO3\CMS\Core\Log\Logger
     * @inject
     */
    protected $logger;


    /**
     * Constructor
     *
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     */
    public function __construct()
    {
        $this->cache = $this->cache->getCache('rkw_mailer');
        $this->logger = $this->logManager->getLogger(__CLASS__);
    }


    /**
     * Returns the plaintextBody
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @return string $plaintextBody
     */
    public function getPlaintextBody($queueRecipient)
    {
        $cacheIdentifier = $this->getCacheIdentifier($queueRecipient, 'plaintext');
        return $this->getCache($cacheIdentifier);
        //===
    }

    /**
     * Sets the plaintextBody
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @param string $plaintextBody
     * @return void
     */
    public function setPlaintextBody($queueRecipient, $plaintextBody)
    {
        $cacheIdentifier = $this->getCacheIdentifier($queueRecipient, 'plaintext');
        $this->setCache($cacheIdentifier, $plaintextBody);
    }


    /**
     * Returns the htmlBody
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @return string $htmlBody
     */
    public function getHtmlBody($queueRecipient)
    {
        $cacheIdentifier = $this->getCacheIdentifier($queueRecipient, 'html');
        return $this->getCache($cacheIdentifier);
        //===
    }


    /**
     * Sets the htmlBody
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @param string $htmlBody
     * @return void
     */
    public function setHtmlBody($queueRecipient, $htmlBody)
    {
        $cacheIdentifier = $this->getCacheIdentifier($queueRecipient, 'html');
        $this->setCache($cacheIdentifier, $htmlBody);
    }


    /**
     * Returns the calendarBody
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @return string $calendarBody
     */
    public function getCalendarBody($queueRecipient)
    {
        $cacheIdentifier = $this->getCacheIdentifier($queueRecipient, 'calendar');
        return $this->getCache($cacheIdentifier);
        //===
    }

    /**
     * Sets the calendarBody
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @param string $calendarBody
     * @return void
     */
    public function setCalendarBody($queueRecipient, $calendarBody)
    {
        $cacheIdentifier = $this->getCacheIdentifier($queueRecipient, 'calendar');
        $this->setCache($cacheIdentifier, $calendarBody);
    }



    /**
     * Returns CacheIdentifier
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @param string $property
     * @return string
     */
    protected function getCacheIdentifier($queueRecipient, $property)
    {
        return sha1(intval($queueRecipient->getUid()) . '_' . $property);
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