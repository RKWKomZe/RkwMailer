<?php

namespace RKW\RkwMailer\ViewHelpers\Statistics;

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

use RKW\RkwMailer\Domain\Repository\OpeningStatisticsRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class OpeningsViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OpeningsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Returns the number of links that have been clicked in a given queueMail
     * 
     * @param int $queueMailUid
     * @return int
     */
    public function render (int $queueMailUid) 
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $openingStatisticsRepository = $objectManager->get(OpeningStatisticsRepository::class);
        
        return $openingStatisticsRepository->findByQueueMailUid($queueMailUid)->count();
    }


}