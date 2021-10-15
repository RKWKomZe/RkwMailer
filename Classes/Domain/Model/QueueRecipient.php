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
use RKW\RkwMailer\Validation\EmailValidator;

/**
 * QueueRecipient
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class QueueRecipient extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * queueMail
     *
     * @var \RKW\RkwMailer\Domain\Model\QueueMail
     */
    protected $queueMail;

    
    /**
     * email
     *
     * @var string
     */
    protected $email = '';

    
    /**
     * title
     *
     * @var string
     */
    protected $title = '';

    
    /**
     * salutation
     *
     * @var int
     */
    protected $salutation = 99;

    
    /**
     * firstName
     *
     * @var string
     */
    protected $firstName = '';

    
    /**
     * lastName
     *
     * @var string
     */
    protected $lastName = '';

    
    /**
     * subject
     *
     * @var string
     */
    protected $subject = '';

    
    /**
     * marker
     *
     * @var string
     */
    protected $marker = '';

    
    /**
     * markerUnserialized
     *
     * @var array
     */
    protected $markerUnserialized = [];

    
    /**
     * status
     *
     * @var integer
     */
    protected $status = 0;

    
    /**
     * languageCode
     *
     * @var string
     */
    protected $languageCode = 'de';



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
    public function setQueueMail(\RKW\RkwMailer\Domain\Model\QueueMail $queueMail): void
    {
        $this->queueMail = $queueMail;
    }


    /**
     * Returns the email
     *
     * @return string $email
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Sets the email
     *
     * @param string $email
     * @return void
     */
    public function setEmail(string $email): void
    {
        $this->email = EmailValidator::cleanUpEmail($email);
    }

    /**
     * Returns the title
     *
     * @return string $title
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Sets the title
     *
     * @param string $title
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * Returns the salutation
     *
     * @return int $salutation
     */
    public function getSalutation(): int
    {
        return $this->salutation;
    }


    /**
     * Returns the salutation
     *
     * @return string $salutation
     */
    public function getSalutationText(): string
    {
        if ($this->getSalutation() < 99) {

            return \RKW\RkwMailer\Utility\FrontendLocalizationUtility::translate(
                'tx_rkwmailer_domain_model_queuerecipient.salutation.I.' . $this->getSalutation(),
                'rkw_mailer',
                array(),
                $this->getLanguageCode()
            );
        }

        return '';
    }

    /**
     * Sets the salutation
     *
     * @param int $salutation
     * @return void
     */
    public function setSalutation(int $salutation): void
    {
        $this->salutation = $salutation;
    }


    /**
     * Returns the firstName
     *
     * @return string $firstName
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * Sets the firstName
     *
     * @param string $firstName
     * @return void
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * Returns the lastName
     *
     * @return string $lastName
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * Sets the lastName
     *
     * @param string $lastName
     * @return void
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }


    /**
     * Returns the subject
     *
     * @return string $subject
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * Sets the subject
     *
     * @param string $subject
     * @return void
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }


    /**
     * Returns the marker
     *
     * @return array $marker
     */
    public function getMarker(): array
    {
        if ($this->markerUnserialized) {
            return $this->markerUnserialized;
        }

        return ($this->marker ? unserialize($this->marker) : []);
    }

    /**
     * Sets the marker
     *
     * @param array $marker
     * @return void
     */
    public function setMarker(array $marker): void
    {
        $this->markerUnserialized = $marker;
        $this->marker = serialize($marker);
    }
    

    /**
     * Returns the status
     *
     * @return int $status
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Sets the status
     *
     * @param integer $status
     * @return void
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }


    /**
     * Returns the languageCode
     *
     * @return string $languageCode
     */
    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }

    /**
     * Sets the languageCode
     *
     * @param string $languageCode
     * @return void
     */
    public function setLanguageCode(string $languageCode): void
    {
        $this->languageCode = $languageCode;
    }

    
}