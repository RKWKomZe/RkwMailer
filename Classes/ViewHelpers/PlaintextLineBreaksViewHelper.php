<?php

namespace RKW\RkwMailer\ViewHelpers;

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
 * Class PlaintextLineBreaksViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PlaintextLineBreaksViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Handles line breaks in plaintext mode
     *
     * @param string $value
     * @param bool $keepLineBreaks
     * @return string
     */
    public function render($value = null, $keepLineBreaks = false)
    {


        if ($value === null) {
            $value = $this->renderChildren();
        }

        if (! is_string($value)) {
            return $value;
            //===
        }

        if ($keepLineBreaks) {
            $value = preg_replace( '/\r|\n/', '\n',  $value);

        } else {

            // replace real line breaks and indents
            $value = preg_replace('/\r|\n|\t|\s\s+/', '', $value);

            // set manual line breaks
            $value = str_replace('\n', "\n", $value);

        }

        return $value;
        //===

    }


}