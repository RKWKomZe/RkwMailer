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

use RKW\RkwMailer\Mail\Mailer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * class SendCommand
 *
 * Execute on CLI with: 'vendor/bin/typo3 rkw_mailer:send'
 */
class SendCommand extends Command
{

    /**
     * @var \RKW\RkwMailer\Mail\Mailer
     */
    protected \RKW\RkwMailer\Mail\Mailer $mailer;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected \TYPO3\CMS\Core\Log\Logger $logger;


    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure(): void
    {
        $this->setDescription('Sends all queueMails.')
            ->addOption(
                'emailsPerJob',
                'j',
                InputOption::VALUE_REQUIRED,
                'How many queueMails are to be processed during one cronjob',
                5
            )
            ->addOption(
                'emailsPerInterval',
                'i',
                InputOption::VALUE_REQUIRED,
                'How may emails are to be sent for each queueMail',
                10
            )
            ->addOption(
                'settingsPid',
                'p',
                InputOption::VALUE_REQUIRED,
                'Pid to fetch TypoScript-settings from',
                0
            )
            ->addOption(
                'sleep',
                's',
                InputOption::VALUE_REQUIRED,
                'How many seconds the script should sleep after each e-mail sent',
                0.0
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
        $this->mailer = $objectManager->get(Mailer::class);
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

        $emailsPerJob = $input->getOption('emailsPerJob');
        $emailsPerInterval = $input->getOption('emailsPerInterval');
        $settingsPid = $input->getOption('settingsPid');
        $sleep = $input->getOption('sleep');

        $result = 0;
        try {

            $queueMails = $this->mailer->processQueueMails($emailsPerJob, $emailsPerInterval, $settingsPid, $sleep);
            $io->note('Processed ' . count($queueMails) . ' queueMails.');

            /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
            foreach ($queueMails as $queueMail) {
                $io->note("\t" . 'uid: ' . $queueMail->getUid());
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
