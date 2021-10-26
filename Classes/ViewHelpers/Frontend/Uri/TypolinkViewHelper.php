<?php

namespace RKW\RkwMailer\ViewHelpers\Frontend\Uri;

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
use RKW\RkwMailer\Utility\FrontendTypolinkUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class TypolinkViewHelper
 *
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
        $this->registerArgument('pageUid', 'int', 'pageUid for FE-configuration - DEPRECATED', false, null);

    }

    /**
     * Render typolinks
     **
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param \TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $parameter = $arguments['parameter'];
        $additionalParams = $arguments['additionalParams'];

        // log deprecated attribute
        if ($arguments['pageUid']) {
            GeneralUtility::logDeprecatedViewHelperAttribute(
                'pageUid',
                $renderingContext,
                'Argument "pageUid" on rkwMailer:frontend.uri.typolink is deprecated and has no effect any more.'
            );
        }
        
        $content = '';
        if ($parameter) {
            $content = FrontendTypolinkUtility::getTypolinkUrl($parameter, $additionalParams);
        }

        return $content;
    }

    
    

}




