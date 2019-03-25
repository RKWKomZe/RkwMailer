<?php

namespace RKW\RkwMailer\Helper;

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
 * FrontendLocalization
 * We can not extend the basic class here, since the methods are used as static methods and this confuses translation-handling
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @see \TYPO3\CMS\Extbase\Utility\LocalizationUtility
 * @deprecated Use \RKW\RkwMailer\Utility\FrontendLocalizationUtility instead
 */
class FrontendLocalization extends \RKW\RkwMailer\Utility\FrontendLocalizationUtility
{

}
