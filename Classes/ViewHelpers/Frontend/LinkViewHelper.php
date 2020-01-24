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
 * Class LinkViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class LinkViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Example how to use in fluid:
     * <vh:frontendLink pageId='3' actionName='optIn' controller='Notification' extensionName='rkwNewsletter'
     * pluginName='Notification' params='{token_yes: marker.token_yes, user: marker.user}' />
     *
     * @param string $action Target action
     * @param array $arguments Arguments
     * @param string $controller Target controller. If null current controllerName is used
     * @param string $extensionName Target Extension Name (without "tx_" prefix and no underscores). If null the current
     *     extension name is used
     * @param string $pluginName Target plugin. If empty, the current plugin name is used
     * @param integer $pageUid target page. See TypoLink destination
     * @param integer $pageType type of the target page. See typolink.parameter
     * @param boolean $noCache set this to disable caching for the target page. You should not need this.
     * @param boolean $noCacheHash set this to supress the cHash query parameter created by TypoLink. You should not need this.
     * @param string $section the anchor to be added to the URI
     * @param boolean $linkAccessRestrictedPages If set, links pointing to access restricted pages will still link to the page
     *     even though the page cannot be accessed.
     * @param array $additionalParams additional query parameters that won't be prefixed like $arguments (overrule $arguments)
     * @param boolean $absolute If set, the URI of the rendered link is absolute
     * @param boolean $addQueryString If set, the current query parameters will be kept in the URI
     * @param array $argumentsToBeExcludedFromQueryString arguments to be removed from the URI. Only active if $addQueryString =
     *     true
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail queueMail for redirecting links
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient queueRecipient of email
     * @return string
     */
    public function render($action = null, array $arguments = array(), $controller = null, $extensionName = null, $pluginName = null, $pageUid = null, $pageType = 0, $noCache = false, $noCacheHash = false, $section = '', $linkAccessRestrictedPages = false, array $additionalParams = array(), $absolute = false, $addQueryString = false, array $argumentsToBeExcludedFromQueryString = array(), \RKW\RkwMailer\Domain\Model\QueueMail $queueMail = null, \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient = null)
    {

        if (!$pageUid) {
            $pageUid = 1;
        }

        // init frontend
        \RKW\RkwBasics\Helper\Common::initFrontendInBackendContext(intval($pageUid));

        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        /** @var \RKW\RkwMailer\UriBuilder\FrontendUriBuilder $uriBuilder */
        $uriBuilder = $objectManager->get('RKW\\RkwMailer\\UriBuilder\\FrontendUriBuilder');
        $uriBuilder->reset();

        // build link based on given data
        $uriBuilder->setTargetPageUid($pageUid)
            ->setTargetPageType($pageType)
            ->setNoCache($noCache)
            ->setUseCacheHash(!$noCacheHash)
            ->setSection($section)
            ->setLinkAccessRestrictedPages($linkAccessRestrictedPages)
            ->setArguments($additionalParams)
            ->setCreateAbsoluteUri(true) // force absolute link
            ->setAddQueryString($addQueryString)
            ->setArgumentsToBeExcludedFromQueryString($argumentsToBeExcludedFromQueryString);

        if ($queueMail) {
            $uriBuilder->setUseRedirectLink(true)
                ->setQueueMail($queueMail)
                ->setQueueRecipient($queueRecipient);
        }

        return $uriBuilder->uriFor($action, $arguments, $controller, $extensionName, $pluginName);
        //===
    }

}