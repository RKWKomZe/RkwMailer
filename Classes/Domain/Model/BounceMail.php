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
 * BounceMail
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class BounceMail extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * status
     *
     * @var string
     */
    protected $status;

    /**
     * type
     *
     * @var string
     */
    protected $type;

    /**
     * email
     *
     * @var string
     */
    protected $email;

    /**
     * subject
     *
     * @var string
     */
    protected $subject;


    /**
     * ruleNumber
     *
     * @var int
     */
    protected $ruleNumber;


    /**
     * ruleCategory
     *
     * @var string
     */
    protected $ruleCategory;


    /**
     * header
     *
     * @var string
     */
    protected $header;

    /**
     * body
     *
     * @var string
     */
    protected $body;


    /**
     * headerFull
     *
     * @var string
     */
    protected $headerFull;


    /**
     * bodyFull
     *
     * @var string
     */
    protected $bodyFull;



    /**
     * Returns the status
     *
     * @return string $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the status
     *
     * @param string $status
     * @return void
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }


    /**
     * Returns the type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the type
     *
     * @param string $type
     * @return void
     */
    public function setType($type)
    {
        $this->type = $type;
    }


    /**
     * Returns the email
     *
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets the email
     *
     * @param string $email
     * @return void
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }


    /**
     * Returns the subject
     *
     * @return string $subject
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Sets the subject
     *
     * @param string $subject
     * @return void
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }



    /**
     * Returns the ruleNumber
     *
     * @return int $ruleNumber
     */
    public function getRuleNumber()
    {
        return $this->ruleNumber;
    }

    /**
     * Sets the ruleNumber
     *
     * @param int $ruleNumber
     * @return void
     */
    public function setRuleNumber($ruleNumber)
    {
        $this->ruleNumber = $ruleNumber;
    }


    /**
     * Returns the ruleCategory
     *
     * @return string $ruleCategory
     */
    public function getRuleCategory()
    {
        return $this->ruleCategory;
    }

    /**
     * Sets the ruleCategory
     *
     * @param string $ruleCategory
     * @return void
     */
    public function setRuleCategory($ruleCategory)
    {
        $this->ruleCategory = $ruleCategory;
    }

    /**
     * Returns the header
     *
     * @return array $header
     */
    public function getHeader()
    {
        return unserialize($this->header);
    }

    /**
     * Sets the header
     *
     * @param array $header
     * @return void
     */
    public function setHeader($header)
    {
        $this->header = serialize($header);
    }


    /**
     * Returns the body
     *
     * @return string $body
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Sets the body
     *
     * @param string $body
     * @return void
     */
    public function setBody($body)
    {
        $this->body = $body;
    }


    /**
     * Returns the headerFull
     *
     * @return string $headerFull
     */
    public function getHeaderFull()
    {
        return $this->headerFull;
    }

    /**
     * Sets the headerFull
     *
     * @param string $headerFull
     * @return void
     */
    public function setHeaderFull($headerFull)
    {
        $this->headerFull = $headerFull;
    }


    /**
     * Returns the bodyFull
     *
     * @return string $bodyFull
     */
    public function getBodyFull()
    {
        return $this->bodyFull;
    }

    /**
     * Sets the bodyFull
     *
     * @param string $bodyFull
     * @return void
     */
    public function setBodyFull($bodyFull)
    {
        $this->bodyFull = $bodyFull;
    }    
    
}