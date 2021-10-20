<?php

namespace RKW\RkwMailer\Controller;

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
use RKW\RkwMailer\Statistics\BounceMailAnalyser;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * MailerCommandController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MailerCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController
{

    /**
     * objectManager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     * @inject
     */
    protected $objectManager;


    /**
     * persistenceManager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager;

    /**
     * queueMailRepository
     *
     * @var \RKW\RkwMailer\Domain\Repository\QueueMailRepository
     * @inject
     */
    protected $queueMailRepository;

    /**
     * queueRecipientRepository
     *
     * @var \RKW\RkwMailer\Domain\Repository\QueueRecipientRepository
     * @inject
     */
    protected $queueRecipientRepository;

    /**
     * bounceMailRepository
     *
     * @var \RKW\RkwMailer\Domain\Repository\BounceMailRepository
     * @inject
     */
    protected $bounceMailRepository;

    
    /**
     * mailingStatisticsAnalyser
     *
     * @var \RKW\RkwMailer\Statistics\MailingStatisticsAnalyser
     * @inject
     */
    protected $mailingStatisticsAnalyser;


    /**
     * mailer
     *
     * @var \RKW\RkwMailer\Mail\Mailer
     * @inject
     */
    protected $mailer;


    /**
     * cleaner
     *
     * @var \RKW\RkwMailer\Persistence\Cleaner
     * @inject
     */
    protected $cleaner;

    
    /**
     * configurationManager
     */
    protected $configurationManager;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;

    
    /**
     * The settings.
     *
     * @var array
     */
    protected $settings = array();



    /**
     * Processes queued mails and sends them
     *
     * @param integer $emailsPerJob How many queueMails are to be processed during one cronjob
     * @param integer $emailsPerInterval How may emails are to be send in each queueMail
     * @param integer $settingsPid Pid to fetch TypoScript-settings from
     * @param float $sleep how many seconds the script should sleep after each e-mail sent
     * @return void
     */
    public function sendEmailsCommand(
        int $emailsPerJob = 5, 
        int $emailsPerInterval = 10, 
        int $settingsPid = 0, 
        float $sleep = 0.0
    ): void {
        try {

            $this->mailer->processQueueMails($emailsPerJob, $emailsPerInterval, $settingsPid, $sleep);

        } catch (\Exception $e) {
            $this->getLogger()->log(
                LogLevel::ERROR, 
                sprintf('An unexpected error occurred while trying to send e-mails: %s', 
                    str_replace(array("\n", "\r"), '', $e->getMessage())
                )
            );
        }
    }



    /**
     * Processes queued mails and analyses their statistics
     *
     * @param int $daysAfterSendingStarted Defines how many days after sending has been started the statistics should be updated (default: 30 days)
     * @return void
     */
    public function analyseStatisticsCommand(
        int $daysAfterSendingStarted = 30
    ): void {
        try {
            
            $this->mailingStatisticsAnalyser->analyse($daysAfterSendingStarted);
            
        } catch (\Exception $e) {
            $this->getLogger()->log(
                LogLevel::ERROR,
                sprintf('An unexpected error occurred while trying to update the statistics of e-mails: %s',
                    str_replace(array("\n", "\r"), '', $e->getMessage())
                )
            );
        }
    }


    /**
     * Clean up for mails and statistics
     *
     * @param int $daysAfterSendingFinished  Defines how many days after its sending has been finished an queueMail and their corresponding data will be deleted (default: 30 days)
     * @param string $types Defines which types of mails the cleanup should look for (comma-separated) (Default: only type "0")
     * @param int $includingStatistics Defines whether the statistics should be deleted too (Default: 0)
     * @return void
     */
    public function cleanupCommand(
        int $daysAfterSendingFinished = 30, 
        string $types = '0',
        int $includingStatistics = 0
    ): void {
        
        try {

            $this->cleaner->cleanup(
                $daysAfterSendingFinished,
                GeneralUtility::trimExplode(',', $types, true),
                boolval($includingStatistics)
            );

        } catch (\Exception $e) {
            $this->getLogger()->log(
                LogLevel::ERROR,
                sprintf('An unexpected error occurred while trying to cleanup the database: %s',
                    str_replace(array("\n", "\r"), '', $e->getMessage())
                )
            );
        }
    }


    /**
     * Analyse bounced mails
     *
     * @param string $username The username for the bounce-mail account
     * @param string $password The password for the bounce-mail account
     * @param string $host The host of the bounce-mail account
     * @param bool $usePop3 Use POP3-Protocol instead of IMAP (default: false)
     * @param int $port The port for the bounce-mail account (default: 143 - IMAP)
     * @param string $tlsMode The connection mode for the bounce-mail account (none, tls, notls, ssl, etc.; default: notls)
     * @param string $inboxName The name of the inbox (default: INBOX)
     * @param string $deleteBefore If set, all mails before the given date will be deleted (format: yyyy-mm-dd)
     * @param int $maxEmails
     * @return void
     * @toDo: rework
     */
    public function analyseBouncedMailsCommand($username, $password, $host, $usePop3 = false, $port = 143, $tlsMode = 'notls', $inboxName = 'INBOX', $deleteBefore = '', $maxEmails = 100)
    {
        
        try {

            $params = [
                'username' => $username,
                'password' => $password,
                'host' => $host,
                'usePop3' => boolval($usePop3),
                'port' => intval($port),
                'tlsMode' => $tlsMode,
                'inboxName' => $inboxName,
                'deleteBefore' => $deleteBefore,
            ];

            /** @var \RKW\RkwMailer\Statistics\BounceMailAnalyser $bounceMailAnalyser */
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);
            $bounceMailAnalyser = $objectManager->get(BounceMailAnalyser::class, $params);
            $bounceMailAnalyser->analyseMails($maxEmails);

        } catch (\Exception $e) {
            $this->getLogger()->log(LogLevel::ERROR, sprintf('An unexpected error occurred while trying to analyse bounced e-mails: %s.', str_replace(array("\n", "\r"), '', $e->getMessage())));
        }
    }


    /**
     * Process bounced mails
     *
     * @param int $maxEmails
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @toDo: rework
     */
    public function processBouncedMailsCommand($maxMails = 100)
    {
        try {
            if ($bouncedRecipients = $this->queueRecipientRepository->findAllLastBounced($maxMails)) {

                /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
                foreach ($bouncedRecipients as $queueRecipient) {

                    // set status to bounced
                    $queueRecipient->setStatus(98);
                    $this->queueRecipientRepository->update($queueRecipient);

                    // set status of bounceMail to processed for all bounces of the same email-address
                    $bounceMails = $this->bounceMailRepository->findByEmail($queueRecipient->getEmail());

                    /** @var \RKW\RkwMailer\Domain\Model\BounceMail $bounceMail */
                    foreach ($bounceMails as $bounceMail) {
                        $bounceMail->setStatus(1);
                        $this->bounceMailRepository->update($bounceMail);
                    }

                    $this->getLogger()->log(
                        LogLevel::INFO, 
                        sprintf(
                            'Setting bounced status for queueRecipient id=%, email=%s.', 
                            $queueRecipient->getUid(), 
                            $queueRecipient->getEmail()
                        )
                    );
                }

            } else {
                $this->getLogger()->log(
                    LogLevel::DEBUG, 
                    'No bounced mails processed.'
                );
            }

        } catch (\Exception $e) {
            $this->getLogger()->log(
                LogLevel::ERROR, 
                sprintf(
                    'An unexpected error occurred while trying to process bounced e-mails: %s.', 
                    str_replace(array("\n", "\r"), '', $e->getMessage())
                )
            );
        }
    }
    
    
    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {

        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }


}