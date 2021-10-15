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

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Log\LogLevel;

/**
 * SetRenderCacheViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @toDo: Write tests
 */
class SetRenderCacheViewHelper extends AbstractRenderCacheViewHelper
{


    /**
     * Caches parts of rendered mail based on queueMail-id
     *
     * @param string $value
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @param boolean $isPlaintext
     * @param string $additionalIdentifier
     * @param array $marker
     * @return string
     */
    public function render(
        string $value = null, 
        \RKW\RkwMailer\Domain\Model\QueueMail $queueMail = null, 
        bool $isPlaintext = false, 
        string $additionalIdentifier = '', 
        array $marker = []
    ): string {

        if ($value === null) {
            $value = $this->renderChildren();
        }

        if (! is_string($value)) {
            return $value;
        }

        if ($queueMail instanceof \RKW\RkwMailer\Domain\Model\QueueMail) {

            /** @var \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend $cacheManager */
            $cacheManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(CacheManager::class)->getCache('rkw_mailer');
            $cacheIdentifier = $this->getIdentifier($queueMail, $isPlaintext , $additionalIdentifier);

            $this->getLogger()->log(
                LogLevel::DEBUG, 
                sprintf(
                    'Setting ViewHelperCache for identifier "%s".', 
                    $cacheIdentifier
                )
            );
            $cacheManager->set(
                $cacheIdentifier,
                $value,
                array(
                    'tx_rkwmailer_rendering',
                    'tx_rkwmailer_rendering_' . intval($queueMail->getUid()),
                    'tx_rkwmailer_rendering_' . ($isPlaintext ? 'plaintext' : 'html'),
                    'tx_rkwmailer_rendering_' . $cacheIdentifier
                ),
                86400
            );
        }

        // replace marker - but do not cache the replaced version!
        return $this->replaceMarker($value, $marker);

    }



}