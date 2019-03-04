<?php

namespace RKW\RkwMailer\Service;

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

use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * MailService
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MailService
{

    /**
     * Signal name
     *
     * @const string
     */
    const SIGNAL_TO_BEFORE_ATTACH = 'toBeforeAttach';

    /**
     * Signal name
     *
     * @const string
     */
    const SIGNAL_RENDER_TEMPLATE_AFTER_MARKERS = 'renderTemplateAfterMarkers';

    /**
     * Signal name
     *
     * @const string
     */
    const SIGNAL_RENDER_TEMPLATE_AFTER_RENDER = 'renderTemplateAfterRender';

    /**
     * Signal name
     *
     * @const string
     */
    const SIGNAL_SEND_TO_RECIPIENT_BEFORE_SEND = 'sendToRecipientBeforeSend';

    /**
     * Namespace Keyword
     *
     * @const string
     */
    const NAMESPACE_KEYWORD = 'RKW_MAILER_NAMESPACES';

    /**
     * Namespace Keyword
     *
     * @const string
     */
    const NAMESPACE_ARRAY_KEYWORD = 'RKW_MAILER_NAMESPACES_ARRAY';

    /**
     * Debug switch
     *
     * @const string
     */
    const DEBUG_TIME = false;


    /**
     * objectManager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     * @inject
     */
    protected $objectManager;


    /**
     * configurationManager
     *
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @inject
     */
    protected $configurationManager;


    /**
     * persistenceManager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager;

    /**
     * queueMail
     *
     * @var \RKW\RkwMailer\Domain\Model\QueueMail
     */
    protected $queueMail;

    /**
     * QueueMailRepository
     *
     * @var \RKW\RkwMailer\Domain\Repository\QueueMailRepository
     * @inject
     */
    protected $queueMailRepository;

    /**
     * QueueRecipientRepository
     *
     * @var \RKW\RkwMailer\Domain\Repository\QueueRecipientRepository
     * @inject
     */
    protected $queueRecipientRepository;


    /**
     * StatisticMailRepository
     *
     * @var \RKW\RkwMailer\Domain\Repository\StatisticMailRepository
     * @inject
     */
    protected $statisticMailRepository;


    /**
     * QueueMailValidator
     *
     * @var \RKW\RkwMailer\Validation\QueueMailValidator
     * @inject
     */
    protected $queueMailValidator = null;


    /**
     * QueueMailValidator
     *
     * @var \RKW\RkwMailer\Validation\QueueRecipientValidator
     * @inject
     */
    protected $queueRecipientValidator = null;
    
    /**
     * Signal-Slot Dispatcher
     *
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     * @inject
     */
    protected $signalSlotDispatcher;

    /**
     * Logger
     *
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;


    /**
     * The normal settings
     *
     * @var array
     */
    protected $settings = array();


    /**
     * Constructor
     */
    public function __construct()
    {
        //self::debugTime(__LINE__, __METHOD__);
        //$this->initializeService();
        //self::debugTime(__LINE__, __METHOD__);
    }


    /**
     * function initializeService
     *
     * @return void
     */
    public function initializeService()
    {

        // set objects if they haven't been injected yet
        if (!$this->objectManager) {
            $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        }
        if (!$this->configurationManager) {
            $this->configurationManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface');
        }
        if (!$this->persistenceManager) {
            $this->persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
        }
        if (!$this->queueMailRepository) {
            $this->queueMailRepository = $this->objectManager->get('RKW\\RkwMailer\\Domain\\Repository\\QueueMailRepository');
        }
        if (!$this->queueRecipientRepository) {
            $this->queueRecipientRepository = $this->objectManager->get('RKW\\RkwMailer\\Domain\\Repository\\QueueRecipientRepository');
        }
        if (!$this->statisticMailRepository) {
            $this->statisticMailRepository = $this->objectManager->get('RKW\\RkwMailer\\Domain\\Repository\\StatisticMailRepository');
        }
        if (!$this->queueMailValidator) {
            $this->queueMailValidator = $this->objectManager->get('RKW\\RkwMailer\\Validation\\QueueMailValidator');
        }
        if (!$this->queueRecipientValidator) {
            $this->queueRecipientValidator = $this->objectManager->get('RKW\\RkwMailer\\Validation\\QueueRecipientValidator');
        }        

    }


    /**
     * Sets the to
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser|\TYPO3\CMS\Extbase\Domain\Model\BackendUser|array $basicData
     * @param array $additionalData
     * @param bool  $renderTemplates
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @return boolean
     */
    public function setTo($basicData, $additionalData = array(), $renderTemplates = false)
    {
        self::debugTime(__LINE__, __METHOD__);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $newRecipient */
        $newRecipient = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwMailer\\Domain\\Model\\QueueRecipient');

        // if a FrontendUser is given, take it's basic values
        if ($basicData instanceof \TYPO3\CMS\Extbase\Domain\Model\FrontendUser) {

           $this->setQueueRecipientByFrontendUser ($newRecipient, $basicData, $additionalData);

         // if a BackendUser is given, take it's basic values
        } else if ($basicData instanceof \TYPO3\CMS\Extbase\Domain\Model\BackendUser) {

            $this->setQueueRecipientByBackendUser ($newRecipient, $basicData, $additionalData);

        } else if (is_array($basicData)) {

            $additionalData = array_merge($additionalData, $basicData);
        }


        // check additional data and add it
        if (!empty($additionalData)) {
            foreach ($additionalData as $property => $value) {

                $setter = 'set' . ucFirst($property);
                if (
                    (method_exists($newRecipient, $setter))
                    && ($value)
                ) {

                    // reduce marker here!
                    if (strtolower($property) == 'marker') {
                        $newRecipient->$setter($this->reduceMarker($value));
                    } else {
                        $newRecipient->$setter($value);
                    }
                }
            }
        }

        // set storage pid
        $newRecipient->setPid(intval($this->getSettings('storagePid', 'persistence')));

        // Signal slot
        $this->getSignalSlotDispatcher()->dispatch(__CLASS__, self::SIGNAL_TO_BEFORE_ATTACH . ($this->getQueueMail()->getCategory() ? '_' . ucFirst($this->getQueueMail()->getCategory()) : ''), array($this->getQueueMail(), &$newRecipient));

        if ($this->queueRecipientValidator->validate($newRecipient)) {

            // render templates right away?
            if ($renderTemplates) {
                $this->addRecipient($newRecipient);
                $this->renderTemplates($newRecipient);
            } else {
                $this->addRecipient($newRecipient);
            }

            self::debugTime(__LINE__, __METHOD__);

            return true;
            //===
        }

        self::debugTime(__LINE__, __METHOD__);

        return false;
        //===
    }

    /**
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $frontendUser
     * @param array $additionalData
     * @return void
     */
    public function setQueueRecipientByFrontendUser (\RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient, \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $frontendUser, &$additionalData = array())
    {

        // define property mapping - order is important!
        $importPropertyMapper = array(
            'username' => 'email',
            'email' => 'email',
            'title' => 'title',
            'salutation' => 'salutation',
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'subject' => 'subject',
            'languageCode' => 'languageCode'
        );

        // expand mapping for \RKW\RkwRegistration\Domain\Model\FrontendUser
        if ($frontendUser instanceof \RKW\RkwRegistration\Domain\Model\FrontendUser) {
            $importPropertyMapper['txRkwregistrationGender'] = 'salutation';
            $importPropertyMapper['txRkwregistrationLanguageKey'] = 'languageCode';
            $importPropertyMapper['titleText'] = 'title';
        }

        // set all relevant values according to given data
        $this->setQueueRecipientSub($queueRecipient, $frontendUser, $importPropertyMapper, $additionalData);

        /* @toDo: Leeds to problems since this does an implicit update on the object
         * which may lead to persisting data before having received a confirmation via opt-in-mail!!!
         */
        if (!$frontendUser->_isNew()) {
            $queueRecipient->setFrontendUser($frontendUser);
        }

    }


    /**
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $frontendUser
     * @param array $additionalData
     * @return void
     */
    public function setQueueRecipientByBackendUser (\RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient, \TYPO3\CMS\Extbase\Domain\Model\BackendUser $backendUser, &$additionalData = array())
    {

        // define property mapping - order is important!
        $importPropertyMapper = array(
            'username' => 'email',
            'email' => 'email',
            'title' => 'title',
            'salutation' => 'salutation',
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'subject' => 'subject',
            'languageCode' => 'languageCode'
        );

        // expand mapping for \RKW\RkwRegistration\Domain\Model\FrontendUser
        if ($backendUser instanceof \RKW\RkwRegistration\Domain\Model\BackendUser) {
            $importPropertyMapper['lang'] = 'languageCode';
        }

        // split realName
        $nameArray = [];
        if ($backendUser->getRealName()) {
            $nameArray = explode(' ', $backendUser->getRealName());

        } else if (
            (isset($additionalData['realName']))
            && ($additionalData['realName'])
        ){
            $nameArray = explode(' ', $additionalData['realName']);
        }
        unset($additionalData['realName']);


        if (count($nameArray) == 2) {
            if (isset($nameArray[0])) {
                $queueRecipient->setFirstName($nameArray[0]);
            }
            if (isset($nameArray[1])) {
                $queueRecipient->setLastName($nameArray[1]);
            }

        } else if (count($nameArray) == 3) {
            if (isset($nameArray[0])) {
                $queueRecipient->setTitle($nameArray[0]);
            }
            if (isset($nameArray[1])) {
                $queueRecipient->setFirstName($nameArray[1]);
            }
            if (isset($nameArray[2])) {
                $queueRecipient->setLastName($nameArray[2]);
            }
        } else if (count($nameArray) > 0) {
            $queueRecipient->setLastName($backendUser->getRealName());
        }


        // set all relevant values according to given data
        $this->setQueueRecipientSub($queueRecipient, $backendUser, $importPropertyMapper, $additionalData);

    }

    /**
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @param \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $user
     * @param array $importPropertyMapper
     * @param array $additionalData
     */
    private function setQueueRecipientSub (\RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient, \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $user, $importPropertyMapper, &$additionalData = array()) {

        // set all relevant values according to given data
        foreach ($importPropertyMapper as $propertySource => $propertyTarget) {
            $getter = 'get' . ucFirst($propertySource);
            $setter = 'set' . ucFirst($propertyTarget);

            if (method_exists($queueRecipient, $setter)) {

                // check for getter value
                if (
                    (method_exists($user, $getter))
                    && ($value = $user->$getter())
                    && ($value !== 99)
                ) {
                    $queueRecipient->$setter($value);

                // fallback: check for value in additional data
                } else if (
                    (isset($additionalData[$propertySource]))
                    && ($value = $additionalData[$propertySource])
                    && ($value !== 99)
                ){
                    $queueRecipient->$setter($value);
                }

                // unset additional data that has been imported
                unset($additionalData[$propertySource]);
            }
        }
    }


    /**
     * Returns the to
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @depricated since 2018/10/28 use $this->getQueueMail()->getQueueRecipients() instead
     */
    public function getTo()
    {
        return $this->getQueueMail()->getQueueRecipients();
        //===
    }


    /**
     * Init and return the queueMail
     *
     * @return \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function getQueueMail()
    {
        if (!$this->queueMail instanceof \RKW\RkwMailer\Domain\Model\QueueMail) {

            /** @var \RKW\RkwMailer\Domain\Model\QueueMail queueMail */
            $this->queueMail = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwMailer\\Domain\\Model\\QueueMail');

            // set storage pid
            $this->queueMail->setPid(intval($this->getSettings('storagePid', 'persistence')));

            // set default tstampFavSending and crDate on now
            $this->queueMail->setTstampFavSending(time());
            $this->queueMail->setCrdate(time());

            // set status to draft
            $this->queueMail->setStatus(1);

            // add and persist
            $this->queueMailRepository->add($this->queueMail);
            $this->persistenceManager->persistAll();
        }

        return $this->queueMail;
        //===
    }

    /**
     * Sets the queueMail
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return void
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function setQueueMail(\RKW\RkwMailer\Domain\Model\QueueMail $queueMail)
    {
        // check QueueMail-object
        if (!$this->queueMailValidator->validate($queueMail)) {
            throw new \RKW\RkwMailer\Service\MailServiceException('Invalid or missing data in queueMail-object.', 1540186518);
            //===
        }

        if ($queueMail->_isNew()) {
            throw new \RKW\RkwMailer\Service\MailServiceException('The queueMail-object has to be persisted before it can be used.', 1540193242);
            //===
        }

        $this->queueMail = $queueMail;

    }


    /**
     * function send
     *
     * @return boolean
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function send()
    {
        self::debugTime(__LINE__, __METHOD__);

        $queueMail = $this->getQueueMail();
        if (!$this->queueMailValidator->validate($queueMail)) {
            throw new \RKW\RkwMailer\Service\MailServiceException('Invalid or missing data in queueMail-object.', 1540186577);
            //===
        }

        // only start sending if we are in draft status
        $status = false;
        if ($queueMail->getStatus() == 1) {

            // find all final recipients of waiting mails!
            $recipientCount = $this->queueRecipientRepository->findAllByQueueMailWithStatusWaiting($queueMail, 0)->count();
            if ($recipientCount > 0) {

                // create StatisticMail dataset */
                /** @var \RKW\RkwMailer\Domain\Model\StatisticMail $statisticMail */
                $statisticMail = $this->objectManager->get('RKW\\RkwMailer\\Domain\\Model\\StatisticMail');
                $statisticMail->setTotalCount($queueMail->getQueueRecipients()->count());
                $statisticMail->setQueueMail($queueMail);
                $this->statisticMailRepository->add($statisticMail);
                $this->persistenceManager->persistAll();

                // set status to waiting so the email will be processed
                $queueMail->setStatisticMail($statisticMail);
                $queueMail->setStatus(2);
                $status = true;
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Marked queueMail with uid=%s for cronjob (%s recipients).', $queueMail->getUid(), $recipientCount));

                // update and persist changes
                $this->queueMailRepository->update($queueMail);

                // persist all until here
                $this->persistenceManager->persistAll();

                // reset object
                $this->unsetVariables();

            } else {
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('QueueMail with uid=%s has no recipients.', $queueMail->getUid()));
            }

        } else {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('QueueMail with uid=%s is not a draft (status = %s).', $queueMail->getUid(), $queueMail->getStatus()));
        }

        self::debugTime(__LINE__, __METHOD__);

        return $status;
        //===
    }


    /**
     * function send
     * this method is extensively protected via try-catch because it may be used in cronjob-context
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueMailRecipient
     * @return boolean
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function sendToRecipient(\RKW\RkwMailer\Domain\Model\QueueRecipient $queueMailRecipient)
    {
        self::debugTime(__LINE__, __METHOD__);

        $status = false;
        try {
            try {
                /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
                $queueMail = $this->getQueueMail();

                /** @var \RKW\RkwMailer\Domain\Model\StatisticMail $statisticMail */
                $statisticMail = $queueMail->getStatisticMail();

                // validate queueMail
                if (!$this->queueMailValidator->validate($queueMail)) {
                    throw new \RKW\RkwMailer\Service\MailServiceException('Invalid or missing data in queueMail-object.', 1438249330);
                    //===
                }
                if ($queueMail->_isNew()) {
                    throw new \RKW\RkwMailer\Service\MailServiceException('The queueMail-object has to be persisted before it can be used.', 1540187256);
                    //===
                }

                // validate user
                if (!$this->queueRecipientValidator->validate($queueMailRecipient)) {
                    throw new \RKW\RkwMailer\Service\MailServiceException('Invalid or missing data in queueRecipient-object.', 1438249113);
                    //===
                }
                if ($queueMailRecipient->_isNew()) {
                    throw new \RKW\RkwMailer\Service\MailServiceException('The queueMailRecipient-object has to be persisted before it can be used.', 1540187277);
                    //===
                }

                try {

                    /** @var \TYPO3\CMS\Core\Mail\MailMessage $message */
                    $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Mail\\MailMessage');

                    // render templates
                    $this->renderTemplates($queueMailRecipient);
                    if ($queueMailRecipient->getStatus() < 4) {

                        // ====================================================
                        // Set message parts based on queueRecipient
                        if (
                            $queueMailRecipient->getPlaintextBody()
                            || $queueMailRecipient->getHtmlBody()
                        ) {

                            // build e-mail
                            foreach (
                                array(
                                    'html'     => 'html',
                                    'plain'    => 'plaintext',
                                    'calendar' => 'calendar',
                                ) as $shortName => $longName
                            ) {

                                $getter = 'get' . ucFirst($longName) . 'Body';
                                if ($queueMailRecipient->$getter()) {

                                    // add calendar-entry as attachment
                                    if ($shortName == 'calendar') {
                                        // replace line breaks according to RFC 5545 3.1.
                                        $emailString = preg_replace('/\n/', "\r\n", $queueMailRecipient->$getter());
                                        $attachment = \Swift_Attachment::newInstance($emailString, 'meeting.ics', 'text/calendar');
                                        $message->attach($attachment);
                                    } else {
                                        $message->addPart($queueMailRecipient->$getter(), 'text/' . $shortName);
                                    }
                                    $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::DEBUG, sprintf('Setting %s-body for recipient with uid=%s in queueMail with uid=%s.', $longName, $queueMailRecipient->getUid(), $queueMail->getUid()));
                                }
                            }

                            // set raw body-text from queueMail
                        } else {

                            $emailBody = $queueMail->getBodyText();
                            $message->setBody($emailBody, 'text/plain');
                            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::DEBUG, sprintf('Setting default body for recipient with uid=%s in queueMail with uid=%s.', $queueMailRecipient->getUid(), $queueMail->getUid()));
                        }

                        // add attachment if set
                        if (
                            ($queueMail->getAttachment())
                            && ($queueMail->getAttachmentName())
                            && ($queueMail->getAttachmentType())
                        ) {

                            $attachment = \Swift_Attachment::newInstance($queueMail->getAttachment(), $queueMail->getAttachmentName(), $queueMail->getAttachmentType());
                            $message->attach($attachment);
                        }

                        // ====================================================
                        // Send mail
                        // set status 3 for "sending" (pro forma since no persistence here)
                        $queueMailRecipient->setStatus(3);

                        // build message based on given data
                        $recipientFullname = trim(ucfirst($queueMailRecipient->getFirstName()) . ' ' . ucfirst($queueMailRecipient->getLastName()));
                        if (!trim($recipientFullname)) {
                            $recipientFullname = $queueMailRecipient->getEmail();
                        }

                        $message->setFrom(array($queueMail->getFromAddress() => $queueMail->getFromName()))
                            ->setReplyTo($queueMail->getReplyAddress())
                            ->setTo(array($queueMailRecipient->getEmail() => $recipientFullname))
                            ->setSubject($queueMailRecipient->getSubject() ? $queueMailRecipient->getSubject() : $queueMail->getSubject())
                            ->setPriority(intval($queueMail->getPriority()))
                            ->setReturnPath($queueMail->getReturnPath());

                        // Signal slot
                        $this->getSignalSlotDispatcher()->dispatch(__CLASS__, self::SIGNAL_SEND_TO_RECIPIENT_BEFORE_SEND . ($queueMail->getCategory() ? '_' . ucFirst($queueMail->getCategory()) : ''), array(&$queueMail, &$queueMailRecipient));

                        // send message
                        if (!$message->send()) {
                            throw new \RKW\RkwMailer\Service\MailServiceException('Message could not be sent.', 1438007181);
                            //===
                        }

                        // set recipient status 4 for "sent" and remove marker
                        $queueMailRecipient->setStatus(4);
                        $status = true;
                        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Successfully sent e-mail to "%s" (recipient-uid=%s) for queueMail id=%s.', $queueMailRecipient->getEmail(), $queueMailRecipient->getUid(), $queueMail->getUid()));

                        // set counter for statistics
                        if ($statisticMail) {
                            $statisticMail->setTotalCount($queueMail->getQueueRecipients()->count());
                            $statisticMail->setContactedCount($statisticMail->getContactedCount() + 1);
                        }
                    }

                    // set status for user to error and count error in statistics
                } catch (\Exception $e) {

                    // set counter for statistics
                    if ($statisticMail) {
                        $statisticMail->setTotalCount($queueMail->getQueueRecipients()->count());
                        $statisticMail->setErrorCount($statisticMail->getErrorCount() + 1);
                    }

                    $queueMailRecipient->setStatus(99);
                    $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('An error occurred while trying to send an e-mail to "%s" (recipient-uid=%s). Message: %s', $queueMailRecipient->getEmail(), $queueMailRecipient->getUid(), str_replace(array("\n", "\r"), '', $e->getMessage())));
                    $status = false;
                }

                // User and statistics have to be updated no matter what!
                $this->queueRecipientRepository->update($queueMailRecipient);

                // update statistics
                if ($statisticMail) {
                    $this->statisticMailRepository->update($statisticMail);
                }

                // persist
                $this->persistenceManager->persistAll();

                // update queueMail with error if something was wrong
            } catch (\Exception $e) {

                $this->getQueueMail()->setStatus(99);
                $this->persistenceManager->persistAll();

                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('An error occurred while trying to send e-mail with uid=%s. Message: %s', $queueMail->getUid(), str_replace(array("\n", "\r"), '', $e->getMessage())));
                $status = false;
            }

            // Something really went wrong here!
        } catch (\Exception $e) {

            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('An error occurred while trying to send an e-mail . Message: %s', str_replace(array("\n", "\r"), '', $e->getMessage())));
            $status = false;
        }

        self::debugTime(__LINE__, __METHOD__);

        return $status;
        //===
    }

    /**
     * function storing data
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    protected function addRecipient(\RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient)
    {
        self::debugTime(__LINE__, __METHOD__);

        // check queueMail-object
        $queueMail = $this->getQueueMail();
        if ($queueMail->_isNew()) {
            throw new \RKW\RkwMailer\Service\MailServiceException('The queueMail-object has to be persisted before recipients can be added.', 1540186734);
            //===
        }

        // add recipient with status "waiting" to queueMail and remove it from object storage
        $queueRecipient->setStatus(2);
        $queueMail->addQueueRecipients($queueRecipient);

        if ($statisticMail = $queueMail->getStatisticMail()) {
            $statisticMail->setTotalCount($queueMail->getQueueRecipients()->count());
            $this->statisticMailRepository->update($statisticMail);
        }

        // update, add and persist
        $this->queueRecipientRepository->add($queueRecipient);
        $this->queueMailRepository->update($queueMail);
        $this->persistenceManager->persistAll();

        self::debugTime(__LINE__, __METHOD__);
        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Added recipient with email "%s" (uid=%s) to queueMail with uid=%s.', $queueRecipient->getEmail(), $queueRecipient->getUid(), $queueMail->getUid()));
    }


    /**
     * rendering of templates
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    protected function renderTemplates(\RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient)
    {
        self::debugTime(__LINE__, __METHOD__);

        // check queueMail-object
        $queueMail = $this->getQueueMail();
        if (!$this->queueMailValidator->validate($queueMail)) {
            throw new \RKW\RkwMailer\Service\MailServiceException('Invalid or missing data in queueMail-object.', 1540186558);
            //===
        }

        // check queueRecipient
        if (!$this->queueRecipientValidator->validate($queueRecipient)) {
            throw new \RKW\RkwMailer\Service\MailServiceException('Invalid or missing data in queueRecipient-object.', 1540186500);
            //===
        }
        if ($queueRecipient->_isNew()) {
            throw new \RKW\RkwMailer\Service\MailServiceException('The queueRecipient-object has to be persisted before it can be used.', 1540294116);
            //===
        }

        $layoutRootPaths = $this->getSettings('layoutRootPaths', 'view');
        $partialRootPaths = $this->getSettings('partialRootPaths', 'view');
        $templateRootPaths = $this->getSettings('templateRootPaths', 'view');

        // build paths to images and logos
        $baseUrlImages = $queueMail->getSettings('baseUrlImages');
        if (
            ($queueMail->getSettings('baseUrl'))
            && ($queueMail->getSettings('basePathImages'))
        ) {
            $baseUrlImages = $queueMail->getSettings('baseUrl') . '/' . $this->getRelativePath($queueMail->getSettings('basePathImages'));
        }

        $baseUrlLogo = $queueMail->getSettings('baseUrlLogo');
        if (
            ($queueMail->getSettings('baseUrl'))
            && ($queueMail->getSettings('basePathLogo'))
        ) {
            $baseUrlLogo = $queueMail->getSettings('baseUrl') . '/' . $this->getRelativePath($queueMail->getSettings('basePathLogo'));
        }

        // build HTML- or Plaintext- Template if set!
        $finalMarkerArray = array();
        foreach (
            array(
                'html'      => 'html',
                'plaintext' => 'plaintext',
                'calendar'  => 'calendar',
            ) as $property => $template
        ) {

            $templateGetter = 'get' . ucFirst($template) . 'Template';
            $propertySetter = 'set' . ucFirst($property) . 'Body';
            $propertyGetter = 'get' . ucFirst($property) . 'Body';
            if ($queueMail->$templateGetter()) {
                if (!$queueRecipient->$propertyGetter()) {

                    // build marker array - but only once!
                    if (count($finalMarkerArray) < 1) {

                        $queueRecipientMarker = $queueRecipient->getMarker();
                        $markerArray = array_merge(
                            array(
                                'queueMail'            => $queueMail,
                                'queueMailSettingsPid' => $queueMail->getSettingsPid(),
                                'queueRecipient'       => $queueRecipient,
                                'bodyText'             => $queueMail->getBodyText(),
                                'settings'             => $queueMail->getSettings(),
                            ),
                            (is_array($queueRecipientMarker) ? $queueRecipientMarker : array())
                        );
                        // rebuild lightweight marker. Replace simple references to real extbase objects
                        $finalMarkerArray = $this->enlargeMarker($markerArray);
                        $this->getSignalSlotDispatcher()->dispatch(__CLASS__, self::SIGNAL_RENDER_TEMPLATE_AFTER_MARKERS . ($queueMail->getCategory() ? '_' . ucFirst($queueMail->getCategory()) : ''), array($queueMail, &$queueRecipient, &$finalMarkerArray));
                    }

                    /** @var \TYPO3\CMS\Fluid\View\StandaloneView $emailView */
                    $emailView = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
                    $emailView->setLayoutRootPaths($layoutRootPaths);
                    $emailView->setPartialRootPaths($partialRootPaths);
                    $emailView->setTemplateRootPaths($templateRootPaths);

                    // set additional layout und partial path
                    if ($queueMail->getLayoutPaths()) {
                        $emailView->setLayoutRootPaths(array_merge($layoutRootPaths, $queueMail->getLayoutPaths()));
                    }
                    if ($queueMail->getPartialPaths()) {
                        $emailView->setPartialRootPaths(array_merge($partialRootPaths, $queueMail->getPartialPaths()));
                    }
                    if ($queueMail->getTemplatePaths()) {
                        $emailView->setTemplateRootPaths(array_merge($templateRootPaths, $queueMail->getTemplatePaths()));
                    }

                    // check for absolute paths!
                    if (strpos($queueMail->$templateGetter(), 'EXT:') === 0) {
                        $templatePathFile = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($queueMail->$templateGetter() . '.html', true, true);
                        $emailView->setTemplatePathAndFilename($templatePathFile);
                    } else {
                        $emailView->setTemplate($queueMail->$templateGetter());
                    }

                    // assign markers
                    $finalMarkerArray = array_merge(
                        $finalMarkerArray,
                        array(
                            'mailType' => ($template ? ucFirst($template) : 'Plaintext'),
                        )
                    );

                    $emailView->assignMultiple($finalMarkerArray);

                    // replace baseURLs in final email  - replacement with asign only works in template-files, not on layout-files
                    $emailString = preg_replace('/###baseUrl###/', $queueMail->getSettings('baseUrl'), $emailView->render());
                    $emailString = preg_replace('/###baseUrlImages###/', $baseUrlImages, $emailString);
                    $emailString = preg_replace('/###baseUrlLogo###/', $baseUrlLogo, $emailString);

                    // replace relative paths and absolute paths to server-root!
                    $emailString = preg_replace('/(src|href)="' . str_replace('/', '\/', GeneralUtility::getIndpEnv('TYPO3_SITE_PATH')) . '([^"]+)"/', 'src="' . $queueMail->getSettings('baseUrl') . '/$2"', $emailString);
                    $emailString = preg_replace('/(src|href)="\/([^"]+)"/', 'src="' . $queueMail->getSettings('baseUrl') . '/$2"', $emailString);

                    // add to recipient object attachment and free space of marker-array
                    $this->getSignalSlotDispatcher()->dispatch(__CLASS__, self::SIGNAL_RENDER_TEMPLATE_AFTER_RENDER . ($queueMail->getCategory() ? '_' . ucFirst($queueMail->getCategory()) : ''), array($queueMail, &$queueRecipient, &$emailView, &$emailString));
                    $queueRecipient->$propertySetter($emailString);

                    $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::DEBUG, sprintf('Added %s-template-property for recipient with email "%s" (queueMail uid=%s).', ucFirst($template), $queueRecipient->getEmail(), $queueMail->getUid()));
                } else {
                    $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::DEBUG, sprintf('%s-template-property is already set for recipient with email "%s" (queueMail uid=%s).', ucFirst($template), $queueRecipient->getEmail(), $queueMail->getUid()));
                }
            } else {
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('%s-template is not set for recipient with email "%s" (queueMail uid=%s).', ucFirst($template), $queueRecipient->getEmail(), $queueMail->getUid()));
            }
        }

        // update and persist
        $this->queueRecipientRepository->update($queueRecipient);
        $this->persistenceManager->persistAll();

        self::debugTime(__LINE__, __METHOD__);
    }

    /**
     * reduceMarker
     * transform objects into simple references
     *
     * @param array $marker
     * @return array
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     */
    protected function reduceMarker($marker)
    {
        self::debugTime(__LINE__, __METHOD__);

        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper $dataMapper */
        $dataMapper = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper::class);
        foreach ($marker as $key => $value) {

            // replace current entry with "table => uid" reference
            // keep current variable name, don't use "unset"
            // @toDo: Make array_unshift or similar? (to avoid senseless repeating cycle by foreach)
            if (is_object($value)) {

                // Normal DomainObject
                if ($value instanceof \TYPO3\CMS\Extbase\DomainObject\AbstractEntity) {

                    $namespace = filter_var($dataMapper->getDataMap(get_class($value))->getClassName(), FILTER_SANITIZE_STRING);
                    if ($value->_isNew()) {
                        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('Object with namespace %s in marker-array is not persisted and will be stored as serialized object in the database. This may cause performance issues!', $namespace));
                    } else {
                        $marker[$key] = self::NAMESPACE_KEYWORD . ' ' . $namespace . ":" . $value->getUid();
                        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::DEBUG, sprintf('Replacing object with namespace %s and uid %s in marker-array.', $namespace, $value->getUid()));
                    }

                    // ObjectStorage or QueryResult
                } else {
                    if (
                        ($value instanceof \Iterator)
                        && (
                            (
                                ($value instanceof \TYPO3\CMS\Extbase\Persistence\QueryResultInterface)
                                && ($firstObject = $value->getFirst())
                            )
                            || (
                                ($value instanceof \TYPO3\CMS\Extbase\Persistence\ObjectStorage)
                                && ($firstObject = $value->current())
                            )
                        )
                        && ($firstObject instanceof \TYPO3\CMS\Extbase\DomainObject\AbstractEntity)
                    ) {

                        $newValues = array();
                        $namespace = filter_var($dataMapper->getDataMap(get_class($firstObject))->getClassName(), FILTER_SANITIZE_STRING);
                        foreach ($value as $object) {
                            if ($object instanceof \TYPO3\CMS\Extbase\DomainObject\AbstractEntity) {
                                if ($object->_isNew()) {
                                    $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('Object with namespace %s in marker-array is not persisted and will be stored as serialized object in the database. This may cause performance issues!', $namespace));
                                } else {
                                    $newValues[] = $namespace . ":" . $object->getUid();
                                    $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::DEBUG, sprintf('Replacing object with namespace %s and uid %s in marker-array.', $namespace, $object->getUid()));
                                }
                            }
                        }
                        $marker[$key] = self::NAMESPACE_ARRAY_KEYWORD . ' ' . implode(',', $newValues);

                    } else {
                        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, ($value->getFirst() instanceof \TYPO3\CMS\Extbase\DomainObject\AbstractEntity));
                        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('Object of class %s in marker-array will be stored as serialized object in the database. This may cause performance issues!', get_class($value)));
                    }
                }
            }
        }

        self::debugTime(__LINE__, __METHOD__);

        return $marker;
        //===
    }


    /**
     * enlargeMarker
     * transform simple references to objects
     *
     * @param array $marker
     * @return array
     */
    protected function enlargeMarker($marker)
    {
        self::debugTime(__LINE__, __METHOD__);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        foreach ($marker as $key => $value) {

            // check for keyword
            if (
                (is_string($value))
                && (
                    (strpos(trim($value), self::NAMESPACE_KEYWORD) === 0)
                    || (strpos(trim($value), self::NAMESPACE_ARRAY_KEYWORD) === 0)
                )
            ) {

                // check if we have an array here
                $isArray = (bool)(strpos(trim($value), self::NAMESPACE_ARRAY_KEYWORD) === 0);
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::DEBUG, sprintf('Detection of objectStorage: %s.', intval($isArray)));

                /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
                $objectStorage = $objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage');

                // clean value from keyword
                $cleanedValue = trim(
                    str_replace(
                        array(
                            self::NAMESPACE_ARRAY_KEYWORD,
                            self::NAMESPACE_KEYWORD,
                        ),
                        '',
                        $value
                    )
                );

                // Go through list of objects. May be comma-separated in case of QueryResultInterface or ObjectStorage
                $listOfObjectDefinitions = GeneralUtility::trimExplode(',', $cleanedValue);
                foreach ($listOfObjectDefinitions as $objectDefinition) {

                    // explode namespace and uid
                    $explodedValue = GeneralUtility::trimExplode(':', $objectDefinition);
                    $namespace = trim($explodedValue[0]);
                    $uid = intval($explodedValue[1]);

                    if (class_exists($namespace)) {

                        // @toDo: Find a way to get the repository namespace instead of this replace
                        $repositoryName = str_replace('Model', 'Repository', $namespace) . 'Repository';
                        if (class_exists($repositoryName)) {

                            /** @var \TYPO3\CMS\Extbase\Persistence\Repository $repository */
                            $repository = $objectManager->get($repositoryName);

                            // build query - we fetch everything here!
                            $query = $repository->createQuery();
                            $query->getQuerySettings()->setRespectStoragePage(false);
                            $query->getQuerySettings()->setIgnoreEnableFields(true);
                            $query->getQuerySettings()->setIncludeDeleted(true);
                            $query->matching(
                                $query->equals('uid', $uid)
                            )->setLimit(1);

                            if ($result = $query->execute()->getFirst()) {
                                $objectStorage->attach($result);
                                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::DEBUG, sprintf('Replacing object with namespace %s and uid %s in marker-array.', $namespace, $result->getUid()));
                            }
                        }
                    }
                }

                if ($objectStorage->count() > 0) {
                    if ($isArray) {
                        $marker[$key] = $objectStorage;
                    } else {
                        $objectStorage->rewind();
                        $marker[$key] = $objectStorage->current();
                    }
                }
            }
        }
        self::debugTime(__LINE__, __METHOD__);

        return $marker;
        //===
    }


    /**
     * unset several variables
     *
     * @return void
     */
    protected function unsetVariables()
    {
        unset($this->queueMail);
    }


    /**
     * Returns SignalSlotDispatcher
     *
     * @return \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     */
    protected function getSignalSlotDispatcher()
    {

        if (!$this->signalSlotDispatcher) {
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
            $this->signalSlotDispatcher = $objectManager->get('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
        }

        return $this->signalSlotDispatcher;
        //===
    }

    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {

        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Log\\LogManager')->getLogger(__CLASS__);
        }

        return $this->logger;
        //===
    }


    /**
     * Returns the relative image path
     *
     * @param $string $path
     * @return string
     */
    protected function getRelativePath($path)
    {
        if (strpos($path, 'EXT:') === 0) {

            list($extKey, $local) = explode('/', substr($path, 4), 2);
            if (
                ((string)$extKey !== '')
                && (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded($extKey))
                && ((string)$local !== '')
            ) {
                $path = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($extKey) . $local;
            }
        }

        return $path;
        //===
    }


    /**
     * Gets TypoScript framework settings
     *
     * @param string $param
     * @param string $type
     * @return mixed
     */
    private function getSettings($param = '', $type = 'settings')
    {

        if (!$this->settings) {

            $this->settings = $this->configurationManager->getConfiguration(
                \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                'RkwMailer',
                'user'
            );
        }

        if ($param) {

            if ($this->settings[$type][$param . '.']) {
                return $this->settings[$type][$param . '.'];
                //===
            }

            return $this->settings[$type][$param];
            //===

        }

        return $this->settings[$type];
        //===
    }


    /**
     * Does debugging of runtime
     *
     * @param integer $line
     * @param string  $function
     */
    private static function debugTime($line, $function)
    {

        if (self::DEBUG_TIME) {

            $path = PATH_site . '/typo3temp/tx_rkwmailer_runtime.txt';
            file_put_contents($path, microtime() . ' ' . $line . ' ' . $function . "\n", FILE_APPEND);
        }
    }


}