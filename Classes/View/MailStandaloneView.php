<?php
namespace RKW\RkwMailer\View;

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
use RKW\RkwMailer\Database\MarkerReducer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Web\Request as WebRequest;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

use TYPO3\CMS\Fluid\View\AbstractTemplateView;
use TYPO3\CMS\Fluid\View\StandaloneView;
/**
 * A standalone template view.
 * Should be used as view if you want to use Fluid without Extbase extensions
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @api
 */
class MailStandaloneView extends StandaloneView
{

    /**
     * settings
     *
     * @var array
     */
    protected $settings = [];


    /**
     * settingsPid
     *
     * @var int
     */
    protected $settingsPid = 0;


    /**
     * objectManager
     *
     * @var ObjectManager
     */
    protected $objectManager;


    /**
     * Constructor
     *
     * @param int $pid Pid to use for frontend simulation
     * @return void
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function __construct(int $pid = 0)
    {
        // simulate frontend
        FrontendSimulatorUtility::simulateFrontendEnvironment($pid);

        // load objects
        if (! $this->objectManager) {
            $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        }

        /** @var WebRequest $request */
        $request = $this->objectManager->get(WebRequest::class);
        $request->setRequestUri(GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'));
        $request->setBaseUri(GeneralUtility::getIndpEnv('TYPO3_SITE_URL'));

        /** @var UriBuilder $uriBuilder */
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($request);

        /** @var ControllerContext $controllerContext */
        $controllerContext = $this->objectManager->get(ControllerContext::class);
        $controllerContext->setRequest($request);
        $controllerContext->setUriBuilder($uriBuilder);

        /** @var RenderingContext $renderingContext */
        $renderingContext = $this->objectManager->get(RenderingContext::class, $this);
        $renderingContext->setControllerContext($controllerContext);

        AbstractTemplateView::__construct($renderingContext);

        // get template configuration based on loaded frontend
        /** @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $GLOBALS ['TSFE'] */
        $this->settings = $GLOBALS['TSFE']->tmpl->setup['module.']['tx_rkwmailer.'];
        $this->settingsPid = $pid;

        // set root-paths according to configuration
        if (
            isset($this->settings['view.']['layoutRootPaths.'])
            && (is_array($this->settings['view.']['layoutRootPaths.']))
        ){
            $this->setLayoutRootPaths($this->settings['view.']['layoutRootPaths.']);
        }

        if (
            isset($this->settings['view.']['partialRootPaths.'])
            && (is_array($this->settings['view.']['partialRootPaths.']))
        ){
            $this->setPartialRootPaths($this->settings['view.']['partialRootPaths.']);
        }

        if (
            isset($this->settings['view.']['templateRootPaths.'])
            && (is_array($this->settings['view.']['templateRootPaths.']))
        ){
            $this->setTemplateRootPaths($this->settings['view.']['templateRootPaths.']);
        }

        // reset frontend
        FrontendSimulatorUtility::resetFrontendEnvironment();
    }



    /**
     * get base url
     *
     * @return string
     * @api
     */
    public function getBaseUrl(): string
    {
        if ($baseUrl = $this->settings['settings.']['baseUrl']) {
            return rtrim($baseUrl, '/');
        }
        return '';
    }


    /**
     * get base url for images
     *
     * @return string
     * @api
     */
    public function getBaseUrlImages(): string
    {
        if (
            ($baseUrl = rtrim($this->settings['settings.']['baseUrl'], '/'))
            && ($basePath = rtrim($this->settings['settings.']['basePathImages'], '/'))
        ) {
            return $baseUrl . '/' . $this->getRelativePath($basePath);
        }
        return '';
    }


    /**
     * get url for logo
     *
     * @return string
     * @api
     */
    public function getLogoUrl(): string
    {

        if (
            ($baseUrl = rtrim($this->settings['settings.']['baseUrl'], '/'))
            && ($basePath = rtrim($this->settings['settings.']['basePathLogo'], '/'))
        ) {
            return $baseUrl . '/' . $this->getRelativePath($basePath);
        }
        return '';
    }


