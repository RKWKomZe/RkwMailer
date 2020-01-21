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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class RteLinks
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RteLinksViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{


    /**
     * @var integer
     */
    protected $pageUid;

    /**
     * @var string
     */
    protected $style;


    /**
     * Replaces all links of WYSIWYG- editor
     *
     * @param string $value
     * @param boolean $plaintextFormat
     * @param integer $pageUid
     * @param string $style Add CSS-style-attribute
     * @return string
     */
    public function render($value = null, $plaintextFormat = false, $pageUid = null, $style = '')
    {

        $this->pageUid = $pageUid;
        $this->style = $style;

        if ($value === null) {
            $value = $this->renderChildren();
        }

        if (!is_string($value)) {
            return $value;
            //===
        }

        if ($plaintextFormat == true) {
            return preg_replace_callback('/(<link ([^>]+)>([^<]+)<\/link>)/', array($this, 'replacePlaintext'), $value);
            //===
        }

        return preg_replace_callback('/(<link ([^>]+)>([^<]+)<\/link>)/', array($this, 'replaceHtml'), $value);
        //===

    }


    /**
     * Replaces the matches
     *
     * @param array $matches
     * @return string
     */
    protected function replaceHtml($matches)
    {

        if (
        (count($matches) == 4)
        ) {

            $url = $this->buildLink($matches[2]);

            return '<a href="' . $url . '" ' . ($this->style ? 'style="' . $this->style . '"' : '') . ' target="_blank">' . $matches[3] . '</a>';
            //===
        }

        return $matches[0];
        //===
    }

    /**
     * Replaces the matches
     *
     * @param array $matches
     * @return string
     */
    protected function replacePlaintext($matches)
    {

        if (
        (count($matches) == 4)
        ) {
            $url = $this->buildLink($matches[2]);

            return $matches[3] . ' < ' . $url . ' > ';
            //===
        }

        return $matches[0];
        //===
    }


    /**
     *  Except for "forceAbsoluteUrl" this is an exact copy of the parent-class
     *
     * @param array $arguments
     * @return string
     */
    protected function buildLink($parameter)
    {

        // check for pid in parameters for getting correct domain
        $pageUid = $this->pageUid;

        if (!$pageUid) {
            $pageUid = 1;
        }

        if (preg_match('/^((t3:\/\/page\?uid=)?([0-9]+))/', $parameter, $matches)) {
            $pageUid = $matches[3];
        }
        $this->initTSFE($pageUid);

        // build link
        $content = '';
        if ($parameter) {
            $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
            $content = $contentObject->typoLink_URL(
                [
                    'parameter'        => $parameter,
                    'forceAbsoluteUrl' => 1,
                ]
            );
        }

        return $content;
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

            // check if we have another id or typeNum here - otherwise we use the existing object
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