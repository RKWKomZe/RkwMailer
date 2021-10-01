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

use RKW\RkwMailer\Cache\MailBodyCache;
use RKW\RkwMailer\Persistence\MarkerReducer;
use RKW\RkwMailer\Utility\QueueRecipientUtility;
use RKW\RkwMailer\View\MailStandaloneView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwMailer\Domain\Repository\QueueMailRepository;
use RKW\RkwMailer\Domain\Repository\QueueRecipientRepository;
use RKW\RkwMailer\Domain\Repository\BounceMailRepository;
use RKW\RkwMailer\Validation\QueueMailValidator;
use RKW\RkwMailer\Validation\QueueRecipientValidator;
use RKW\RkwMailer\Validation\EmailValidator;

/**
 * MailService
 *
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
     * BounceMailRepository
     *
     * @var \RKW\RkwMailer\Domain\Repository\BounceMailRepository
     * @inject
     */
    protected $bounceMailRepository;


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
     * MailBodyCache
     *
     * @var \RKW\RkwMailer\Cache\MailBodyCache
     * @inject
     */
    protected $mailBodyCache;
    
    /**
     * MarkerReducer
     *
     * @var \RKW\RkwMailer\Persistence\MarkerReducer
     * @inject
     */
    protected $markerReducer;
    
    /**
     * Logger
     *
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;
    
    /**
     * MailStandaloneView
     *
     * @var \RKW\RkwMailer\View\MailStandaloneView
     */
    protected $view;

    
    /**
     * The normal settings
     *
     * @var array
     */
    protected $settings = array();


    /**
     * Constructor
     * @param bool $unitTest
     */
    public function __construct($unitTest = false)
    {
        self::debugTime(__LINE__, __METHOD__);
        if (! $unitTest) {
            $this->initializeService();
        }

        self::debugTime(__LINE__, __METHOD__);
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
            $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);
        }
        if (!$this->configurationManager) {
            $this->configurationManager = $this->objectManager->get(ConfigurationManagerInterface::class);
        }
        if (!$this->persistenceManager) {
            $this->persistenceManager = $this->objectManager->get(PersistenceManager::class);
        }
        if (!$this->queueMailRepository) {
            $this->queueMailRepository = $this->objectManager->get(QueueMailRepository::class);
        }
        if (!$this->queueRecipientRepository) {
            $this->queueRecipientRepository = $this->objectManager->get(QueueRecipientRepository::class);
        }
        if (!$this->bounceMailRepository) {
            $this->bounceMailRepository = $this->objectManager->get(BounceMailRepository::class);
        }
        if (!$this->queueMailValidator) {
            $this->queueMailValidator = $this->objectManager->get(QueueMailValidator::class);
        }
        if (!$this->queueRecipientValidator) {
            $this->queueRecipientValidator = $this->objectManager->get(QueueRecipientValidator::class);
        }
        if (!$this->view) {
            $this->view = $this->objectManager->get(MailStandaloneView::class);
        }
        if (!$this->markerReducer) {
            $this->markerReducer = $this->objectManager->get(MarkerReducer::class);
        }
        if (!$this->mailBodyCache) {
            $this->mailBodyCache= $this->objectManager->get(MailBodyCache::class);
        }
        \TYPO3\CMS\Core\Utility\GeneralUtility::deprecationLog(__CLASS__ . ': Please use the ObjectManager to load this class.');
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

            // set storage pidt
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
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceQueueMailException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function setQueueMail(\RKW\RkwMailer\Domain\Model\QueueMail $queueMail)
    {
        // check QueueMail-object
        if (!$this->queueMailValidator->validate($queueMail)) {
            throw new \RKW\RkwMailer\Service\Exception\MailServiceQueueMailException('Invalid or missing data in queueMail-object.', 1540186518);
            //===
        }

        if ($queueMail->_isNew()) {
            throw new \RKW\RkwMailer\Service\Exception\MailServiceQueueMailException('The queueMail-object has to be persisted before it can be used.', 1540193242);
            //===
        }

        $this->queueMail = $queueMail;

    }



    /**
     * Sets the recipients
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser|\TYPO3\CMS\Extbase\Domain\Model\BackendUser|array $basicData
     * @param array $additionalData
     * @param bool  $renderTemplates
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @return boolean
     */
    public function setTo($basicData, $additionalData = array(), $renderTemplates = false)
    {

        self::debugTime(__LINE__, __METHOD__);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = QueueRecipientUtility::initQueueRecipient($basicData, $additionalData);

        // set marker
        if (isset($additionalData['marker'])) {
            $queueRecipient->setMarker($this->markerReducer->implodeMarker($additionalData['marker']));
        }

        // Signal slot
        $this->getSignalSlotDispatcher()->dispatch(__CLASS__, self::SIGNAL_TO_BEFORE_ATTACH . ($this->getQueueMail()->getCategory() ? '_' . ucFirst($this->getQueueMail()->getCategory()) : ''), array($this->getQueueMail(), &$queueRecipient));

        if ($this->addQueueRecipient($queueRecipient)) {

            // render templates right away?
            if ($renderTemplates) {
                $this->renderTemplates($queueRecipient);
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
     * Returns the recipients
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @depricated since 2018/10/28 use $this->queueRecipientRepository->findByQueueMail($queueMail) instead
     */
    public function getTo()
    {
        \TYPO3\CMS\Core\Utility\GeneralUtility::deprecationLog(__CLASS__ . ': GetTo() method will be removed soon. Use $this->queueRecipientRepository->findByQueueMail($queueMail) instead.');
        return $this->queueRecipientRepository->findByQueueMail($this->getQueueMail());
        //===
    }


    /**
     * function storing data
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @return bool
     */
    public function addQueueRecipient(\RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient)
    {

        self::debugTime(__LINE__, __METHOD__);
        if (
            ($this->queueRecipientValidator->validate($queueRecipient))
            && (! $this->hasQueueRecipient($queueRecipient))
        ){

            // get queueMail-object
            $queueMail = $this->getQueueMail();

            // add recipient with status "waiting" to queueMail and remove it from object storage
            $queueRecipient->setStatus(2);

            // set storage pid
            $queueRecipient->setPid(intval($this->getSettings('storagePid', 'persistence')));

            // set queueMail
            $queueRecipient->setQueueMail($queueMail);

            // update, add and persist
            $this->queueRecipientRepository->add($queueRecipient);
            $this->persistenceManager->persistAll();

            self::debugTime(__LINE__, __METHOD__);
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Added recipient with email "%s" (uid=%s) to queueMail with uid=%s.', $queueRecipient->getEmail(), $queueRecipient->getUid(), $queueMail->getUid()));

            return true;
        }

        return false;
    }


    /**
     * check if queue recipient already exists for queueMail
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient|string $email
     * @throws \Exception
     * @return bool
     */
    public function hasQueueRecipient($email)
    {
        if ($email instanceof \RKW\RkwMailer\Domain\Model\QueueRecipient){
            $email = $email->getEmail();
        }

        self::debugTime(__LINE__, __METHOD__);
        if ($this->queueRecipientRepository->findOneByEmailAndQueueMail($email, $this->getQueueMail())) {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Recipient with email "%s" already exists for queueMail with uid=%s.', $email, $this->getQueueMail()->getUid()));
            return true;
        }

        return false;
    }



    /**
     * rendering of templates
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @return void
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceQueueRecipientException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function renderTemplates(\RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient): void
    {

        self::debugTime(__LINE__, __METHOD__);

        // check if queueRecipient is persisted
        if ($queueRecipient->_isNew()) {
            throw new \RKW\RkwMailer\Service\Exception\MailServiceQueueRecipientException(
                'The queueRecipient-object has to be persisted before it can be used.', 
                1540294116
            );
        }

        // build HTML- or Plaintext- Template if set!
        foreach (['html', 'plaintext', 'calendar'] as $type) {

            $templateGetter = 'get' . ucFirst($type) . 'Template';
            $propertySetter = 'set' . ucFirst($type) . 'Body';
            $propertyGetter = 'get' . ucFirst($type) . 'Body';
            
            if ($this->getQueueMail()->$templateGetter()) {

                // check if templates have already been rendered and stored in cache
                if (! $this->mailBodyCache->$propertyGetter($queueRecipient)) {

                    // load EmailStandaloneView with configuration of queueMail
                    /** @var \RKW\RkwMailer\View\MailStandaloneView $emailView */
                    $emailView = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                        MailStandaloneView::class,
                        $this->getQueueMail()->getSettingsPid()
                    );

                    $emailView->setQueueMail($this->getQueueMail());
                    $emailView->setQueueRecipient($queueRecipient);
                    $emailView->setTemplateType($type);
                    $emailView->assignMultiple($queueRecipient->getMarker());
                    $renderedTemplate = $emailView->render();
                    
                    // cache rendered templates
                    $this->mailBodyCache->$propertySetter($queueRecipient, $renderedTemplate);

                    $this->getLogger()->log(
                        \TYPO3\CMS\Core\Log\LogLevel::DEBUG, 
                        sprintf(
                            'Added %s-template-property for recipient with email "%s" (queueMail uid=%s).', 
                            ucFirst($type), 
                            $queueRecipient->getEmail(),
                            $this->getQueueMail()->getUid()
                        )
                    );
                } else {
                    $this->getLogger()->log(
                        \TYPO3\CMS\Core\Log\LogLevel::DEBUG, 
                        sprintf(
                            '%s-template-property is already set for recipient with email "%s" (queueMail uid=%s).', 
                            ucFirst($type), 
                            $queueRecipient->getEmail(),
                            $this->getQueueMail()->getUid()
                        )
                    );
                }
            } else {
                $this->getLogger()->log(
                    \TYPO3\CMS\Core\Log\LogLevel::INFO, 
                    sprintf(
                        '%s-template is not set for recipient with email "%s" (queueMail uid=%s).', 
                        ucFirst($type), 
                        $queueRecipient->getEmail(),
                        $this->getQueueMail()->getUid()
                    )
                );
            }
        }

        self::debugTime(__LINE__, __METHOD__);
    }



    /**
     * function send
     *
     * @return boolean
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceQueueMailException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function send()
    {
        self::debugTime(__LINE__, __METHOD__);

        $queueMail = $this->getQueueMail();
        if (!$this->queueMailValidator->validate($queueMail)) {
            throw new \RKW\RkwMailer\Service\Exception\MailServiceQueueMailException('Invalid or missing data in queueMail-object.', 1540186577);
            //===
        }

        // only start sending if we are in draft status
        if ($queueMail->getStatus() == 1) {

            // find all final recipients of waiting mails!
            $recipientCount = $this->queueRecipientRepository->findAllByQueueMailWithStatusWaiting($queueMail, 0)->count();
            if ($recipientCount > 0) {

                // set status to waiting so the email will be processed
                $queueMail->setStatus(2);

                // update and persist changes
                $this->queueMailRepository->update($queueMail);

                // persist all until here
                $this->persistenceManager->persistAll();

                // reset object
                $this->unsetVariables();

                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Marked queueMail with uid=%s for cronjob (%s recipients).', $queueMail->getUid(), $recipientCount));

                return true;
                //====

            } else {
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('QueueMail with uid=%s has no recipients.', $queueMail->getUid()));
            }

        } else {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('QueueMail with uid=%s is not a draft (status = %s).', $queueMail->getUid(), $queueMail->getStatus()));
        }

        self::debugTime(__LINE__, __METHOD__);

        return false;
        //===
    }


    /**
     * function sendToRecipient
     * this method is extensively protected via try-catch because it may be used in cronjob-context
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @return boolean
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceQueueMailException
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceQueueRecipientException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function sendToRecipient(\RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient)
    {
        self::debugTime(__LINE__, __METHOD__);
        $status = false;

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->getQueueMail();

        // validate queueMail
        if (!$this->queueMailValidator->validate($queueMail)) {
            throw new \RKW\RkwMailer\Service\Exception\MailServiceQueueMailException('Invalid or missing data in queueMail-object.', 1438249330);
            //===
        }

        // validate queueRecipient
        if (!$this->queueRecipientValidator->validate($queueRecipient)) {
            throw new \RKW\RkwMailer\Service\Exception\MailServiceQueueRecipientException('Invalid or missing data in queueRecipient-object.', 1438249113);
            //===
        }
        if ($queueRecipient->_isNew()) {
            throw new \RKW\RkwMailer\Service\Exception\MailServiceQueueRecipientException('The queueMailRecipient-object has to be persisted before it can be used.', 1540187277);
            //===
        }


        // check if email of recipient has bounced recently - but only for pipeline mailings
        if (
            ($this->bounceMailRepository->countByEmailAndType($queueRecipient->getEmail()) < 3)
            || (! $this->queueMail->getPipeline())
        ){

            // render templates
            $this->renderTemplates($queueRecipient);

            // try to send message
            try {

                /** @var  \TYPO3\CMS\Core\Mail\MailMessage $message */
                $message = $this->prepareEmailForRecipient($queueRecipient);
                $this->getSignalSlotDispatcher()->dispatch(__CLASS__, self::SIGNAL_SEND_TO_RECIPIENT_BEFORE_SEND . ($queueMail->getCategory() ? '_' . ucFirst($queueMail->getCategory()) : ''), array(&$queueMail, &$queueRecipient));

                // add mailing list header if it is a pipeline
                if ($this->queueMail->getPipeline()) {
                    $message->getHeaders()->addTextHeader('List-Unsubscribe', $queueMail->getFromAddress());
                }

                $message->send();
                $status = true;

                // set recipient status 4 for "sent" and remove marker
                $queueRecipient->setStatus(4);

                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Successfully sent e-mail to "%s" (recipient-uid=%s) for queueMail id=%s.', $queueRecipient->getEmail(), $queueRecipient->getUid(), $queueMail->getUid()));


            } catch (\Exception $e) {

                $status = false;
                $errorMessage = str_replace(array("\n", "\r"), '', $e->getMessage());

                // set recipient status to error
                $queueRecipient->setStatus(99);

                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('An error occurred while trying to send an e-mail to "%s" (recipient-uid=%s). Message: %s', $queueRecipient->getEmail(), $queueRecipient->getUid(), $errorMessage));
            }

        } else {

            // set status to deferred - we don't sent an email to this address again
            $queueRecipient->setStatus(97);
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('E-mail "%s" (recipient-uid=%s) blocked for further mailings because of bounces detected during processing of queueMail width uid=%s.', $queueRecipient->getEmail(), $queueRecipient->getUid(), $queueMail->getUid()));

        }

        // User has to be updated no matter what!
        $this->queueRecipientRepository->update($queueRecipient);

        // persist
        $this->persistenceManager->persistAll();

        self::debugTime(__LINE__, __METHOD__);

        return $status;
        //===
    }

    /**
     * prepareEmailForRecipient
     *
     * prepares email object for given recipient user
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @return null |\TYPO3\CMS\Core\Mail\MailMessage
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceQueueMailException
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceQueueRecipientException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function prepareEmailForRecipient ($queueRecipient) {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->getQueueMail();

        // validate queueMail
        if (!$this->queueMailValidator->validate($queueMail)) {
            throw new \RKW\RkwMailer\Service\Exception\MailServiceQueueMailException('Invalid or missing data in queueMail-object.', 1438249330);
            //===
        }
        // validate queueRecipient
        if (!$this->queueRecipientValidator->validate($queueRecipient)) {
            throw new \RKW\RkwMailer\Service\Exception\MailServiceQueueRecipientException('Invalid or missing data in queueRecipient-object.', 1552485792);
            //===
        }

        // render templates
        $this->renderTemplates($queueRecipient);

        if ($queueRecipient->getStatus() < 4) {

            /** @var \TYPO3\CMS\Core\Mail\MailMessage $message */
            $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Mail\\MailMessage');

            // Set message parts based on cache
            if (
                $this->mailBodyCache->getPlaintextBody($queueRecipient)
                || $this->mailBodyCache->getHtmlBody($queueRecipient)
            ) {

                // build e-mail
                foreach (
                    array(
                        'html'     => 'html',
                        'plain'    => 'plaintext',
                    ) as $shortName => $longName
                ) {

                    $getter = 'get' . ucFirst($longName) . 'Body';
                    if ($template = $this->mailBodyCache->$getter($queueRecipient)) {

                        $message->addPart($template, 'text/' . $shortName);
                        $this->getLogger()->log(
                            \TYPO3\CMS\Core\Log\LogLevel::DEBUG, 
                            sprintf(
                                'Setting %s-body for recipient with uid=%s in queueMail with uid=%s.', 
                                $longName, 
                                $queueRecipient->getUid(), 
                                $queueMail->getUid()
                            )
                        );
                    }
                }

            // set raw body-text from queueMail
            } else {

                $emailBody = $queueMail->getBodyText();
                $message->setBody($emailBody, 'text/plain');
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::DEBUG, sprintf('Setting default body for recipient with uid=%s in queueMail with uid=%s.', $queueRecipient->getUid(), $queueMail->getUid()));
            }

            // set calendar attachment
            if ($template = $this->mailBodyCache->getCalendarBody($queueRecipient)) {

                // replace line breaks according to RFC 5545 3.1.
                $emailString = preg_replace('/\n/', "\r\n", $template);
                $attachment = \Swift_Attachment::newInstance($emailString, 'meeting.ics', 'text/calendar');
                $message->attach($attachment);
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::DEBUG, sprintf('Setting calendar-body for recipient with uid=%s in queueMail with uid=%s.', $queueRecipient->getUid(), $queueMail->getUid()));
            }

            // add attachment if set
            if (
                $queueMail->getAttachment()
                || is_array(json_decode($queueMail->getAttachment(), true))
            ) {

                $attachments = json_decode($queueMail->getAttachment(), true);

                foreach ($attachments as $attachment) {
                    $file = \Swift_Attachment::fromPath($attachment['path']);
                    $message->attach($file);
                }

            }

            // ====================================================
            // Send mail
            // set status 3 for "sending" (pro forma since no persistence here)
            $queueRecipient->setStatus(3);

            // build message based on given data
            $recipientAddress = EmailValidator::cleanUpEmail($queueRecipient->getEmail());
            $recipientName = trim(ucfirst($queueRecipient->getFirstName()) . ' ' . ucfirst($queueRecipient->getLastName()));
            if (trim($recipientName)) {
                $recipientAddress = [EmailValidator::cleanUpEmail($queueRecipient->getEmail()) => $recipientName];
            }

            $message->setFrom(array($queueMail->getFromAddress() => $queueMail->getFromName()))
                ->setReplyTo(EmailValidator::cleanUpEmail($queueMail->getReplyAddress()))
                ->setTo($recipientAddress)
                ->setSubject($queueRecipient->getSubject() ? $queueRecipient->getSubject() : $queueMail->getSubject())
                ->setPriority(intval($queueMail->getPriority()))
                ->setReturnPath($queueMail->getReturnPath());


            return $message;
            //===
        }

        return null;
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