    /**
     * Returns the relative image path
     *
     * @param string $path
     * @return string
     * @api
     */
    public function getRelativePath(string $path): string
    {
        if (strpos($path, 'EXT:') === 0) {

            list($extKey, $local) = explode('/', substr($path, 4), 2);
            if (
                ((string)$extKey !== '')
                && (ExtensionManagementUtility::isLoaded($extKey))
                && ((string)$local !== '')
            ) {
                $path = ExtensionManagementUtility::siteRelPath($extKey) . $local;
                if (strpos($path, '../') === 0) {
                    $path = substr($path, -(strlen($path)-3));
                }
            }
        }

        return rtrim($path, '/');
    }


    /**
     * Assigns multiple values to the JSON output.
     * However, only the key "value" is accepted.
     *
     * @param array $values Keys and values - only a value with key "value" is considered
     * @return $this
     * @api
     */
    public function assignMultiple($values): MailStandaloneView
    {
        /** @var MarkerReducer $markerReducer */
        $markerReducer = GeneralUtility::makeInstance(MarkerReducer::class);
        $values = $markerReducer->explodeMarker($values);

        parent::assignMultiple($values);
        return $this;
    }


    /**
     * Adds the root path(s) to the layouts.
     *
     * @param string[] $layoutRootPaths Root path to the layouts
     * @return void
     * @api
     */
    public function addLayoutRootPaths(array $layoutRootPaths): void
    {
        if ($existingPaths = $this->baseRenderingContext->getTemplatePaths()->getLayoutRootPaths()) {
            $this->baseRenderingContext->getTemplatePaths()->setLayoutRootPaths(
                array_merge(
                    $existingPaths,
                    $layoutRootPaths
                )
            );

        } else {
            $this->baseRenderingContext->getTemplatePaths()->setLayoutRootPaths($layoutRootPaths);
        }
    }


    /**
     * Adds the root path(s) to the partials.
     *
     * @param string[] $partialRootPaths Root path to the partials
     * @return void
     * @api
     */
    public function addPartialRootPaths(array $partialRootPaths): void
    {
        if ($existingPaths = $this->baseRenderingContext->getTemplatePaths()->getPartialRootPaths()) {
            $this->baseRenderingContext->getTemplatePaths()->setPartialRootPaths(
                array_merge(
                    $existingPaths,
                    $partialRootPaths
                )
            );

        } else {
            $this->baseRenderingContext->getTemplatePaths()->setPartialRootPaths($partialRootPaths);
        }
    }


    /**
     * Adds the root path(s) to the templates.
     *
     * @param string[] $templateRootPaths Root path to the templates
     * @return void
     * @api
     */
    public function addTemplateRootPaths(array $templateRootPaths): void
    {
        if ($existingPaths = $this->baseRenderingContext->getTemplatePaths()->getTemplateRootPaths()) {
            $this->baseRenderingContext->getTemplatePaths()->setTemplateRootPaths(
                array_merge(
                    $existingPaths,
                    $templateRootPaths
                )
            );

        } else {
            $this->baseRenderingContext->getTemplatePaths()->setTemplateRootPaths($templateRootPaths);
        }
    }


    /**
     * Resolves the template root to be used inside other paths.
     *
     * @return array Fluid layout root paths
     * @throws InvalidTemplateResourceException
     * @api
     */
    public function getTemplateRootPaths(): array
    {
        return $this->baseRenderingContext->getTemplatePaths()->getTemplateRootPaths();
    }


