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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * QueueMail
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class QueueMail extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * crdate
     *
     * @var integer
     */
    protected $crdate;

    /**
     * sorting
     *
     * @var integer
     */
    protected $sorting = 0;

    /**
     * status
     *
     * @var integer
     */
    protected $status = 1;

    /**
     * type
     *
     * @var integer
     */
    protected $type = 0;


    /**
     * pipeline
     *
     * @var bool
     */
    protected $pipeline = false;


    /**
     * fromName
     *
     * @var string
     */
    protected $fromName = '';

    /**
     * fromAddress
     *
     * @var string
     */
    protected $fromAddress = '';

    /**
     * replyToName
     *
     * @var string
     */
    protected $replyToName = '';
    
    /**
     * replyToAddress
     *
     * @var string
     */
    protected $replyToAddress = '';

    /**
     * returnPath
     *
     * @var string
     */
    protected $returnPath = '';

    /**
     * subject
     *
     * @var string
     */
    protected $subject = '';


    /**
     * bodyText
     *
     * @var string
     */
    protected $bodyText = '';


    /**
     * attachmentPath
     *
     * @var string
     */
    protected $attachmentPaths = '';

    
    /**
     * attachment
     *
     * @var string
     * @deprecated 
     */
    protected $attachment = '';


    /**
     * attachmentType
     *
     * @var string
     * @deprecated 
     */
    protected $attachmentType = '';

    /**
     * attachmentName
     *
     * @var string
     * @deprecated 
     */
    protected $attachmentName = '';

    /**
     * plaintextTemplate
     *
     * @var string
     */
    protected $plaintextTemplate = '';

    
    /**
     * htmlTemplate
     *
     * @var string
     */
    protected $htmlTemplate = '';

    
    /**
     * calendarTemplate
     *
     * @var string
     */
    protected $calendarTemplate = '';


    /**
     * templatePaths
     *
     * @var string
     */
    protected $templatePaths = '';


    /**
     * layoutPaths
     *
     * @var string
     */
    protected $layoutPaths = '';

    /**
     * partialPaths
     *
     * @var string
     */
    protected $partialPaths = '';


    /**
     * category
     *
     * @var string
     */
    protected $category = '';


    /**
     * campaignParameter
     *
     * @var string
     */
    protected $campaignParameter = '';


    /**
     * priority
     *
     * @var integer
     */
    protected $priority = 3;


    /**
     * settingsPid
     *
     * @var integer
     */
    protected $settingsPid = 0;

      /**
     * settings
     *
     * @var array
     */
    protected $settings = array();


    /**
     * queueMail
     *
     * @var \RKW\RkwMailer\Domain\Model\MailingStatistics
     */
    protected $mailingStatistics;
    

    /**
     * tstampFavSending
     *
     * @var integer
     * @deprecated
     */
    protected $tstampFavSending = 0;

    /**
     * tstampRealSending
     *
     * @var integer
     * @deprecated
     */
    protected $tstampRealSending = 0;

    
    /**
     * tstampSendFinish
     *
     * @var integer
     * @deprecated
     */
    protected $tstampSendFinish = 0;
    

    /**
     * total
     *
     * @var integer
     * @deprecated
     */
    protected $total;


    /**
     * sent
     *
     * @var integer
     * @deprecated
     */
    protected $sent;


    /**
     * successful
     *
     * @var integer
     * @deprecated
     */
    protected $successful;


    /**
     * failed
     *
     * @var integer
     * @deprecated
     */
    protected $failed;

    /**
     * deferred
     *
     * @var integer
     * @deprecated
     */
    protected $deferred;

    /**
     * bounced
     *
     * @var integer
     * @deprecated
     */
    protected $bounced;


    /**
     * opened
     *
     * @var integer
     * @deprecated
     */
    protected $opened;


    /**
     * clicked
     *
     * @var integer
     * @deprecated 
     */
    protected $clicked;
    
    

    /**
     * Returns the crdate
     *
     * @return integer $crdate
     */
    public function getCrdate(): int
    {
        return $this->crdate;
    }

    /**
     * Sets the crdate
     *
     * @param integer $crdate
     * @return void
     */
    public function setCrdate(int $crdate)
    {
        $this->crdate = $crdate;
    }

    /**
     * Returns the sorting
     *
     * @return integer $sorting
     */
    public function getSorting(): int 
    {
        return $this->sorting;
    }

    /**
     * Sets the sorting
     *
     * @param integer $sorting
     * @return void
     */
    public function setSorting(int $sorting)
    {
        $this->sorting = $sorting;
    }


    /**
     * Returns the status
     *
     * @return integer $status
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
    public function setStatus(int $status)
    {
        $this->status = $status;
    }

    /**
     * Returns the type
     *
     * @return integer $type
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * Sets the type
     *
     * @param integer $type
     * @return void
     */
    public function setType(int $type)
    {
        $this->type = $type;
    }

    /**
     * Returns the pipeline
     *
     * @return bool $pipeline
     */
    public function getPipeline(): bool
    {
        return $this->pipeline;
    }

    /**
     * Sets the pipeline
     *
     * @param bool $pipeline
     * @return void
     */
    public function setPipeline(bool $pipeline)
    {
        $this->pipeline = $pipeline;
    }

    /**
     * Returns the fromName
     *
     * @return string $fromName
     */
    public function getFromName(): string
    {
        return $this->fromName;
    }

    /**
     * Sets the fromName
     *
     * @param string $fromName
     * @return void
     */
    public function setFromName(string $fromName)
    {
        $this->fromName = $fromName;
    }

    /**
     * Returns the fromAddress
     *
     * @return string $fromAddress
     */
    public function getFromAddress(): string
    {
        return $this->fromAddress;
    }

    /**
     * Sets the fromAddress
     *
     * @param string $fromAddress
     * @return void
     */
    public function setFromAddress(string $fromAddress)
    {
        $this->fromAddress = EmailValidator::cleanUpEmail($fromAddress);
    }


    /**
     * Returns the replyToName
     *
     * @return string $replyToName
     */
    public function getReplyToName(): string
    {
        return $this->replyToName;
    }

    /**
     * Sets the replyToName
     *
     * @param string $replyToName
     * @return void
     */
    public function setReplyToName(string $replyToName)
    {
        $this->replyToName = $replyToName;
    }
    
    
    /**
     * Returns the replyToAddress
     *
     * @return string $replyToAddress
     */
    public function getReplyToAddress(): string
    {
        return $this->replyToAddress;
    }

    /**
     * Sets the replyToAddress
     *
     * @param string $replyToAddress
     * @return void
     */
    public function setReplyToAddress(string $replyToAddress)
    {
        $this->replyToAddress = EmailValidator::cleanUpEmail($replyToAddress);
    }

    /**
     * Sets the replyAddress
     *
     * @param string $replyAddress
     * @return void
     * @deprecated This method is deprecated. Please use setReplyToAddress() instead.
     */
    public function setReplyAddress(string $replyAddress)
    {
        \TYPO3\CMS\Core\Utility\GeneralUtility::deprecationLog(__CLASS__ . ': This method is deprecated. Please use setReplyToAddress() instead.');
        $this->setReplyToAddress($replyAddress);
    }
    
    /**
     * Returns the returnPath
     *
     * @return string $returnPath
     */
    public function getReturnPath(): string
    {
        return $this->returnPath;
    }

    /**
     * Sets the returnPath
     *
     * @param string $returnPath
     * @return void
     */
    public function setReturnPath(string $returnPath)
    {
        $this->returnPath = EmailValidator::cleanUpEmail($returnPath);
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
    public function setSubject(string $subject)
    {
        $this->subject = $subject;
    }

    /**
     * Returns the bodyText
     *
     * @return string $bodyText
     */
    public function getBodyText(): string
    {
        return $this->bodyText;
    }

    /**
     * Sets the bodyText
     *
     * @param string $bodyText
     * @return void
     */
    public function setBodyText(string $bodyText)
    {
        $this->bodyText = $bodyText;
    }
    

    /**
     * Returns the attachmentPath
     *
     * @return array $attachmentPath
     */
    public function getAttachmentPaths(): array
    {
        $paths = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->attachmentPaths, true);
        return $paths;
    }

    
    /**
     * Sets the attachmentPaths
     *
     * @param array $attachmentPaths
     * @return void
     */
    public function setAttachmentPaths (array $attachmentPaths): void
    {
        $this->attachmentPaths = implode(',', $attachmentPaths);
    }

    
    /**
     * Adds an attachmentPath
     *
     * @param string $attachmentPath
     * @return void
     */
    public function addAttachmentPath(string $attachmentPath)
    {
        $paths = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->attachmentPaths);
        $paths[] = $attachmentPath;
        $this->attachmentPaths = implode(',', $paths);
    }


    /**
     * Adds attachmentPaths
     *
     * @param array $attachmentPaths
     * @return void
     */
    public function addAttachmentPaths(array $attachmentPaths)
    {
        if (is_array($attachmentPaths)) {
            $paths = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->attachmentPaths, true);
            $this->attachmentPaths = implode(',', array_merge($paths, $attachmentPaths));
        }
    }
    
    
    /**
     * Returns the attachment
     *
     * @return string $attachment
     * @deprecated use $this->getAttachmentPath() instead
     */
    public function getAttachment()
    {
        return $this->attachment;
    }
    

    /**
     * Sets the attachment
     *
     * @param string $attachment
     * @return void
     * @deprecated use $this->setAttachmentPath() instead
     */
    public function setAttachment($attachment)
    {
        $this->attachment = $attachment;
    }

    /**
     * Returns the attachment
     *
     * @return integer $attachment
     * @deprecated 
     */
    public function getAttachmentType()
    {
        return $this->attachmentType;
    }

    /**
     * Sets the attachment
     *
     * @param string $attachmentType
     * @return void
     * @deprecated 
     */
    public function setAttachmentType($attachmentType)
    {
        $this->attachmentType = $attachmentType;
    }

    /**
     * Returns the attachment
     *
     * @return integer $attachment
     * @deprecated 
     */
    public function getAttachmentName()
    {
        return $this->attachmentName;
    }

    /**
     * Sets the attachment
     *
     * @param string $attachmentName
     * @return void
     * @deprecated 
     */
    public function setAttachmentName($attachmentName)
    {
        $this->attachmentName = $attachmentName;
    }

    /**
     * Returns the plaintextTemplate
     *
     * @return string $plaintextTemplate
     */
    public function getPlaintextTemplate(): string
    {
        return $this->plaintextTemplate;
    }

    /**
     * Sets the plaintextTemplate
     *
     * @param string $plaintextTemplate
     * @return void
     */
    public function setPlaintextTemplate(string $plaintextTemplate)
    {
        $this->plaintextTemplate = $plaintextTemplate;
    }

    /**
     * Returns the htmlTemplate
     *
     * @return string $htmlTemplate
     */
    public function getHtmlTemplate(): string
    {
        return $this->htmlTemplate;
    }

    /**
     * Sets the htmlTemplate
     *
     * @param string $htmlTemplate
     * @return void
     */
    public function setHtmlTemplate(string $htmlTemplate)
    {
        $this->htmlTemplate = $htmlTemplate;
    }

    /**
     * Returns the calendarTemplate
     *
     * @return string $calendarTemplate
     */
    public function getCalendarTemplate(): string
    {
        return $this->calendarTemplate;
    }

    /**
     * Sets the calendarTemplate
     *
     * @param string $calendarTemplate
     * @return void
     */
    public function setCalendarTemplate(string $calendarTemplate)
    {
        $this->calendarTemplate = $calendarTemplate;
    }


    /**
     * Returns the layoutPath
     *
     * @return array
     * @throws \Exception
     */
    public function getLayoutPaths(): array
    {
        $paths = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->layoutPaths, true);
        return $paths;
    }


    /**
     * Sets the layoutPaths
     *
     * @param array $layoutPaths
     * @return void
     */
    public function setLayoutPaths(array $layoutPaths)
    {
        $this->layoutPaths = implode(',', $layoutPaths);
    }


    /**
     * Sets the layoutPath
     *
     * @param string $layoutPath
     * @return void
     * @deprecated use addLayoutPath or setLayoutPaths instead
     */
    public function setLayoutPath(string $layoutPath)
    {
        $this->addLayoutPath($layoutPath);
    }


    /**
     * Adds an layoutPath
     *
     * @param string $layoutPath
     * @return void
     */
    public function addLayoutPath(string $layoutPath)
    {
        $paths = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->layoutPaths);
        $paths[] = $layoutPath;
        $this->layoutPaths = implode(',', $paths);
    }


    /**
     * Adds layoutPaths
     *
     * @param array $layoutPaths
     * @return void
     */
    public function addLayoutPaths(array $layoutPaths)
    {
        if (is_array($layoutPaths)) {
            $paths = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->layoutPaths, true);
            $this->layoutPaths = implode(',', array_merge($paths, $layoutPaths));
        }
    }


    /**
     * Returns the partialPath
     *
     * @return array
     * @throws \Exception
     */
    public function getPartialPaths(): array
    {
        $paths = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->partialPaths, true);
        return $paths;
    }


    /**
     * Sets the partialPaths
     *
     * @param array $partialPaths
     * @return void
     */
    public function setPartialPaths(array $partialPaths)
    {
        $this->partialPaths = implode(',', $partialPaths);
    }


    /**
     * Sets the partialPath
     *
     * @param string $partialPath
     * @return void
     * @deprecated use addPartialPath or setPartialPaths instead
     */
    public function setPartialPath(string $partialPath)
    {
        $this->addPartialPath($partialPath);
    }


    /**
     * Adds an partialPath
     *
     * @param string $partialPath
     * @return void
     */
    public function addPartialPath(string $partialPath)
    {
        $paths = GeneralUtility::trimExplode(',', $this->partialPaths, true);
        $paths[] = $partialPath;
        $this->partialPaths = implode(',', $paths);
    }


    /**
     * Adds partialPaths
     *
     * @param array $partialPaths
     * @return void
     */
    public function addPartialPaths(array $partialPaths)
    {
        if (is_array($partialPaths)) {
            $paths = GeneralUtility::trimExplode(',', $this->partialPaths, true);
            $this->partialPaths = implode(',', array_merge($paths, $partialPaths));
        }
    }


    /**
     * Returns the templatePath
     *
     * @return array
     * @throws \Exception
     */
    public function getTemplatePaths(): array 
    {
        $paths = GeneralUtility::trimExplode(',', $this->templatePaths, true);
        return $paths;
    }


    /**
     * Sets the templatePaths
     *
     * @param array $templatePaths
     * @return void
     */
    public function setTemplatePaths(array $templatePaths)
    {
        $this->templatePaths = implode(',', $templatePaths);
    }


    /**
     * Sets the templatePath
     *
     * @param string $templatePath
     * @return void
     * @deprecated use addTemplatePath or setTemplatePaths instead
     */
    public function setTemplatePath(string $templatePath)
    {
        $this->addTemplatePath($templatePath);
    }


    /**
     * Adds an templatePath
     *
     * @param string $templatePath
     * @return void
     */
    public function addTemplatePath(string $templatePath)
    {
        $paths = GeneralUtility::trimExplode(',', $this->templatePaths, true);
        $paths[] = $templatePath;
        $this->templatePaths = implode(',', $paths);
    }


    /**
     * Adds templatePaths
     *
     * @param array $templatePaths
     * @return void
     */
    public function addTemplatePaths(array $templatePaths)
    {
        if (is_array($templatePaths)) {
            $paths = GeneralUtility::trimExplode(',', $this->templatePaths, true);
            $this->templatePaths = implode(',', array_merge($paths, $templatePaths));
        }
    }


    /**
     * Returns the category
     *
     * @return string $category
     */
    public function getCategory(): string
    {
        return $this->category;
    }


    /**
     * Sets the category
     *
     * @param string $category
     * @return void
     */
    public function setCategory(string $category)
    {
        $this->category = $category;
    }


    /**
     * Returns the campaignParameter
     *
     * @return string $campaignParameter
     */
    public function getCampaignParameter(): string
    {
        return $this->campaignParameter;
    }

    /**
     * Returns the exploded campaignParameter
     *
     * @return array
     */
    public function getCampaignParameterExploded(): array
    {

        // explode by ampersand
        $implodedFirst = explode('&', str_replace('?', '', $this->campaignParameter));

        // now explode by equal-sign
        $result = array();
        foreach ($implodedFirst as $entry) {

            $tempExplode = explode('=', $entry);
            if (
                (count($tempExplode) == 2)
                && (strlen(trim($tempExplode[0])) > 0)
            ) {
                $result [trim($tempExplode[0])] = trim($tempExplode[1]);
            }

        }

        return $result;
    }

    /**
     * Sets the campaignParameter
     *
     * @param string $campaignParameter
     * @return void
     */
    public function setCampaignParameter(string $campaignParameter)
    {
        $this->campaignParameter = $campaignParameter;
    }


    /**
     * Returns the priority
     *
     * @return integer $priority
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Sets the priority
     *
     * @param integer $priority
     * @return void
     */
    public function setPriority(int $priority)
    {
        $this->priority = $priority;
    }


    /**
     * Returns the settingsPid
     *
     * @return integer $settingsPid
     */
    public function getSettingsPid(): int
    {
        return $this->settingsPid;
    }

    /**
     * Sets the settingsPid
     *
     * @param integer $settingsPid
     * @return void
     */
    public function setSettingsPid(int $settingsPid)
    {
        $this->settingsPid = $settingsPid;
    }

    /**
     * Returns the mailingStatistics
     *
     * @return \RKW\RkwMailer\Domain\Model\MailingStatistics $mailingStatistics
     */
    public function getMailingStatistics()
    {
        return $this->mailingStatistics;
    }

    /**
     * Sets the mailingStatistics
     *
     * @param \RKW\RkwMailer\Domain\Model\MailingStatistics $mailingStatistics
     * @return void
     */
    public function setMailingStatistics(\RKW\RkwMailer\Domain\Model\MailingStatistics $mailingStatistics): void
    {
        $this->mailingStatistics = $mailingStatistics;
    }


    /**
     * Returns the tstampFavSending
     *
     * @return integer $tstampFavSending
     * @deprecated
     */
    public function getTstampFavSending(): int
    {
        return $this->tstampFavSending;
    }

    /**
     * Sets the tstampFavSending
     *
     * @param integer $tstampFavSending
     * @return void
     * @deprecated
     */
    public function setTstampFavSending(int $tstampFavSending)
    {
        $this->tstampFavSending = $tstampFavSending;
    }

    /**
     * Returns the tstampRealSending
     *
     * @return integer $tstampRealSending
     * @deprecated
     */
    public function getTstampRealSending(): int
    {
        return $this->tstampRealSending;
    }

    /**
     * Sets the tstampRealSending
     *
     * @param integer $tstampRealSending
     * @return void
     * @deprecated
     */
    public function setTstampRealSending(int $tstampRealSending)
    {
        $this->tstampRealSending = $tstampRealSending;
    }

    /**
     * Returns the tstampSendFinish
     *
     * @return integer $tstampSendFinish
     * @deprecated
     */
    public function getTstampSendFinish(): int
    {
        return $this->tstampSendFinish;
    }

    /**
     * Sets the tstampSendFinish
     *
     * @param integer $tstampSendFinish
     * @return void
     * @deprecated
     */
    public function setTstampSendFinish(int $tstampSendFinish)
    {
        $this->tstampSendFinish = $tstampSendFinish;
    }


    /**
     * Returns the total
     *
     * @return integer $total
     * @deprecated
     */
    public function getTotal(): int
    {
        return $this->total;
    }


    /**
     * Returns the sent
     *
     * @return integer $sent
     * @deprecated
     */
    public function getSent(): int
    {
        return $this->sent;
    }


    /**
     * Returns the successful
     *
     * @return integer $successful
     * @deprecated
     */
    public function getSuccessful(): int
    {
        return $this->successful;
    }


    /**
     * Returns the failed
     *
     * @return integer $failed
     * @deprecated
     */
    public function getFailed(): int
    {
        return $this->failed;
    }


    /**
     * Returns the deferred
     *
     * @return integer $deferred
     * @deprecated
     */
    public function getDeferred(): int
    {
        return $this->deferred;
    }


    /**
     * Returns the bounced
     *
     * @return integer $bounced
     * @deprecated
     */
    public function getBounced(): int
    {
        return $this->bounced;
    }


    /**
     * Returns the opened
     *
     * @return integer $opened
     * @deprecated
     */
    public function getOpened(): int
    {
        return $this->opened;
    }

    /**
     * Returns the clicked
     *
     * @return integer $clicked
     * @deprecated 
     */
    public function getClicked(): int
    {
        return $this->clicked;
    }

}