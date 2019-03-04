<?php

namespace RKW\RkwMailer\Utility;

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
 * TimePeriod
 * Due to the large commonalities, the QueueMail-Model is derived and the other propertys for
 * QueueRecipient specially created
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TimePeriodUtility
{

    /**
     * function getTimePeriod
     * The given param is a numeric identifier between 0-8 which stands for some time period
     *
     * @param integer $timeFrame
     * @return array $spaceOfTime
     */
    public static function getTimePeriod($timeFrame = 0)
    {

        $spaceOfTime = array();

        switch ($timeFrame) {
            case 0:
                // complete
                $spaceOfTime['from'] = mktime(0, 0, 0, date("m"), date("d"), date(1970));
                $spaceOfTime['to'] = time();
                break;
            case 1:
                // this month
                $spaceOfTime['from'] = mktime(0, 0, 0, date("m"), date(1), date("Y"));
                $spaceOfTime['to'] = time();
                break;
            case 2:
                // last month
                $spaceOfTime['from'] = mktime(0, 0, 0, date("m") - 1, date(1), date("Y"));
                $spaceOfTime['to'] = strtotime('+1 month', $spaceOfTime['from']);
                break;
            case 3:
                // this quarter
                $quarters = self::findOutQuarter();
                $spaceOfTime['from'] = $quarters['this']['start'];
                $spaceOfTime['to'] = $quarters['this']['end'];
                break;
            case 4:
                // last quarter
                $quarters = self::findOutQuarter();
                $spaceOfTime['from'] = $quarters['last']['start'];
                $spaceOfTime['to'] = $quarters['last']['end'];
                break;
            case 5:
                // this half-year
                $halfyear = self::findOutHalfYear();
                $spaceOfTime['from'] = $halfyear['this']['start'];
                $spaceOfTime['to'] = $halfyear['this']['end'];
                break;
            case 6:
                // last half-year
                $halfyear = self::findOutHalfYear();
                $spaceOfTime['from'] = $halfyear['last']['start'];
                $spaceOfTime['to'] = $halfyear['last']['end'];
                break;
            case 7:
                // this year
                $spaceOfTime['from'] = mktime(0, 0, 0, date(1), date(1), date("Y"));
                $spaceOfTime['to'] = time();
                break;
            case 8:
                // last year
                $spaceOfTime['from'] = mktime(0, 0, 0, date(1), date(1), date("Y") - 1);
                $spaceOfTime['to'] = mktime(0, 0, 0, date(12), date(31), date("Y") - 1);
                break;
        }

        return $spaceOfTime;
        //===

    }


    /**
     * findOutQuarter
     * get this quarter, then conclude last quarter
     *
     * @return array $quarters
     */
    public static function findOutQuarter()
    {

        $quarters = array();

        // first quarter
        if (time() > mktime(0, 0, 0, date(1), date(1), date("Y"))
            && time() < mktime(0, 0, 0, date(4), date(1), date("Y"))
        ) {
            $quarters['this']['start'] = mktime(0, 0, 0, date(1), date(1), date("Y")); //
            $quarters['this']['end'] = mktime(0, 0, 0, date(4), date(1), date("Y")); //
        }
        // second quarter
        if (time() > mktime(0, 0, 0, date(4), date(1), date("Y"))
            && time() < mktime(0, 0, 0, date(7), date(1), date("Y"))
        ) {
            $quarters['this']['start'] = mktime(0, 0, 0, date(4), date(1), date("Y")); //
            $quarters['this']['end'] = mktime(0, 0, 0, date(7), date(1), date("Y")); //
        }
        // third quarter
        if (time() > mktime(0, 0, 0, date(7), date(1), date("Y"))
            && time() < mktime(0, 0, 0, date(10), date(1), date("Y"))
        ) {
            $quarters['this']['start'] = mktime(0, 0, 0, date(7), date(1), date("Y")); //
            $quarters['this']['end'] = mktime(0, 0, 0, date(10), date(1), date("Y")); //
        }
        // fourth quarter
        if (time() > mktime(0, 0, 0, date(10), date(1), date("Y"))
            && time() < mktime(0, 0, 0, date(1), date(1), date("Y") + 1)
        ) {
            $quarters['this']['start'] = mktime(0, 0, 0, date(10), date(1), date("Y")); //
            $quarters['this']['end'] = mktime(0, 0, 0, date(1), date(1), date("Y") + 1); //
        }


        // last quarter
        $quarters['last']['start'] = $quarters['this']['start'] - (60 * 60 * 24 * 90);
        $quarters['last']['end'] = $quarters['this']['start'];

        return $quarters;
        //===
    }


    /**
     * findOutHalfyear
     * get this halfyear, then conclude last halfyear
     *
     * @return array $quarters
     */
    public static function findOutHalfYear()
    {

        $halfYear = array();

        // first halfyear
        if (time() > mktime(0, 0, 0, date(1), date(1), date("Y"))
            && time() < mktime(0, 0, 0, date(7), date(1), date("Y"))
        ) {
            $halfYear['this']['start'] = mktime(0, 0, 0, date(1), date(1), date("Y")); //
            $halfYear['this']['end'] = mktime(0, 0, 0, date(7), date(1), date("Y")); //
        }
        // second halfyear
        if (time() > mktime(0, 0, 0, date(7), date(1), date("Y"))
            && time() < mktime(0, 0, 0, date(1), date(1), date("Y") + 1)
        ) {
            $halfYear['this']['start'] = mktime(0, 0, 0, date(7), date(1), date("Y")); //
            $halfYear['this']['end'] = mktime(0, 0, 0, date(1), date(1), date("Y") + 1); //
        }


        // last halfyear
        $halfYear['last']['start'] = $halfYear['this']['start'] - (60 * 60 * 24 * 180);
        $halfYear['last']['end'] = $halfYear['this']['start'];

        return $halfYear;
        //===

    }

}