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

use RKW\RkwMailer\Utility\FrontendLocalizationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\Exception\InvalidVariableException;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;


/**
 * Class TranslateViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TranslateViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\TranslateViewHelper
{

    /**
     * initializeArguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('languageKey', 'string', 'Language Key', false, null);
    }

    /**
     * Return array element by key.
     *
     * @param array   $arguments
     * @param \Closure $renderChildrenClosure
     * @param \TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
     * @throws InvalidVariableException
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $key = $arguments['key'];
        $languageKey = $arguments['languageKey'];
        $id = $arguments['id'];
        $default = $arguments['default'];
        $htmlEscape = $arguments['htmlEscape'];
        $extensionName = $arguments['extensionName'];
        $arguments = $arguments['arguments'];


        if ($htmlEscape !== null) {
            GeneralUtility::logDeprecatedViewHelperAttribute(
                'htmlEscape',
                $renderingContext,
                'Please wrap the view helper in <f:format.raw> if you want to disable HTML escaping, which is enabled by default now.'
            );
        }

        // Wrapper including a compatibility layer for TYPO3 Flow Translation
        if ($id === null) {
            $id = $key;
        }

        if ((string)$id === '') {
            throw new InvalidVariableException('An argument "key" or "id" has to be provided', 1351584844);
        }

        $request = $renderingContext->getControllerContext()->getRequest();
        $extensionName = $extensionName === null ? $request->getControllerExtensionName() : $extensionName;
        try {
            $value = FrontendLocalizationUtility::translate($id, $extensionName, $arguments, $languageKey);
        } catch (\InvalidArgumentException $e) {
            $value = null;
        }
        if ($value === null) {
            $value = $default !== null ? $default : $renderChildrenClosure();
            if (!empty($arguments)) {
                $value = vsprintf($value, $arguments);
            }
        }
        return $value;
    }
}


