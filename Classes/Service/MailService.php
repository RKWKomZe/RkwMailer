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

use RKW\RkwMailer\Mail\Mailer;
use RKW\RkwMailer\Persistence\MarkerReducer;
use RKW\RkwMailer\Utility\QueueMailUtility;
use RKW\RkwMailer\Utility\QueueRecipientUtility;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use RKW\RkwMailer\Domain\Repository\QueueMailRepository;
use RKW\RkwMailer\Domain\Repository\QueueRecipientRepository;
use RKW\RkwMailer\Validation\QueueMailValidator;
use RKW\RkwMailer\Validation\QueueRecipientValidator;

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
     * Mailer
     *
     * @var \RKW\RkwMailer\Mail\Mailer
     * @inject
     */
    protected $mailer;    
    
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
            $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
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
        if (!$this->queueMailValidator) {
            $this->queueMailValidator = $this->objectManager->get(QueueMailValidator::class);
        }
        if (!$this->queueRecipientValidator) {
            $this->queueRecipientValidator = $this->objectManager->get(QueueRecipientValidator::class);
        }
        if (!$this->mailer) {
            $this->mailer = $this->objectManager->get(Mailer::class);
        }        
        GeneralUtility::deprecationLog(__CLASS__ . ': Please use the ObjectManager to load this class.');
    }

    
    /**
     * Init and return the queueMail
     *
     * @return \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @api
     */
    public function getQueueMail()
    {
        if (!$this->queueMail instanceof \RKW\RkwMailer\Domain\Model\QueueMail) {

            // init object
            $storagePid = intval($this->getSettings('storagePid', 'persistence'));
            
            /** @var \RKW\RkwMailer\Domain\Model\QueueMail queueMail */
            $this->queueMail = QueueMailUtility::initQueueMail($storagePid);

            // add and persist
            $this->queueMailRepository->add($this->queueMail);
            $this->persistenceManager->persistAll();
        }

        return $this->queueMail;
    }

    
    /**
     * Sets the queueMail
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return void
     * @throws \RKW\RkwMailer\Exception
     * @api
     */
    public function setQueueMail(\RKW\RkwMailer\Domain\Model\QueueMail $queueMail)
    {

        if ($queueMail->_isNew()) {
            throw new \RKW\RkwMailer\Exception (
                'The queueMail-object has to be persisted before it can be used.', 
                1540193242
            );
        }

        $this->queueMail = $queueMail;
    }



    /**
     * Sets the recipients
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser|\TYPO3\CMS\Extbase\Domain\Model\BackendUser|array $basicData
     * @param array $additionalData
     * @param bool $renderTemplates
     * @return boolean
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \RKW\RkwMailer\Exception
     * @api
     */
    public function setTo(
        $basicData, 
        array $additionalData = [],
        bool $renderTemplates = false
    ): bool {

        self::debugTime(__LINE__, __METHOD__);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = QueueRecipientUtility::initQueueRecipient($basicData, $additionalData);

        if ($this->addQueueRecipient($queueRecipient)) {

            // render templates right away?
            if ($renderTemplates) {
                $this->mailer->renderTemplates($this->getQueueMail(), $queueRecipient);
            }

            self::debugTime(__LINE__, __METHOD__);
            return true;
        }

        self::debugTime(__LINE__, __METHOD__);
        return false;
    }


    /**
     * Returns the recipients
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function getTo()
    {
        return $this->queueRecipientRepository->findByQueueMail($this->getQueueMail());
    }


    /**
     * add queueRecipient to queueMail
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @throws \Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @return bool
     * @api
     */
    public function addQueueRecipient(
        \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
    ): bool {
        self::debugTime(__LINE__, __METHOD__);
        if (
            ($this->queueRecipientValidator->validate($queueRecipient))
            && (! $this->hasQueueRecipient($queueRecipient))
        ){

            // add recipient with status "waiting" to queueMail and remove it from object storage
            $queueRecipient->setStatus(2);

            // set storage pid
            $queueRecipient->setPid(intval($this->getSettings('storagePid', 'persistence')));

            // set queueMail
            $queueRecipient->setQueueMail($this->getQueueMail());

            // update, add and persist
            $this->queueRecipientRepository->add($queueRecipient);
            $this->persistenceManager->persistAll();

            $this->getLogger()->log(
                \TYPO3\CMS\Core\Log\LogLevel::INFO, 
                sprintf(
                    'Added recipient with email "%s" (uid %s) to queueMail with uid %s.', 
                    $queueRecipient->getEmail(), 
                    $queueRecipient->getUid(),
                    $this->getQueueMail()->getUid()
                )
            );
            
            self::debugTime(__LINE__, __METHOD__);
            return true;
        }
        
        self::debugTime(__LINE__, __METHOD__);
        return false;
    }


    /**
     * check if queue recipient already exists for queueMail
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient|string $email
     * @throws \Exception
     * @return bool
     * @api
     */
    public function hasQueueRecipient($email): bool
    {
        self::debugTime(__LINE__, __METHOD__);

        if ($email instanceof \RKW\RkwMailer\Domain\Model\QueueRecipient){
            $email = $email->getEmail();
        }
       
        if ($this->queueRecipientRepository->findOneByEmailAndQueueMail($email, $this->getQueueMail())) {
            $this->getLogger()->log(
                \TYPO3\CMS\Core\Log\LogLevel::INFO, 
                sprintf(
                    'Recipient with email "%s" already exists for queueMail with uid %s.', 
                    $email, 
                    $this->getQueueMail()->getUid()
                )
            );  
            
            self::debugTime(__LINE__, __METHOD__);
            return true;
        }
        
        self::debugTime(__LINE__, __METHOD__);
        return false;
    }
    

    /**
     * function send
     *
     * @return boolean
     * @throws \Exception
     * @throws \RKW\RkwMailer\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @api
     */
    public function send()
    {
        self::debugTime(__LINE__, __METHOD__);

        $queueMail = $this->getQueueMail();
        if (!$this->queueMailValidator->validate($queueMail)) {
            throw new \RKW\RkwMailer\Exception(
                'Invalid or missing data in queueMail-object.', 
                1540186577
            );
        }

        // only start sending if we are in draft status
        if ($queueMail->getStatus() == 1) {

            // find all final recipients of waiting mails!
            $recipientCount = $this->queueRecipientRepository->findAllByQueueMailWithStatusWaiting(
                $queueMail, 
                0
            )->count();
            
            if ($recipientCount > 0) {

                // set status to waiting so the email will be processed
                $queueMail->setStatus(2);

                // update and persist changes
                $this->queueMailRepository->update($queueMail);
                $this->persistenceManager->persistAll();

                // reset object
                $this->unsetVariables();

                $this->getLogger()->log(
                    \TYPO3\CMS\Core\Log\LogLevel::INFO, 
                    sprintf(
                        'Marked queueMail with uid %s for cronjob (%s recipients).', 
                        $queueMail->getUid(), 
                        $recipientCount
                    )
                );
                
                self::debugTime(__LINE__, __METHOD__);
                return true;

            } else {
                $this->getLogger()->log(
                    \TYPO3\CMS\Core\Log\LogLevel::INFO, 
                    sprintf(
                        'QueueMail with uid %s has no recipients.', 
                        $queueMail->getUid()
                    )
                );
            }

        } else {
            $this->getLogger()->log(
                \TYPO3\CMS\Core\Log\LogLevel::INFO, 
                sprintf(
                    'QueueMail with uid %s is not a draft (status %s).', 
                    $queueMail->getUid(), 
                    $queueMail->getStatus()
                )
            );
        }

        self::debugTime(__LINE__, __METHOD__);
        return false;
    }


    /**
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @return \TYPO3\CMS\Core\Mail\MailMessage
     * @throws \RKW\RkwMailer\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @deprecated 
     */
    public function prepareEmailForRecipient(
        \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
    ): \TYPO3\CMS\Core\Mail\MailMessage {
        return $this->mailer->prepareEmailBody($this->getQueueMail(), $queueRecipient);
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
     * Gets TypoScript framework settings
     *
     * @param string $param
     * @param string $type
     * @return mixed
     */
    protected function getSettings(string $param = '', string $type = 'settings')
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
            }

            return $this->settings[$type][$param];
        }

        return $this->settings[$type];
    }
    

    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger(): \TYPO3\CMS\Core\Log\Logger
    {
        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }
        return $this->logger;
    }


    /**
     * Does debugging of runtime
     *
     * @param integer $line
     * @param string  $function
     */
    protected static function debugTime(int $line, string $function): void
    {
        if (GeneralUtility::getApplicationContext()->isDevelopment()) {
            $path = PATH_site . '/typo3temp/var/logs/tx_rkwmailer_runtime.txt';
            file_put_contents($path, microtime() . ' ' . $line . ' ' . $function . "\n", FILE_APPEND);
        }
    }


}