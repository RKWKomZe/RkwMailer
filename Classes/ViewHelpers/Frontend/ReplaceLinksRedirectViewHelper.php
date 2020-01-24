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
 * Class ReplaceLinksRedirectViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ReplaceLinksRedirectViewHelper extends Replace\RedirectLinksViewHelper
{

    /**
     * Replaces all set links with redirect links
     *
     * @param string $value
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @param boolean $isPlaintext
     * @param array $additionalParams additional query parameters that won't be prefixed like $arguments (overrule $arguments)
     * @return string
     */
    public function render($value = null, \RKW\RkwMailer\Domain\Model\QueueMail $queueMail = null, \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient = null, $isPlaintext = false, $additionalParams = array())
    {
        \TYPO3\CMS\Core\Utility\GeneralUtility::deprecationLog(__CLASS__ . ': This ViewHelper will be removed soon. Use \RKW\RkwMailer\ViewHelpers\Frontend\Replace\RedirectLinksViewHelper instead.');
        return parent::render($value, $queueMail, $queueRecipient, $isPlaintext, $additionalParams);
    }

}