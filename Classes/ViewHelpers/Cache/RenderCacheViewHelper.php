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

use RKW\RkwMailer\Cache\RenderCache;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

/**
 * RenderCacheViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RenderCacheViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{

    use CompileWithContentArgumentAndRenderStatic;

    
    /**
     * @var bool
     */
    protected $escapeOutput = false;


    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        $this->registerArgument('value', 'string', 'String to work on.');
        $this->registerArgument('queueMail', '\RKW\RkwMailer\Domain\Model\QueueMail', 'The queueMail-object.');
        $this->registerArgument('isPlaintext', 'boolean', 'Whether the content is plaintext or not.');
        $this->registerArgument('additionalIdentifier', 'string', 'String which is appended to the cache-identifier.');
        $this->registerArgument('nonCachedMarkers', 'array', 'Markers that are to be replaced after(!) the content has been cached. They have to be set via ###marker### or ---marker---');
    }


    /**
     * Cache for rendered fragments
     **
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param \TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(
        array $arguments, 
        \Closure $renderChildrenClosure, 
        RenderingContextInterface $renderingContext
    ): string {
        
        $queueMail = $arguments['queueMail'];
        $isPlaintext = (bool) ($arguments['isPlaintext']);
        $additionalIdentifier = ($arguments['additionalIdentifier'] ? $arguments['additionalIdentifier'] : '');
        $nonCachedMarkers = ($arguments['nonCachedMarkers'] ? $arguments['nonCachedMarkers'] : []);
        
        try {
            
            if ($queueMail instanceof \RKW\RkwMailer\Domain\Model\QueueMail) {

                /** @var \RKW\RkwMailer\Cache\RenderCache $cache */
                $cache = GeneralUtility::makeInstance(RenderCache::class);
                $cacheIdentifier = $cache->getIdentifier($queueMail, $isPlaintext, $additionalIdentifier);
                
                // check if cache has to be build
                if (! $value = $cache->getContent($cacheIdentifier)) {
                    $value = $renderChildrenClosure();
                    $cache->setContent($cacheIdentifier, $value);
                }

                // replace nonCachedMarkers
                return $cache->replaceMarkers($value, $nonCachedMarkers);
            }
            
        } catch (\Exception $e) {

            $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
            $logger->log(
                LogLevel::ERROR,
                sprintf(
                    'Error while trying to cache content: %s',
                    $e->getMessage()
                )
            );
        }

        return $renderChildrenClosure();
    }
}