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

/**
 * Class RteLinks
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RteLinksViewHelper extends Replace\RteLinksViewHelper
{

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
        \TYPO3\CMS\Core\Utility\GeneralUtility::deprecationLog(__CLASS__ . ': This ViewHelper will be removed soon. Use \RKW\RkwMailer\ViewHelpers\Frontend\Replace\RteLinksViewHelper instead.');
        return parent::render($value, $plaintextFormat, $style);
    }

}