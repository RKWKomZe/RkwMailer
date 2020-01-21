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
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @param integer $counterPixelPid
     * @return string
     */
    public function render(\RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient = null, \RKW\RkwMailer\Domain\Model\QueueMail $queueMail = null, $counterPixelPid = 0)
    {

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface');

        if (!$counterPixelPid) {
            $extbaseFrameworkConfiguration = $configurationManager->getConfiguration(
                \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'RkwMailer', 'user'
            );
            $counterPixelPid = $extbaseFrameworkConfiguration['counterPixelPid'];
        }

        if (
            ($counterPixelPid > 0)
            && ($queueRecipient > 0)
            && ($queueMail > 0)
        ) {

            // set pid
            $this->initTSFE(intval($counterPixelPid));

            // load FrontendUriBuilder
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

            /** @var \RKW\RkwMailer\UriBuilder\FrontendUriBuilder $uriBuilder */
            $uriBuilder = $objectManager->get('RKW\\RkwMailer\\UriBuilder\\FrontendUriBuilder');
            $uriBuilder->reset();

            // build link to controller action with needed params
            $uriBuilder->setTargetPageUid($counterPixelPid)
                ->setNoCache(true)
                ->setUseCacheHash(false)
                ->setArguments(
                    array(
                        'tx_rkwmailer_rkwmailer[uid]' => intval($queueRecipient->getUid()),
                        'tx_rkwmailer_rkwmailer[mid]' => intval($queueMail->getUid()),
                    )
                )
                ->setCreateAbsoluteUri(true);

            return '<img src="' . urldecode($uriBuilder->uriFor('confirmation', array(), 'Link', 'rkwmailer', 'Rkwmailer')) . '" width="1" height="1" alt="" />';
            //===
        }

        return '';
        //===
    }


    /**
     * init frontend to render frontend links in task
     *
     * @param integer $id
     * @param integer $typeNum
     * @return void
     */
    protected function initTSFE($id = 1, $typeNum = 0)
    {

        // only if in BE-Mode!!! Otherwise FE will be crashed
        if (TYPO3_MODE == 'BE') {

            if (!is_object($GLOBALS['TT'])) {
                $GLOBALS['TT'] = new \TYPO3\CMS\Core\TimeTracker\NullTimeTracker;
                $GLOBALS['TT']->start();
            }

            if (
                (!$GLOBALS['TSFE'] instanceof \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController)
                || ($GLOBALS['TSFE']->id != $id)
                || ($GLOBALS['TSFE']->type != $typeNum)
            ) {
                $GLOBALS['TSFE'] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController', $GLOBALS['TYPO3_CONF_VARS'], $id, $typeNum);
                $GLOBALS['TSFE']->connectToDB();
                $GLOBALS['TSFE']->initFEuser();
                $GLOBALS['TSFE']->determineId();
                $GLOBALS['TSFE']->initTemplate();
                $GLOBALS['TSFE']->getConfigArray();
                $GLOBALS['LANG']->csConvObj = $GLOBALS['TSFE']->csConvObj;

                if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('realurl')) {
                    $rootline = \TYPO3\CMS\Backend\Utility\BackendUtility::BEgetRootLine($id);
                    $host = \TYPO3\CMS\Backend\Utility\BackendUtility::firstDomainRecord($rootline);
                    $_SERVER['HTTP_HOST'] = $host;
                    $GLOBALS['TSFE']->config['config']['absRefPrefix'] = $host;
                }
            }
        }
    }
}