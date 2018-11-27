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
 * Class SelectPropertyViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class SelectPropertyViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * return the value of a property of an object
     *
     * @param string $property
     * @param array $objectList
     * @param integer $iterationNumber
     * @return boolean
     */
    public function render($property, $objectList, $iterationNumber)
    {

        $i = 0;
        foreach ($objectList as $object) {

            if ($i == $iterationNumber) {
                $getter = 'get' . ucfirst($property);
                $propertyValueToCompare = $object->$getter();

                return $propertyValueToCompare;
                //===
            }
            $i++;
        }

        return false;
        //===
    }
}