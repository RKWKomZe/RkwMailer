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
 * StatisticMail
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class StatisticOpening extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * queueMail
     *
     * @var \RKW\RkwMailer\Domain\Model\QueueMail
     */
    protected $queueMail;

    /**
     * queueRecipient
     *
     * @var \RKW\RkwMailer\Domain\Model\QueueRecipient
     */
    protected $queueRecipient;

    /**
     * link
     *
     * @var \RKW\RkwMailer\Domain\Model\Link
     */
    protected $link;

    /**
     * pixel
     *
     * @var integer
     */
    protected $pixel = 0;

    /**
     * clickCount
     *
     * @var integer
     */
    protected $clickCount = 0;


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
     * Sets the mailQueue
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return void

    public function setQueueMail($queueMail)
    {
        $this->queueMail = $queueMail;
    }
     * */

    /**
     * Returns the queueRecipient
     *
     * @return \RKW\RkwMailer\Domain\Model\QueueRecipient
     */
    public function getQueueRecipient()
    {
        return $this->queueRecipient;
    }

    /**
     * Sets the queueRecipient
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @return void

    public function setQueueRecipient($queueRecipient)
    {
        $this->queueRecipient = $queueRecipient;
    }
     */


    /**
     * Returns the link
     *
     * @return \RKW\RkwMailer\Domain\Model\Link
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Sets the link
     *
     * @param \RKW\RkwMailer\Domain\Model\Link $link
     * @return void

    public function setLink($link)
    {
        $this->link = $link;
    }
     */

    /**
     * Returns the pixel
     *
     * @return integer $pixel
     */
    public function getPixel()
    {
        return $this->pixel;
    }

    /**
     * Sets the pixel
     *
     * @param integer $pixel
     * @return void
     */
    public function setPixel($pixel)
    {
        $this->pixel = $pixel;
    }

    /**
     * Returns the clickCount
     *
     * @return integer $clickCount
     */
    public function getClickCount()
    {
        return $this->clickCount;
    }

    /**
     * Sets the clickCount
     *
     * @param integer $clickCount
     * @return void
     */
    public function setClickCount($clickCount)
    {
        $this->clickCount = $clickCount;
    }

}