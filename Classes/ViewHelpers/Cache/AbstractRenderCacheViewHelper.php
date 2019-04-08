<?php

namespace RKW\RkwMailer\ViewHelpers\Cache;
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
 * GetRenderCacheViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class AbstractRenderCacheViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Logger
     *
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;


    /**
     * Replaces marker in content
     *
     * @param string $content
     * @param array $marker
     * @return string
     */
    public function replaceMarker ($content, $marker = [])
    {
        // replace marker
        foreach ($marker as $key => $value) {

            $contentBefore = $content;
            $content = str_replace('---' . $key . '---', $value, $content);
            $content = str_replace('###' . $key . '###', $value, $content);
            $content = str_replace('{' . $key . '}', $value, $content);

            if ($contentBefore != $content) {
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::DEBUG, sprintf('Replaced key "%s" with value "%s".', $key, str_replace("\n", '', print_r($value, true))));
            }
        }

        return $content;
        //===
    }
    
    

    /**
     * Returns logger instance
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @param boolean $isPlaintext
     * @param string $additionalIdentifier
     * @return string
     */
    protected function getIdentifier(\RKW\RkwMailer\Domain\Model\QueueMail $queueMail = null, $isPlaintext = false, $additionalIdentifier = '')
    {
       return intval($queueMail->getUid()) . '_' . ($isPlaintext ? 'plaintext' : 'html') . '_' . $additionalIdentifier;
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