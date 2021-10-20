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

use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwBasics\Utility\GeneralUtility;
use RKW\RkwMailer\Domain\Model\QueueMail;
use RKW\RkwMailer\Domain\Model\QueueRecipient;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * FrontendUriBuilder
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @comment implicitly tested
 */
class FrontendUriBuilder extends \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder
{

    /**
     * @var integer
     */
    protected $redirectPid = 0;

    /**
     * @var boolean
     */
    protected $useRedirectLink = false;

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
    protected $redirectLink = '';

    /**
     * @var array
     */
    protected $settings = [];

    
    /**
     * Life-cycle method that is called by the DI container as soon as this object is completely built
     */
    public function initializeObject(): void
    {
        // init frontend with defaults
        FrontendSimulatorUtility::simulateFrontendEnvironment();

        // re-initialize configurationManager in order to load correct concreteConfigurationManager (FE vs. BE)
        $this->configurationManager->initializeObject();
        
        // set contentObject to configurationManager
        /** @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $contentObject */
        $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $this->configurationManager->setContentObject($contentObject);
        
        // set url scheme based on settings
        $this->settings = $this->getSettings();
        if (isset($this->settings['baseUrl'])) {
            $this->setAbsoluteUriScheme($this->getUrlScheme($this->settings['baseUrl']));
        }
        
        parent::initializeObject();
    }

    
    /**
     * Uid of the target page
     *
     * @param int $targetPageUid
     * @return $this
     * @api
     */
    public function setTargetPageUid($targetPageUid): FrontendUriBuilder
    {
        // re-init frontend with given pid
        FrontendSimulatorUtility::simulateFrontendEnvironment(intval($targetPageUid));

        $this->targetPageUid = $targetPageUid;
        return $this;
    }



    /**
     * Sets $useRedirectLink
     *
     * @param boolean $useRedirectLink
     * @return $this the current UriBuilder to allow method chaining
     */
    public function setUseRedirectLink($useRedirectLink)
    {
        $this->useRedirectLink = (boolean) $useRedirectLink;
        return $this;
    }

    /**
     * Gets $useRedirectLink
     *
     * @return boolean
     */
    public function getUseRedirectLink(): bool
    {
        return $this->useRedirectLink;
    }

    /**
     * Sets $queueMail
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return $this the current UriBuilder to allow method chaining
     */
    public function setQueueMail(QueueMail $queueMail): FrontendUriBuilder
    {
        $this->queueMail = $queueMail;
        return $this;
    }

    /**
     * Gets $queueMail
     *
     * @return \RKW\RkwMailer\Domain\Model\QueueMail|null
     */
    public function getQueueMail()
    {
        return $this->queueMail;
    }

    /**
     * Sets $mail
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @return $this the current UriBuilder to allow method chaining
     */
    public function setQueueRecipient(QueueRecipient $queueRecipient): FrontendUriBuilder
    {
        $this->queueRecipient = $queueRecipient;
        return $this;
    }

    /**
     * Gets $queueRecipient
     *
     * @return \RKW\RkwMailer\Domain\Model\QueueRecipient|null
     */
    public function getQueueRecipient()
    {
        return $this->queueRecipient;
    }

    /**
     * Sets $redirectLink
     *
     * @param string $redirectLink
     * @return $this the current UriBuilder to allow method chaining
     */
    public function setRedirectLink(string $redirectLink): FrontendUriBuilder
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
    }

    /**
     * Sets $redirectPid
     *
     * @param integer $redirectPid
     * @return $this the current UriBuilder to allow method chaining
     */
    public function setRedirectPid(int $redirectPid): FrontendUriBuilder
    {
        $this->redirectPid = $redirectPid;
        return $this;
    }

    /**
     * Gets $redirectLPid
     *
     * @return integer
     */
    public function getRedirectPid(): int
    {
        if (!$this->redirectPid) {
            $this->redirectPid = $this->settings['redirectPid'];
        }
        return $this->redirectPid;
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
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @api
     * @see build()
     */
    public function uriFor($actionName = null, $controllerArguments = array(), $controllerName = null, $extensionName = null, $pluginName = null)
    {

        // kill request-calls for non-set values
        if ($actionName !== null) {
            $controllerArguments['action'] = $actionName;
        }
        if ($controllerName !== null) {
            $controllerArguments['controller'] = $controllerName;
        }

        if ($this->format !== '') {
            $controllerArguments['format'] = $this->format;
        }
        if ($this->argumentPrefix !== null) {
            $prefixedControllerArguments = [$this->argumentPrefix => $controllerArguments];
        } else {
            $pluginNamespace = $this->extensionService->getPluginNamespace($extensionName, $pluginName);
            $prefixedControllerArguments = [$pluginNamespace => $controllerArguments];
        }

        ArrayUtility::mergeRecursiveWithOverrule($this->arguments, $prefixedControllerArguments);
        return $this->build();
    }

    
    /**
     * Builds the URI
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
                
                // reset and unset redirect to avoid an infinite loop since uriFor() calls build()!
                // keep the set arguments (addition to queryString)
                $this->reset();
                $this->setUseRedirectLink(false);
                
                // get url
                $url = $this->buildFrontendUri();
                if ($this->getRedirectLink()) {
                    $url = $this->getRedirectLink();
                } 

                // set params
                $arguments = [
                    'tx_rkwmailer_rkwmailer[url]' => $url,
                    'tx_rkwmailer_rkwmailer[mid]'  => intval($this->getQueueMail()->getUid()),
                ];
                
                if ($this->getQueueRecipient()) {
                    $arguments['tx_rkwmailer_rkwmailer[uid]']  = intval($this->getQueueRecipient()->getUid());
                }

                // never use cHash here!
                // this is a bad thing when sending from BE!
                // set all params for redirect link!
                $this->setTargetPageUid($this->getRedirectPid())
                    ->setNoCache(true)
                    ->setUseCacheHash(false)
                    ->setCreateAbsoluteUri(true)
                    ->setArguments(
                        $arguments
                    );

                // generate redirect link
                $url = $this->uriFor(
                    'redirect', 
                    [], 
                    'Tracking', 
                    'rkwmailer', 
                    'Rkwmailer'
                );

                // reset frontend
                FrontendSimulatorUtility::resetFrontendEnvironment();

                return $url;
            }
        }

        // never use cHash here - this is a bad thing when sending from BE!
        // force absolute link and link to access-restricted pages
        $this->setUseCacheHash(false)
            ->setCreateAbsoluteUri(true)
            ->setLinkAccessRestrictedPages(true);

        $url = $this->buildFrontendUri();

        // reset frontend
        FrontendSimulatorUtility::resetFrontendEnvironment();

        return $url;
    }


    
    /**
     * Get UrlScheme
     *
     * @param string $baseUrl
     * @return string
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
    */
    public function getUrlScheme(string $baseUrl): string
    {
        $parsedUrl = parse_url($baseUrl);
        return (isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] : 'http');
    }
    
    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getSettings($which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS)
    {
        return GeneralUtility::getTyposcriptConfiguration('Rkwmailer', $which);
    }
}
