<?php

namespace RKW\RkwMailer\ViewHelpers\Email;

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


use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;


/**
 * Class RecipientSalutationViewHelper
 **
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RecipientSalutationViewHelper extends AbstractViewHelper
{

    use CompileWithRenderStatic;

    /**
     * initializeArguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('queueRecipient', '\RKW\RkwMailer\Domain\Model\QueueRecipient', 'The queue recipient', true);
        $this->registerArgument('useFirstName', 'bool', 'Set to true if first name should be used in salutation', false, false);
        $this->registerArgument('appendText', 'string', 'Set text you want to append to the salutation', false, '');
        $this->registerArgument('prependText', 'string', 'Set text you want to prepend to the salutation', false, '');
        $this->registerArgument('fallbackText', 'string', 'Set text you want to use as general fallback', false, '');
    }


    /**
     * Static rendering
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $arguments['queueRecipient'];
        $useFirstName = ($arguments['useFirstName'] ? true : false);
        $appendText = $arguments['appendText'] ? $arguments['appendText'] : '';
        $prependText = $arguments['prependText'] ? $arguments['prependText'] : '';
        $fallbackText = $arguments['fallbackText'] ? $arguments['fallbackText'] : '';

        $fullName = array();
        if ($queueRecipient->getLastName()) {

            // if salutation has value 2 ("divers" / "mx"), do not print salutation (instead use firstName)
            if (
                $queueRecipient->getSalutationText()
                && ($queueRecipient->getSalutation() != 2)
            ) {
                $fullName[] = $queueRecipient->getSalutationText();
            } else {
                $useFirstName = true;
            }

            if ($queueRecipient->getTitle()) {
                $fullName[] = $queueRecipient->getTitle();
            }

            if (
                ($useFirstName == true)
                && ($queueRecipient->getFirstName())
            ) {
                $fullName[] = ucFirst($queueRecipient->getFirstName());
            }

            $fullName[] = ucFirst($queueRecipient->getLastName());
        }


        $finalName = trim(implode(' ', $fullName));
        if (
            (!trim($queueRecipient->getFirstName()))
            && (!trim($queueRecipient->getLastName()))
        ) {

            if ($fallbackText) {
                return $fallbackText;
            }
            return trim(($prependText ? $prependText : '')) . ($appendText ? $appendText : '');
        }

        return ($prependText ? $prependText : '') . $finalName . ($appendText ? $appendText : '');
    }


}

