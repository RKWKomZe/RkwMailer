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

/***
 * class AnalyseBounceMailsCommand
 *
 * Execute on CLI with: 'vendor/bin/typo3 rkw_mailer:analyseBounceMails <username> <password> <host>'
 * @todo rework
 */
class AnalyseBounceMailsCommand extends Command
{

    /**
     * @var \RKW\RkwMailer\Statistics\BounceMailAnalyser
     */
    protected $bounceMailAnalyser;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;


    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure(): void
    {
        $this->setDescription('Cleanup for sent queueMails.')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'The username for the bounce-mail account',
            )
            ->addArgument(
                'password',
                InputArgument::REQUIRED,
                'The password for the bounce-mail account',
            )
            ->addArgument(
                'host',
                InputArgument::REQUIRED,
                'The host of the bounce-mail account',
            )
            ->addOption(
                'port',
                'p',
                InputOption::VALUE_REQUIRED,
                'The port for the bounce-mail account (default: 143 - IMAP)',
                '143'
            )
            ->addOption(
                'usePop3',
                'r',
                InputOption::VALUE_REQUIRED,
                'Use POP3-Protocol instead of IMAP (default: 0)',
                '0'
            )
            ->addOption(
                'tlsMode',
                't',
                InputOption::VALUE_REQUIRED,
                'The connection mode for the bounce-mail account (none, tls, notls, ssl, etc.; default: notls)',
                'notls'
            )
            ->addOption(
                'inboxName',
                'i',
                InputOption::VALUE_REQUIRED,
                'The name of the inbox (default: INBOX)',
                'INBOX'
            )
            ->addOption(
                'deleteBefore',
                'd',
                InputOption::VALUE_REQUIRED,
                'If set, all mails before the given date will be deleted (format: yyyy-mm-dd)',
                ''
            )
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
        $params = [
            'username' => $input->getArgument('username'),
            'password' => $input->getArgument('password'),
            'host' => $input->getArgument('host'),
            'port' => intval($input->getOption('port')),
            'usePop3' => boolval($input->getOption('usePop3')),
            'tlsMode' => $input->getOption('tlsMode'),
            'inboxName' => $input->getOption('inboxName'),
            'deleteBefore' => $input->getOption('deleteBefore'),
        ];

        /** @var  TYPO3\CMS\Extbase\Object\ObjectManager$objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->bounceMailAnalyser = $objectManager->get(BounceMailAnalyser::class, $params);
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

            $this->bounceMailAnalyser->analyseMails($maxEmails);

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
