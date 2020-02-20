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

            $callbackFunction = 'replaceHtml';
            if ($this->plaintextFormat) {
                $callbackFunction = 'replacePlaintext';
            }

            return preg_replace_callback('/(<link ([^>]+)>([^<]+)<\/link>)/', array($this, $callbackFunction), $value);

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
            if (preg_match('/^((t3:\/\/page\?uid=)?([0-9]+))/', $parameters, $matches)) {
                if ($matches[3] > 0) {
                    $pid = $matches[3];
                }
            }
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
            if (preg_match('/^((t3:\/\/page\?uid=)?([0-9]+))/', $parameters, $matches)) {
                if ($matches[3] > 0) {
                    $pid = $matches[3];
                }
            }
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
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }

}