<?php

namespace RKW\RkwMailer\Command;
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

use RKW\RkwMailer\Domain\Repository\BounceMailRepository;
use RKW\RkwMailer\Domain\Repository\QueueRecipientRepository;
use RKW\RkwMailer\Statistics\BounceMailAnalyser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/***
 * class ProcessBounceMailsCommand
 *
 * Execute on CLI with: 'vendor/bin/typo3 rkw_mailer:processBounceMails <username> <password> <host>'
 * @todo rework
 */
class ProcessBounceMailsCommand extends Command
{

    /**
     * @var \RKW\RkwMailer\Domain\Repository\QueueRecipientRepository
     */
    protected $queueRecipientRepository;


    /**
     * @var \RKW\RkwMailer\Domain\Repository\BounceMailRepository
     */
    protected $bounceMailRepository;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    protected $persistenceManager;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;


    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure(): void
    {
        $this->setDescription('Process bounced emails.')
            ->addOption(
                'maxEmails',
                'm',
                InputOption::VALUE_REQUIRED,
                'Maximum number of emails to be processed (Default: 100)',
                100
            );
    }


    /**
     * Initializes the command after the input has been bound and before the input
     * is validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @see InputInterface::bind()
     * @see InputInterface::validate()
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {

        /** @var  TYPO3\CMS\Extbase\Object\ObjectManager$objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->bounceMailRepository = $objectManager->get(BounceMailRepository::class);
        $this->queueRecipientRepository = $objectManager->get(QueueRecipientRepository::class);
        $this->persistenceManager = $objectManager->get(PersistenceManager::class);
    }


    /**
     * Executes the command for showing sys_log entries
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @see InputInterface::bind()
     * @see InputInterface::validate()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());
        $io->newLine();

        $maxEmails = $input->getOption('maxEmails');

        $result = 0;
        try {
            if (
                ($bouncedRecipients = $this->queueRecipientRepository->findAllLastBounced($maxEmails))
                && (count($bouncedRecipients))
            ){

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

                    $message = sprintf(
                        'Setting bounced status for queueRecipient id=%, email=%s.',
                        $queueRecipient->getUid(),
                        $queueRecipient->getEmail()
                    );
                    $io->note($message);
                    $this->getLogger()->log(LogLevel::INFO, $message);
                }

                $io->note('test' . count($bouncedRecipients ));


                $this->persistenceManager->persistAll();

            } else {
                $message = 'No bounced mails processed.';
                $io->note($message);
                $this->getLogger()->log(LogLevel::DEBUG, $message);
            }


        } catch (\Exception $e) {

            $message = sprintf('An unexpected error occurred while trying to update the statistics of e-mails: %s',
                str_replace(array("\n", "\r"), '', $e->getMessage())
            );
            $io->error($message);
            $this->getLogger()->log(LogLevel::ERROR, $message);
        }

        $io->writeln('Done');
        return $result;

    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger(): \TYPO3\CMS\Core\Log\Logger
    {
        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }
}
