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
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class RteLinks
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RteLinksViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * The output must not be escaped.
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @var string
     */
    protected $style;

    /**
     * @var bool
     */
    protected $plaintextFormat = false;

    /**
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    protected $contentObject;



    /**
     * Replaces all links of WYSIWYG- editor
     *
     * @param string $value
     * @param boolean $plaintextFormat
     * @param string $style Add CSS-style-attribute
     * @return string
     */
    public function render($value = null, $plaintextFormat = false, $style = '')
    {

        try {

            $this->contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
            $this->style = $style;
            $this->plaintextFormat = (bool) $plaintextFormat;
            if ($value === null) {
                $value = $this->renderChildren();
            }

            if (!is_string($value)) {
                return $value;
            }

            // new version for TKE
            $callbackFunction = 'replaceTypolink';
            $value = preg_replace_callback('/(<a([^>]+)href="([^"]+)"([^>]+)>([^<]+)<\/a>)/', array($this, $callbackFunction), $value);

            // Old version for RTE
            $callbackFunction = 'replaceHtml';
            if ($this->plaintextFormat) {
                $callbackFunction = 'replacePlaintext';
            }
            $value = preg_replace_callback('/(<link ([^>]+)>([^<]+)<\/link>)/', array($this, $callbackFunction), $value);

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
     */
    protected function replaceHtml($matches)
    {

        if (count($matches) == 4) {

            $parameters = $matches[2];
            $linkText = $matches[3];

            // init frontend
            $pid = 1;
            if (preg_match('/^((t3:\/\/page\?uid=)?([0-9]+))/', $parameters, $matchesSub)) {
                if ($matchesSub[3] > 0) {
                    $pid = $matchesSub[3];
                }
            }

            /** @todo: should not be necessary any more - try removing this */
            \RKW\RkwBasics\Helper\Common::initFrontendInBackendContext(intval($pid));

            // get url
            $conf = [
                'parameter' => $parameters,
                'forceAbsoluteUrl' => 1,
                'target' => '_blank',
                'extTarget' => '_blank',
                'fileTarget' => '_blank',
                'ATagParams' => ($this->style ? 'style="' . $this->style . '"' : '')
            ];
            return $this->contentObject->typoLink($linkText, $conf);
        }

        return $matches[0];
    }



    /**
     * Replaces the matches
     *
     * @param array $matches
     * @return string
     */
    protected function replacePlaintext($matches)
    {

        if (count($matches) == 4) {

            $parameters = $matches[2];
            $linkText = $matches[3];

            // init frontend
            $pid = 1;
            if (preg_match('/^((t3:\/\/page\?uid=)?([0-9]+))/', $parameters, $matchesSub)) {
                if ($matchesSub[3] > 0) {
                    $pid = $matchesSub[3];
                }
            }

            /** @todo: should not be necessary any more - try removing this */
            \RKW\RkwBasics\Helper\Common::initFrontendInBackendContext(intval($pid));

            // get url
            $url = $this->contentObject->typoLink_URL(
                [
                    'parameter'        => $parameters,
                    'forceAbsoluteUrl' => 1,
                ]
            );

            return $linkText . ' [' . $url . ']';
        }

        return $matches[0];
    }


    /**
     * Replaces the matches
     *
     * @param array $matches
     * @return string
     */
    protected function replaceTypolink($matches)
    {

        if (count($matches) == 6) {

            $attributes = trim($matches[2]) . ' ' . trim($matches[4]);
            $typoLink = $matches[3];
            $linkText = $matches[5];

            // check for pid in parameters for getting correct domain
            $pageUid = 1;
            if (preg_match('/^((t3:\/\/page\?uid=)?([0-9]+))/', $typoLink , $matchesSub)) {
                if ($matchesSub[3] > 0) {
                    $pageUid = $matchesSub[3];
                }
            }

            // init frontend
            /** @todo: should not be necessary any more - try removing this */
            \RKW\RkwBasics\Helper\Common::initFrontendInBackendContext(intval($pageUid));

            $url = '';
            if ($typoLink) {
                $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
                $url = $contentObject->typoLink_URL(
                    [
                        'parameter'        => $typoLink,
                        'forceAbsoluteUrl' => 1,
                    ]
                );
            }

            // add styles if needed
            $this->setStyles($attributes);

            if ($this->plaintextFormat) {
                return $linkText . ' [' . $url . ']';
            } else {
                return '<a href="' . $url . '" ' . trim($attributes) . '>' . $linkText . '</a>';
            }
        }

        return $matches[0];
    }



    /**
     * Sets styles of links
     *
     * @param string $string
     * @return void
     */
    protected function setStyles(&$string)
    {
        if ($this->style) {
            if (strpos($string, 'style="') !== false) {
                $string = preg_replace('/style="([^"]+)"/', "style=\"$1 $this->style\"", $string);

            } else {
                $string .= ' style="' . $this->style . '"';
            }
        }
    }


    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }

}