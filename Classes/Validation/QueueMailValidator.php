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

/**
 * QueueMailValidator
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
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
     * @return boolean
     */
    public function validate(\RKW\RkwMailer\Domain\Model\QueueMail $queueMail)
    {

        $valid = true;
        if (!$queueMail->getFromName()) {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('No FromName is set (Mail UID "%s").', $queueMail->getUid()));
            $valid = false;
        }

        if (!$queueMail->getFromAddress()) {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('No FromAddress is set (Mail UID "%s").', $queueMail->getUid()));
            $valid = false;
        }

        if (!$queueMail->getSubject()) {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('No Subject is set (Mail UID "%s").', $queueMail->getUid()));
        }

        /**
        if (!$queueMail->getPlaintextTemplate() && !$queueMail->getHtmlTemplate()) {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('No Template is set (Mail UID "%s").', $queueMail->getUid()));
            $valid = false;
        }
        */

        return $valid;
        //===

    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {

        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Log\\LogManager')->getLogger(__CLASS__);
        }

        return $this->logger;
        //===
    }

}