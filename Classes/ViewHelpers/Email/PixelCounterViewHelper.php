<?php

namespace RKW\RkwMailer\ViewHelpers\Email;

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

use RKW\RkwMailer\Domain\Model\QueueMail;
use RKW\RkwMailer\Domain\Model\QueueRecipient;
use RKW\RkwMailer\UriBuilder\EmailUriBuilder;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Class PixelCounterViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PixelCounterViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{
    
    use CompileWithRenderStatic;
    
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
        $this->registerArgument('queueMail', QueueMail::class, 'QueueMail-object for counter');
        $this->registerArgument('queueRecipient', QueueRecipient::class, 'QueueRecipient-object for counter');
    }
    

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param \TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(
        array $arguments, 
        \Closure $renderChildrenClosure, 
        RenderingContextInterface $renderingContext
    ){
        
        $queueMail = $arguments['queueMail'];
        $queueRecipient = $arguments['queueRecipient'];
        
        try {

            $settings = self::getSettings();
            $counterPixelPid = intval($settings['counterPixelPid']);

            if (
                ($counterPixelPid > 0)
                && ($queueRecipient > 0)
                && ($queueMail > 0)
            ) {

                // load EmailUriBuilder
                $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

                /** @var \RKW\RkwMailer\UriBuilder\EmailUriBuilder $uriBuilder */
                $uriBuilder = $objectManager->get(EmailUriBuilder::class);
                $uriBuilder->reset();

                // build link to controller action with needed params
                $uriBuilder->setTargetPageUid($counterPixelPid)
                    ->setNoCache(true)
                    ->setArguments(
                        array(
                            'tx_rkwmailer_rkwmailer[uid]' => intval($queueRecipient->getUid()),
                            'tx_rkwmailer_rkwmailer[mid]' => intval($queueMail->getUid()),
                        )
                    );

                return '<img src="' . urldecode(
                    $uriBuilder->uriFor(
                        'opening', 
                        array(), 
                        'Tracking', 
                        'rkwmailer', 
                        'Rkwmailer')
                    ) . '" width="1" height="1" alt="" />';
            }

        } catch (\Exception $e) {

            $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
            $logger->log(
                LogLevel::ERROR, 
                sprintf(
                    'Error while trying to set pixel-counter: %s', 
                    $e->getMessage()
                )
            );
        }

        return '';
    }

    
    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    static protected function getSettings($which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS)
    {
        return \RKW\RkwBasics\Utility\GeneralUtility::getTyposcriptConfiguration('Rkwmailer', $which);
    }

}