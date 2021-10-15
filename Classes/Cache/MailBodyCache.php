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

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
     * @var \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend
     */
    protected $cache;


    /**
     * Logger
     *
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('rkw_mailer');
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }


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

        $cacheIdentifier = $this->getCacheIdentifier($queueRecipient, 'plaintext');
        return $this->getCache($cacheIdentifier);
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
        $cacheIdentifier = $this->getCacheIdentifier($queueRecipient, 'plaintext');
        $this->setCache($cacheIdentifier, $plaintextBody);
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
        $cacheIdentifier = $this->getCacheIdentifier($queueRecipient, 'html');
        return $this->getCache($cacheIdentifier);
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
        \RKW\RkwMailer\Domain\Model\QueueRecipient$queueRecipient, 
        string $htmlBody): void {
        $cacheIdentifier = $this->getCacheIdentifier($queueRecipient, 'html');
        $this->setCache($cacheIdentifier, $htmlBody);
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
        $cacheIdentifier = $this->getCacheIdentifier($queueRecipient, 'calendar');
        return $this->getCache($cacheIdentifier);
    }


    /**
     * Sets the calendarBody
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @param string                                     $calendarBody
     * @return void
     * @throws \RKW\RkwMailer\Exception
     */
    public function setCalendarBody(
        \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient, 
        string $calendarBody
    ): void {
        $cacheIdentifier = $this->getCacheIdentifier($queueRecipient, 'calendar');
        $this->setCache($cacheIdentifier, $calendarBody);
    }


    /**
     * Clear cached content
     */
    public function clearCache(): void
    {
        $this->logger->log(LogLevel::DEBUG, 'Flushing MailBodyCache');
        $this->cache->flush();
    }


    /**
     * Returns CacheIdentifier
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @param string                                     $property
     * @return string
     * @throws \RKW\RkwMailer\Exception
     */
    protected function getCacheIdentifier(
        \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient, 
        string $property
    ) : string {

        if ($queueRecipient->_isNew()) {
            throw new \RKW\RkwMailer\Exception (
                'The queueRecipient-object has to be persisted before it can be used.',
                1634308452
            );
        }
        
        return 'MailBodyCache_' . intval($queueRecipient->getUid()) . '_' . $property;
    }


    /**
     * Returns cached content
     *
     * @param string $cacheIdentifier
     * @return string
     */
    protected function getCache(string $cacheIdentifier): string
    {

        if ($this->cache->has($cacheIdentifier)) {

            // get cached content
            $this->logger->log(
                LogLevel::DEBUG, 
                sprintf(
                    'Getting MailBodyCache for identifier "%s".', 
                    $cacheIdentifier
                )
            );
            return $this->cache->get($cacheIdentifier);
        }

        return '';

    }

    /**
     * Sets cache content
     *
     * @param string $cacheIdentifier
     * @param mixed $value
     * @return void
     */
    protected function setCache(string $cacheIdentifier, $value): void
    {

        $this->logger->log(
            LogLevel::DEBUG, 
            sprintf(
                'Setting MailBodyCache for identifier "%s".', 
                $cacheIdentifier
            )
        );
        
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