    /**
     * Loads the template source and renders the template.
     *
     * @param string|null $actionName If set, this action's template will be rendered instead of the one defined in the context.
     * @return string
     * @api
     */
    public function render($actionName = null): string
    {

        $renderedTemplate = parent::render($actionName);

        // replace baseURLs in final email  - replacement with asign only works in template-files, not on layout-files
        $renderedTemplate = preg_replace('/###baseUrl###/', $this->getBaseUrl(), $renderedTemplate);
        $renderedTemplate = preg_replace('/###baseUrlImages###/', $this->getBaseUrlImages(), $renderedTemplate);
        $renderedTemplate = preg_replace('/###baseUrlLogo###/', $this->getLogoUrl(), $renderedTemplate);
        $renderedTemplate = preg_replace('/###logoUrl###/', $this->getLogoUrl(), $renderedTemplate);

        // replace relative paths and absolute paths to server-root!
        /* @toDo: Check if Environment-variables are still valid in TYPO3 9 and upwards! */
        $replacePaths = [
            GeneralUtility::getIndpEnv('TYPO3_SITE_PATH'),
            $_SERVER['TYPO3_PATH_ROOT'] .'/'
        ];

        foreach ($replacePaths as $replacePath) {
            $renderedTemplate = preg_replace(
                '/(src|href)="' .
                str_replace('/', '\/', $replacePath) . '([^"]+)"/', '$1="' . '/$2"',
                $renderedTemplate
            );
        }
        $renderedTemplate = preg_replace(
            '/(src|href)="\/([^"]+)"/',
            '$1="' . $this->getBaseUrl() . '/$2"',
            $renderedTemplate
        );

        return $renderedTemplate;
    }


    /**
     * Sets the template
     *
     * @param string $templateName
     * @api
     */
    public function setTemplate($templateName): void
    {

        // check for absolute paths!
        if (strpos($templateName, 'EXT:') === 0) {

            // check for file-extension
            if (strpos($templateName, '.') === false) {
                $templateName .=  '.' . $this->getFormat();
            }

            $templatePathFile = GeneralUtility::getFileAbsFileName($templateName);
            $this->setTemplatePathAndFilename($templatePathFile);

        // check if there is a template-path included
        // Since TYPO3 8 templates are set via the given controller action.
        // Thus using setTemplate with relative paths will result in using this path as controller action.
        } else if (
            ($subFolder = dirname($templateName))
            && ($subFolder != '.')
        ){
            $newTemplatePaths = [];
            foreach ($this->getTemplateRootPaths() as $path) {
                if (is_dir($path . $subFolder)) {
                    $newTemplatePaths[] = $path . $subFolder;
                }
            }
            $this->addTemplateRootPaths($newTemplatePaths);

            GeneralUtility::deprecationLog(
                __CLASS__ . '::' . __METHOD__ . '(): Please do not use this method with relative paths included. ' .
                'The paths should be added to the TemplateRootPaths instead. From TYPO3 8.7 on templates are set via the given controller action. ' .
                'Thus using setTemplate with relative paths will in turn result in using this path as controller action.'
            );
        }

        parent::setTemplate(basename($templateName));
    }



    /**
     * Set the request object
     *
     * @param \TYPO3\CMS\Extbase\Mvc\Web\Request $webRequest
     * @return void
     * @see __construct()

    public function setRequest(\TYPO3\CMS\Extbase\Mvc\Web\Request $webRequest)
    {
        // set basics
        $webRequest->setRequestURI(GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'));
        $webRequest->setBaseURI(GeneralUtility::getIndpEnv('TYPO3_SITE_URL'));

        /** @var UriBuilder $uriBuilder  *
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($webRequest);

        $currentVersion = VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
        if ($currentVersion < 8000000) {

            /** @var ControllerContext $controllerContext *
            $this->controllerContext->setRequest($webRequest);
            $this->controllerContext->setUriBuilder($uriBuilder);

        } else {

            /** @var ControllerContext $controllerContext *
            $controllerContext = $this->objectManager->get(ControllerContext::class);
            $controllerContext->setRequest($webRequest);
            $controllerContext->setUriBuilder($uriBuilder);

            /** @var RenderingContext $renderingContext
            $renderingContext = $this->objectManager->get(RenderingContext::class, $this);
            $renderingContext->setControllerContext($controllerContext);
            $this->setRenderingContext($renderingContext);
        }
    }*/


    /**
     * Returns configuration array
     * @return array
     */
    public function getSettings(): array
    {
        return ($this->settings ? $this->settings : []);
    }


    /**
     * Returns the pid which contains the settings
     * @return int
     */
    public function getSettingsPid(): int
    {
        return $this->settingsPid;
    }

}
