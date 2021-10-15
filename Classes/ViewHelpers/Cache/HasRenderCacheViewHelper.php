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
 * hasRenderCacheViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @toDo: Write tests
 */
class HasRenderCacheViewHelper extends AbstractRenderCacheViewHelper
{


    /**
     * Caches parts of rendered mail based on queueMail-id
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @param boolean $isPlaintext
     * @param string $additionalIdentifier
     * @return bool
     */
    public function render(
        \RKW\RkwMailer\Domain\Model\QueueMail $queueMail = null, 
        bool $isPlaintext = false, 
        string $additionalIdentifier = ''
    ): bool {

        if ($queueMail instanceof \RKW\RkwMailer\Domain\Model\QueueMail) {

            /** @var \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend $cacheManager */
            $cacheManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(CacheManager::class)->getCache('rkw_mailer');
            $cacheIdentifier = $this->getIdentifier($queueMail, $isPlaintext , $additionalIdentifier);

            if ($cacheManager->has($cacheIdentifier)) {

                $this->getLogger()->log(
                    LogLevel::DEBUG, 
                    sprintf(
                        'ViewHelperCache found for cache-identifier "%s".', 
                        $cacheIdentifier
                    )
                );
                
                return true;
            }
        }

        return false;
    }

}