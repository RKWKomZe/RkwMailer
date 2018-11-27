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

use \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use \TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;
use \TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class RecipientSalutationViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RecipientSalutationViewHelper extends AbstractViewHelper implements CompilableInterface
{

    /**
     * Build a full salutation for the queueRecipient
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @param bool $useFirstName
     * @param string $prependText
     * @param string $appendText
     * @return string $string
     */
    public function render(\RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient, $useFirstName = false, $prependText = '', $appendText = '')
    {

        return static::renderStatic(
            array(
                'queueRecipient' => $queueRecipient,
                'useFirstName'   => $useFirstName,
                'appendText'     => $appendText,
                'prependText'    => $prependText,
            ),
            $this->buildRenderChildrenClosure(),
            $this->renderingContext
        );
        //===
    }


    /**
     * Static rendering
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    static public function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $queueRecipient = $arguments['queueRecipient'];
        $useFirstName = $arguments['useFirstName'];
        $appendText = $arguments['appendText'];
        $prependText = $arguments['prependText'];

        $fullName = array();
        if ($queueRecipient->getLastName()) {

            if ($queueRecipient->getSalutationText()) {
                $fullName[] = $queueRecipient->getSalutationText();
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
        if (!$finalName) {
            return trim(($prependText ? $prependText : '')) . ($appendText ? $appendText : '');
            //===
        }

        return ($prependText ? $prependText : '') . trim(implode(' ', $fullName)) . ($appendText ? $appendText : '');
        //===
    }


}