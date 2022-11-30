<?php

namespace RKW\RkwMailer\Validation;
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

use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;

/**
 * QueueRecipientValidator
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @todo write tests
 */
class QueueRecipientValidator implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;


    /**
     * validateQueueRecipient
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @return boolean
     */
    public function validate(
        \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
    ): bool {

        $valid = true;

        if (!$queueRecipient->getEmail()) {
            $this->getLogger()->log(
                LogLevel::ERROR,
                sprintf(
                    'No email-address is set (queueRecipient with uid %s).',
                    $queueRecipient->getUid()
                )
            );
            $valid = false;
        }

        if (!EmailValidator::validateEmail($queueRecipient->getEmail())) {
            $this->getLogger()->log(
                LogLevel::ERROR,
                sprintf(
                    'Email-address is not valid (queueRecipient with uid %s).',
                    $queueRecipient->getUid()
                )
            );

            $valid = false;
        }

        if (!$queueRecipient->getFirstName()) {
            $this->getLogger()->log(
                LogLevel::INFO,
                sprintf(
                    'No firstName is set (queueRecipient with uid %s).',
                    $queueRecipient->getUid()
                )
            );
        }

        if (!$queueRecipient->getLastName()) {
            $this->getLogger()->log(
                LogLevel::INFO,
                sprintf(
                    'No lastName is set (queueRecipient with uid %s).',
                    $queueRecipient->getUid()
                )
            );
        }

        return $valid;
    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {

        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }

}
