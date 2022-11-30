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
 * StatisticsUtility
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class StatisticsUtility
{

    /**
     * Generate a normalized hash value from the given link
     *
     * @param string $link
     * @return string
     */
    public static function generateLinkHash (
        string $link
    ): string {

        // decode url (just to be sure)
       $link = urldecode($link);

       $parsedUrl = parse_url($link);
       $params = $parsedUrl['query'] ? '?' . $parsedUrl['query'] : '';
       $hashString = $parsedUrl['host'] . '/' . trim($parsedUrl['path'], '/') . $params;
       return sha1($hashString);
    }


    /**
     * Generate a hash value from the given queueRecipient
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @return string
     * @throws \RKW\RkwMailer\Exception
     */
    public static function generateRecipientHash (
        \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
    ): string {

        if ($queueRecipient->_isNew()) {
            throw new \RKW\RkwMailer\Exception('Given object is not persisted.');
        }
        return sha1($queueRecipient->getUid());
    }


    /**
     * Adds parameters to url
     *
     * @param string $url
     * @param array $additionalParams
     * @return string
     */
    public static function addParamsToUrl(
        string $url,
        array $additionalParams = []
    ) : string {

        if ($additionalParams) {

            // add params first and THEN add anchor
            if ($section = parse_url($url, PHP_URL_FRAGMENT)) {
                $url = str_replace('#' . $section, '', $url);
                $section = '#' . $section;
            }
            $url = $url . (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . implode('&', $additionalParams) . $section;
        }

        return $url;
    }



}
