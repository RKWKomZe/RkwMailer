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
class StatisticMail extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * queueMail
     *
     * @var \RKW\RkwMailer\Domain\Model\QueueMail
     */
    protected $queueMail;

    /**
     * totalCount
     *
     * @var integer
     */
    protected $totalCount = 0;

    /**
     * contactedCount
     *
     * @var integer
     */
    protected $contactedCount = 0;

    /**
     * bouncesCount
     *
     * @var integer
     */
    protected $bouncesCount = 0;

    /**
     * errorCount
     *
     * @var integer
     */
    protected $errorCount = 0;

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

    /**
     * Returns the totalCount
     *
     * @return integer $totalCount
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * Sets the totalCount
     *
     * @param integer $totalCount
     * @return void
     */
    public function setTotalCount($totalCount)
    {
        $this->totalCount = $totalCount;
    }

    /**
     * Returns the contactedCount
     *
     * @return integer $contactedCount
     */
    public function getContactedCount()
    {
        return $this->contactedCount;
    }

    /**
     * Sets the contactedCount
     *
     * @param integer $contactedCount
     * @return void
     */
    public function setContactedCount($contactedCount)
    {
        $this->contactedCount = $contactedCount;
    }

    /**
     * Returns the bouncesCount
     *
     * @return integer $bouncesCount
     */
    public function getBouncesCount()
    {
        return $this->bouncesCount;
    }

    /**
     * Sets the bouncesCount
     *
     * @param integer $bouncesCount
     * @return void
     */
    public function setBouncesCount($bouncesCount)
    {
        $this->bouncesCount = $bouncesCount;
    }

    /**
     * Returns the errorCount
     *
     * @return integer $errorCount
     */
    public function getErrorCount()
    {
        return $this->errorCount;
    }

    /**
     * Sets the errorCount
     *
     * @param integer $errorCount
     * @return void
     */
    public function setErrorCount($errorCount)
    {
        $this->errorCount = $errorCount;
    }


}