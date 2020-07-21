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
 * Class ComposeDateTimePartsViewHelper
 *
 * @author Carlos Meyer <cm@davitec.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ComposeDateTimePartsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Managed datetime output for detail view and email templates
     * -----------------------------------
     * -> Old template code which we've replaced with this ViewHelper:
     * {event.start -> f:format.date(format:"d.m.Y")}
     * <f:if condition="{event.start -> f:format.date(format:'Hi')}">,
     *        {event.start -> f:format.date(format:"H:i")}
     *        <f:if condition="{event.start -> f:format.date(format:'d.m.Y')} <> {event.end -> f:format.date(format:'d.m.Y')}">
     *            <f:else>
     *                <f:translate key="tx_rkwevents_fluid.partials_event_info_time.time_after" />
     *            </f:else>
     *        </f:if>
     *        <f:if condition="{event.end -> f:format.date(format:'Hi')}">
     *            <f:else>
     *                <f:translate key="tx_rkwevents_fluid.partials_event_info_time.time_after" />
     *            </f:else>
     *        </f:if>
     * </f:if>
     * <f:if condition="{event.end}">
     *        - <f:if condition="{event.start -> f:format.date(format:'d.m.Y')} == {event.end -> f:format.date(format:'d.m.Y')}">
     *            <f:else>
     *                {event.end -> f:format.date(format:"d.m.Y")},
     *            </f:else>
     *        </f:if>
     *        <f:if condition="{event.end -> f:format.date(format:'Hi')}">
     *            {event.end -> f:format.date(format:"H:i")}
     *            <f:translate key="tx_rkwevents_fluid.partials_event_info_time.time_after" />
     *        </f:if>
     * </f:if>
     * -----------------------------------
     *
     * @param \RKW\RkwEvents\Domain\Model\Event $event
     * @param string $languageKey
     * @return boolean
     */
    public function render($event, $languageKey = 'default')
    {
        // 1. start date & time
        // set always the starting date
        $output = date("d.m.Y", $event->getStart());

        if (date("Hi", $event->getStart())) {
            $output .= ', ';
            $output .= date("H:i", $event->getStart());
            // if startDate != endDate OR no endDate is given, so close here with time_after-string
            if (
                date("d.m.Y", $event->getStart()) != date("d.m.Y", $event->getEnd())
                || !date("Hi", $event->getEnd())
            ) {
                $output .= ' ' . \RKW\RkwBasics\Utility\FrontendLocalization::translate('tx_rkwevents_fluid.partials_event_info_time.time_after', 'rkw_events', null, $languageKey);
            }
        }

        // 2. end date & time
        if ($event->getEnd()) {
            $output .= ' - ';
            if (date("d.m.Y", $event->getStart()) != date("d.m.Y", $event->getEnd())) {
                $output .= date("d.m.Y", $event->getEnd());
                $output .= ', ';
            }
            if (date("Hi", $event->getEnd())) {
                $output .= date("H:i", $event->getEnd());
                $output .= ' ' . \RKW\RkwBasics\Utility\FrontendLocalization::translate('tx_rkwevents_fluid.partials_event_info_time.time_after', 'rkw_events', null, $languageKey);
            }
        }

        return $output;
        //===
    }
}