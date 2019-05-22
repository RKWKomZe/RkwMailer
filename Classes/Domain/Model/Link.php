<?php

namespace RKW\RkwMailer\Domain\Model;

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
 * Link
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Link extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * queueMail
     *
     * @var \RKW\RkwMailer\Domain\Model\QueueMail
     */
    protected $queueMail;

    /* @toDo: Remove completely */
    /**
     * statisticOpenings
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwMailer\Domain\Model\StatisticOpening>

    protected $statisticOpenings;
     * */

    /**
     * crdate
     *
     * @var string
     */
    protected $crdate;

    /**
     * hash
     *
     * @var string
     */
    protected $hash;

    /**
     * url
     *
     * @var string
     */
    protected $url;


    /**
     * Constructor
     */
    public function __construct()
    {
        //$this->initializeObject();
    }

    /* @toDo: Remove completely */
    /**
     * Initialize object storage

    public function initializeObject()
    {
        $this->statisticOpenings = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }*/


    /**
     * Returns the queueMail
     *
     * @return \RKW\RkwMailer\Domain\Model\QueueMail
     */
    public function getQueueMail()
    {
        return $this->queueMail;
    }

    /**
     * Sets the queueMail
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return void
     */
    public function setQueueMail($queueMail)
    {
        $this->queueMail = $queueMail;
    }


    /* @toDo: Remove completely */
    /**
     * Adds a statisticOpenings
     *
     * @param \RKW\RkwMailer\Domain\Model\StatisticOpening $statisticOpening
     * @return void
     * @api

    public function addStatisticOpenings(\RKW\RkwMailer\Domain\Model\StatisticOpening $statisticOpening)
    {
        $this->statisticOpenings->attach($statisticOpening);
    }*/


    /**
     * Removes a statisticOpenings
     *
     * @param \RKW\RkwMailer\Domain\Model\StatisticOpening $statisticOpening
     * @return void
     * @api

    public function removeStatisticOpenings(\RKW\RkwMailer\Domain\Model\StatisticOpening $statisticOpening)
    {
        $this->statisticOpenings->detach($statisticOpening);
    }*/

    /**
     * Returns the statisticOpenings
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage An object storage containing the statisticOpenings
     * @api

    public function getStatisticOpenings()
    {
        return $this->statisticOpenings;
    }*/

    /**
     * Sets the statisticOpenings
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $statisticOpenings
     * @return void
     * @api

    public function setStatisticOpenings(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $statisticOpenings)
    {
        $this->statisticOpenings = $statisticOpenings;
    }*/

    /**
     * Returns the crdate
     *
     * @return string $crdate
     */
    public function getCrdate()
    {
        return $this->crdate;
    }

    /**
     * Sets the crdate
     *
     * @param string $crdate
     * @return void
     */
    public function setCrdate($crdate)
    {
        $this->crdate = $crdate;
    }

    /**
     * Returns the hash
     *
     * @return string $hash
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Sets the hash
     *
     * @param string $hash
     * @return void
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * Returns the url
     *
     * @return string $url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Sets the url
     *
     * @param string $url
     * @return void
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

}