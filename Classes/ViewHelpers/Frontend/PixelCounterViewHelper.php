<?php

namespace RKW\RkwMailer\ViewHelpers\Frontend;

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

use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PixelCounterViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PixelCounterViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * The output must not be escaped.
     *
     * @var bool
     */
    protected $escapeOutput = false;


    /**
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return string
     */
    public function render(\RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient = null, \RKW\RkwMailer\Domain\Model\QueueMail $queueMail = null)
    {

        try {

            /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
            $configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface');
            $extbaseFrameworkConfiguration = $configurationManager->getConfiguration(
                \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'RkwMailer', 'user'
            );
            $counterPixelPid = intval($extbaseFrameworkConfiguration['counterPixelPid']);

            if (
                ($counterPixelPid > 0)
                && ($queueRecipient > 0)
                && ($queueMail > 0)
            ) {

                // init frontend
                /** @todo: should not be necessary any more - try removing this */
                \RKW\RkwBasics\Helper\Common::initFrontendInBackendContext(intval($counterPixelPid));

                // load FrontendUriBuilder
                $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

                /** @var \RKW\RkwMailer\UriBuilder\FrontendUriBuilder $uriBuilder */
                $uriBuilder = $objectManager->get('RKW\\RkwMailer\\UriBuilder\\FrontendUriBuilder');
                $uriBuilder->reset();

                // build link to controller action with needed params
                $uriBuilder->setTargetPageUid($counterPixelPid)
                    ->setNoCache(true)
                    ->setUseCacheHash(false)
                    ->setCreateAbsoluteUri(true)
                    ->setArguments(
                        array(
                            'tx_rkwmailer_rkwmailer[uid]' => intval($queueRecipient->getUid()),
                            'tx_rkwmailer_rkwmailer[mid]' => intval($queueMail->getUid()),
                        )
                    );

                return '<img src="' . urldecode($uriBuilder->uriFor('confirmation', array(), 'Link', 'rkwmailer', 'Rkwmailer')) . '" width="1" height="1" alt="" />';
            }

        } catch (\Exception $e) {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('Error while trying to set pixel-counter: %s', $e->getMessage()));
        }

        return '';
    }


    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }

}