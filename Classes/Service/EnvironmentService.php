<?php

namespace RKW\RkwMailer\Service;

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
 * EnvironmentService
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class EnvironmentService extends \TYPO3\CMS\Extbase\Service\EnvironmentService
{

    /**
     * Always returns FE-Mode true
     *
     * @return bool
     */
    public function isEnvironmentInFrontendMode()
    {
        return true;
    }

    /**
     * Always return BE-Mode false
     *
     * @return bool
     */
    public function isEnvironmentInBackendMode()
    {
        return false;
    }

    /**
     * Always returns CliMode false
     *
     * @return bool
     */
    public function isEnvironmentInCliMode()
    {
        return false;
    }

}
