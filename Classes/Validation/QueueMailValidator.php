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
 * QueueMailValidator
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @todo write tests
 */
class QueueMailValidator implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;


    /**
     * validateQueueMail
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return bool
     */
    public function validate(
        \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
    ): bool {

        $valid = true;
        if (!$queueMail->getFromName()) {
            $this->getLogger()->log(LogLevel::ERROR,
                sprintf(
                    'No fromName is set (queueMail with uid %s).',
                    $queueMail->getUid()
                )
            );
            $valid = false;
        }

        if (!$queueMail->getFromAddress()) {
            $this->getLogger()->log(
                LogLevel::ERROR,
                sprintf(
                    'No fromAddress is set (queueMail with uid %s).',
                    $queueMail->getUid()
                )
            );
            $valid = false;
        }

        if (!$queueMail->getSubject()) {
            $this->getLogger()->log(
                LogLevel::WARNING,
                sprintf(
                    'No Subject is set (queueMail with uid %s).',
                    $queueMail->getUid()
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
