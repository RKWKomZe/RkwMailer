<?php

namespace RKW\RkwMailer\UriBuilder;

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
 * FrontendUriBuilder
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUriBuilder extends \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder
{

    /**
     * @var integer
     */
    protected $redirectPid;

    /**
     * @var boolean
     */
    protected $useRedirectLink;

    /**
     * @var \RKW\RkwMailer\Domain\Model\QueueMail
     */
    protected $queueMail;

    /**
     * @var \RKW\RkwMailer\Domain\Model\QueueRecipient
     */
    protected $queueRecipient;

    /**
     * @var string
     */
    protected $redirectLink;


    /**
     * Override the normal EnvironmentService with own
     */
    public function injectEnvironmentServiceOverride()
    {
        $this->environmentService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwMailer\\Service\\EnvironmentService');
    }

    /**
     * Sets $useRedirectLink
     *
     * @param boolean $useRedirectLink
     * @return $this the current UriBuilder to allow method chaining
     */
    public function setUseRedirectLink($useRedirectLink)
    {
        $this->useRedirectLink = (boolean)$useRedirectLink;

        return $this;
    }

    /**
     * Gets $useRedirectLink
     *
     * @return boolean
     */
    public function getUseRedirectLink()
    {
        return (boolean)$this->useRedirectLink;
        //===
    }

    /**
     * Sets $queueMail
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return $this the current UriBuilder to allow method chaining
     */
    public function setQueueMail($queueMail)
    {
        $this->queueMail = $queueMail;

        return $this;
    }

    /**
     * Gets $queueMail
     *
     * @return \RKW\RkwMailer\Domain\Model\QueueMail
     */
    public function getQueueMail()
    {
        return $this->queueMail;
        //===
    }

    /**
     * Sets $mail
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @return $this the current UriBuilder to allow method chaining
     */
    public function setQueueRecipient($queueRecipient)
    {
        $this->queueRecipient = $queueRecipient;

        return $this;
    }

    /**
     * Gets $queueRecipient
     *
     * @return \RKW\RkwMailer\Domain\Model\QueueRecipient
     */
    public function getQueueRecipient()
    {
        return $this->queueRecipient;
        //===
    }

    /**
     * Sets $redirectLink
     *
     * @param string $redirectLink
     * @return $this the current UriBuilder to allow method chaining
     */
    public function setRedirectLink($redirectLink)
    {
        $this->redirectLink = $redirectLink;

        return $this;
    }

    /**
     * Gets $redirectLink
     *
     * @return string
     */
    public function getRedirectLink()
    {
        return $this->redirectLink;
        //===
    }

    /**
     * Sets $redirectPid
     *
     * @param integer $redirectPid
     * @return $this the current UriBuilder to allow method chaining
     */
    public function setRedirectPid($redirectPid)
    {
        $this->redirectPid = intval($redirectPid);

        return $this;
    }

    /**
     * Gets $redirectLPid
     *
     * @return integer
     */
    public function getRedirectPid()
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
        //===
    }


    /**
     * Creates an URI used for linking to an Extbase action.
     * Works in Frontend and Backend mode of TYPO3.
     *
     * @param string $actionName Name of the action to be called
     * @param array $controllerArguments Additional query parameters. Will be "namespaced" and merged with $this->arguments.
     * @param string $controllerName Name of the target controller. If not set, current ControllerName is used.
     * @param string $extensionName Name of the target extension, without underscores. If not set, current ExtensionName is used.
     * @param string $pluginName Name of the target plugin. If not set, current PluginName is used.
     * @return string the rendered URI
     * @api
     * @see build()
     */
    public function uriFor($actionName = null, $controllerArguments = array(), $controllerName = null, $extensionName = null, $pluginName = null)
    {

        // kill request-calls for non-set values
        if (!$controllerName) {
            $controllerName = '';
        }

        if (!$extensionName) {
            $extensionName = '';
        }

        if (!$pluginName) {
            $pluginName = '';
        }

        return parent::uriFor($actionName, $controllerArguments, $controllerName, $extensionName, $pluginName);
        //===
    }

    /**
     * Builds the URI
     * Depending on the current context this calls buildBackendUri() or buildFrontendUri()
     *
     * @return string The URI
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @api
     * @see buildFrontendUri()
     */
    public function build()
    {

        if (
            ($this->getUseRedirectLink())
            && ($this->getQueueMail())
        ) {

            if ($this->getRedirectPid()) {

                // generate link object and save it
                /** @var \RKW\RkwMailer\Domain\Model\Link $link */
                $link = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwMailer\\Domain\\Model\\Link');
                if ($this->getRedirectLink()) {
                    $link->setUrl($this->getRedirectLink());
                } else {
                    $link->setUrl($this->buildFrontendUri());
                }

                // set QueueMail
                $link->setQueueMail($this->getQueueMail());

                // unique is build via mail-id and link only - NOT with user-id included!!!
                // this way a link used twice in a mail is only saved once
                $link->setHash(sha1($this->getQueueMail()->getUid() . $link->getUrl()));

                /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
                $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

                /** @var \RKW\RkwMailer\Domain\Repository\LinkRepository $linkRepository */
                $linkRepository = $objectManager->get('RKW\\RkwMailer\\Domain\\Repository\\LinkRepository');

                if (!$linkRepository->findOneByHash($link->getHash())) {
                    $linkRepository->add($link);

                    /** @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager */
                    $persistenceManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
                    $persistenceManager->persistAll();
                }

                // reset and unset redirect to avoid an infinite loop since uriFor() calls build()!
                // keep the set arguments (addition to queryString)

                $this->reset();
                $this->setUseRedirectLink(false);

                // set params
                $arguments = [
                    'tx_rkwmailer_rkwmailer[hash]' => $link->getHash(),
                    'tx_rkwmailer_rkwmailer[mid]'  => intval($this->getQueueMail()->getUid()),
                ];
                if ($this->getQueueRecipient()) {
                    $arguments['tx_rkwmailer_rkwmailer[uid]']  = intval($this->getQueueRecipient()->getUid());
                }

                // set all params for redirect link!
                $this->setTargetPageUid($this->getRedirectPid())
                    ->setNoCache(true)
                    ->setUseCacheHash(false)
                    ->setCreateAbsoluteUri(true)
                    ->setArguments(
                        $arguments
                    );

                // generate redirect link
                return $this->uriFor('redirect', array(), 'Link', 'rkwmailer', 'Rkwmailer');
                //===
            }
        }

        // never use cHash here!
        // this is a bad thing when sending from BE!
        //$this->setUseCacheHash(false);

        return $this->buildFrontendUri();
        //===
    }
}
