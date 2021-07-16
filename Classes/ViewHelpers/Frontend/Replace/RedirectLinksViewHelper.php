<?php

namespace RKW\RkwMailer\ViewHelpers\Frontend\Replace;

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
 * Class RedirectLinksViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RedirectLinksViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * The output must not be escaped.
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @var integer
     */
    protected $redirectPid;

    /**
     * @var \RKW\RkwMailer\Domain\Model\QueueMail
     */
    protected $queueMail = null;

    /**
     * @var \RKW\RkwMailer\Domain\Model\QueueRecipient
     */
    protected $queueRecipient = null;

    /**
     * @var array $additionalParams
     */
    protected $additionalParams = array();


    /**
     * Replaces all set links with redirect links
     *
     * @param string $value
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @param boolean $isPlaintext
     * @param array $additionalParams additional query parameters that won't be prefixed like $arguments (overrule $arguments)
     * @return string
     */
    public function render($value = null, \RKW\RkwMailer\Domain\Model\QueueMail $queueMail = null, \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient = null, $isPlaintext = false, $additionalParams = array())
    {

        try {

            if ($value === null) {
                $value = $this->renderChildren();
            }

            if (!is_string($value)) {
                return $value;
            }

            $this->queueMail = $queueMail;
            $this->queueRecipient = $queueRecipient;
            $this->additionalParams = $additionalParams;

            if ($this->queueMail) {

                if ($isPlaintext == true) {

                    return preg_replace_callback('/(http[s]?:\/\/[^\s]+)/', array($this, 'replacePlaintext'), $value);

                } else {
                    // U for non-greedy behavior: take as less signs as possible
                    return preg_replace_callback('/(<a.+href=")([^"]+)(")/U', array($this, 'replaceHtml'), $value);
                }

            }

        } catch (\Exception $e) {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('Error while trying to replace links: %s', $e->getMessage()));
        }

        return $value;
    }


    /**
     * Replaces the matches
     *
     * @param array $matches
     * @return string
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     *
     */
    protected function replaceHtml($matches)
    {

        // do replacement but not for anchors and mailto's
        if (
            (count($matches) == 4)
            && (strpos($matches[2], '#') !== 0)
            && (strpos($matches[2], 'mailto:') !== 0)
        ) {
            return $matches[1] . $this->replace($matches[2]) . $matches[3];
        }

        return $matches[0];
    }


    /**
     * Replaces the matches
     *
     * @param array $matches
     * @return string
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    protected function replacePlaintext($matches)
    {

        // do replacement but not for anchors and mailto's
        if (
            (count($matches) == 2)
            && (strpos($matches[1], '#') !== 0)
            && (strpos($matches[1], 'mailto:') !== 0)
        ) {

            return $this->replace($matches[1]);
        }

        return $matches[0];
    }


    /**
     * Replaces the link
     *
     * @param string $link
     * @return string
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    protected function replace($link)
    {

        if ($this->queueMail) {

            // load FrontendUriBuilder
            /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

            /** @var \RKW\RkwMailer\UriBuilder\FrontendUriBuilder $uriBuilder */
            $uriBuilder = $objectManager->get(\RKW\RkwMailer\UriBuilder\FrontendUriBuilder::class);
            $uriBuilder->reset();

            $uriBuilder->setUseRedirectLink(true)
                ->setRedirectPid($this->getRedirectPid())
                ->setQueueMail($this->queueMail)
                ->setQueueRecipient($this->queueRecipient)
                ->setRedirectLink($link)
                ->setArguments($this->additionalParams);

            return $uriBuilder->build();
        }

        return $link;
    }


    /**
     * Gets $redirectLPid
     *
     * @return integer
     */
    protected function getRedirectPid()
    {

        if (!$this->redirectPid) {

            /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

            // get rewrite link from TypoScript
            /** @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager */
            $configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface');
            $extbaseFrameworkConfiguration = $configurationManager->getConfiguration(
                \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'RkwMailer', 'user'
            );
            $this->redirectPid = $extbaseFrameworkConfiguration['redirectPid'];
        }

        return $this->redirectPid;
    }


    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }
}