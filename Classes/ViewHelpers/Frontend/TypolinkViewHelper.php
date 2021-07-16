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

use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;



/**
 * Class TypolinkViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TypolinkViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Uri\TypolinkViewHelper
{

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('pageUid', 'int', 'pageUid for FE-configuration (optional)', false, null);

    }

    /**
     * Except for "forceAbsoluteUrl" this is an exact copy of the parent-class
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param \TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, \TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface $renderingContext)
    {
        $parameter = $arguments['parameter'];
        $additionalParams = $arguments['additionalParams'];
        $pageUid = $arguments['pageUid'];

        // Start: Added content from old render() function
        if (!$pageUid) {
            $pageUid = 1;
        }

        // check for pid in parameters for getting correct domain
        //$pageUid = 1;
        if (preg_match('/^((t3:\/\/page\?uid=)?([0-9]+))/', $parameter, $matches)) {
            if ($matches[3] > 0) {
                $pageUid = $matches[3];
            }
        }

        $content = '';
        if ($parameter) {

            // init frontend
            FrontendSimulatorUtility::simulateFrontendEnvironment(intval($pageUid));

            $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
            $content = $contentObject->typoLink_URL(
                [
                    'parameter'        => self::createTypolinkParameterFromArguments($parameter, $additionalParams),
                    'forceAbsoluteUrl' => 1,
                    'target'           => '_blank',
                    'extTarget'        => '_blank',
                ]
            );

            // reset frontend
            FrontendSimulatorUtility::resetFrontendEnvironment();
        }

        return $content;
    }

}




