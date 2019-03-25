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

use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\Exception\InvalidVariableException;

/**
 * Class TranslateViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TranslateViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\TranslateViewHelper
{

    /**
     * Render translation
     *
     * @param string $key Translation Key
     * @param string $languageKey Language Key
     * @param string $id Translation Key compatible to TYPO3 Flow
     * @param string $default If the given locallang key could not be found, this value is used. If this argument is not set,
     *     child nodes will be used to render the default
     * @param array $arguments Arguments to be replaced in the resulting string
     * @param string $extensionName UpperCamelCased extension key (for example BlogExample)
     * @param bool $htmlEscape TRUE if the result should be htmlescaped. This won't have an effect for the default value
     * @return string The translated key or tag body if key doesn't exist
     * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception\InvalidVariableException
     */
    public function render($key = null, $languageKey = null, $id = null, $default = null, array $arguments = null, $extensionName = null, $htmlEscape = false)
    {
        return static::renderStatic(
            array(
                'key'           => $key,
                'languageKey'   => $languageKey,
                'id'            => $id,
                'default'       => $default,
                'htmlEscape'    => $htmlEscape,
                'arguments'     => $arguments,
                'extensionName' => $extensionName,
            ),
            $this->buildRenderChildrenClosure(),
            $this->renderingContext
        );
    }


    /**
     * Return array element by key.
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
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

        // Wrapper including a compatibility layer for TYPO3 Flow Translation
        if ($id === null) {
            $id = $key;
        }

        if ((string)$id === '') {
            throw new InvalidVariableException('An argument "key" or "id" has to be provided', 1351584844);
            //===
        }

        $value = \RKW\RkwMailer\Utility\FrontendLocalizationUtility::translate($id, $extensionName, $arguments, $languageKey);
        if ($value === null) {
            $value = $default !== null ? $default : $renderChildrenClosure();
            if (!empty($arguments)) {
                $value = vsprintf($value, $arguments);
            }
        } elseif ($htmlEscape) {
            $value = htmlspecialchars($value);
        }

        return $value;
        //===
    }
}
