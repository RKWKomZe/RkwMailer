<?php

namespace RKW\RkwMailer\Example;

use \RKW\RkwBasics\Helper\Common;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

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
 * RkwMailService
 * This is an example file for using the mailer-API as a mail-service
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RkwMailService implements \TYPO3\CMS\Core\SingletonInterface
{


    /**
     * Handles create user event
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwRegistration\Domain\Model\Registration $registration
     * @param mixed $signalInformation
     * @return void
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function handleExampleEvent(\RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser, \RKW\RkwRegistration\Domain\Model\Registration $registration, $signalInformation)
    {

        /** Load configuration an template path */
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        if ($settings['view']['templateRootPaths']) {

            /** @var \RKW\RkwMailer\Service\MailService $mailService */
            $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwMailer\\Service\\MailService');

            /**
             * Here we set the recipients of the email.
             * Expected params:
             * 1. Parameter: FE-User-object OR array with the following keys:
             *      email --> email-address - HAS TO BE SET!
             *      firstName --> first name - optional
             *      lastName --> last name - optional
             * 2. Parameter: Additional params that are added to the object of the mail-recipient
             *
             * @see: RKW\RkwMailer\Domain\Model\QueueRecipient
             *      Example:
             *      subject (string) --> Overrides globally set subject of the email. Allows to personalize the subject of the email
             *      marker (array) --> Every key-value-pair given into the marker array will be given into the fluid template
             *      The languageKey is needed to translate the email into the language of the user
             * @see Resources/Private/Templates/Email/Example.html
             *      You can also use the variables queueMail and queueRecipient in fluid. These reference to the following objects:
             * @see: RKW\RkwMailer\Domain\Model\QueueMail
             * @see: RKW\RkwMailer\Domain\Model\QueueRecipient
             * You can call setTo multiple times in order to send the same email to different users.
             * The variables will be set for every recipient accordingly.
             */
            $mailService->setTo($frontendUser, array(
                'marker' => array(
                    'frontendUser' => $frontendUser,
                    'pageUid'      => intval($GLOBALS['TSFE']->id),
                ),
            ));

            /**
             * Set the globally used subject
             * Here we use a user-specific translation based on the languageKey of the user.
             */
            $mailService->getQueueMail()->setSubject(
                \RKW\RkwMailer\Utility\FrontendLocalizationUtility::translate(
                    'rkwMailService.exampleEvent.subject',
                    'rkw_registration',
                    null,
                    $frontendUser->getTxRkwregistrationLanguageKey()
                )
            );

            /**
             * Set the templates. The templates are to be placed in the extension that uses the service.
             */
            $mailService->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
            $mailService->getQueueMail()->setPlaintextTemplate('Email/RegisterOptInRequest');
            $mailService->getQueueMail()->setHtmlTemplate('Email/RegisterOptInRequest');

            /**
             * send the email.
             * If you have set more than one recipient, the mail will be queued and send via cronjob
             */
            $mailService->send();
        }

    }


    /**
     * Handles register user event
     *
     * @param \RKW\RkwRegistration\Domain\Model\BackendUser $admin
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $frontendUserGroup
     * @param \RKW\RkwRegistration\Domain\Model\Service $serviceOptIn
     * @param integer $pid
     * @param mixed $signalInformation
     * @return void
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function handleAdminServiceEvent(\RKW\RkwRegistration\Domain\Model\BackendUser $admin, \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser, \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $frontendUserGroup, \RKW\RkwRegistration\Domain\Model\Service $serviceOptIn, $pid, $signalInformation)
    {

        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        if ($settings['view']['templateRootPaths']) {

            /** @var \RKW\RkwMailer\Service\MailService $mailService */
            $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwMailer\\Service\\MailService');

            // send new user an email with token
            $mailService->setTo($admin, array(
                'marker' => array(
                    'tokenYes'          => $serviceOptIn->getTokenYes(),
                    'tokenNo'           => $serviceOptIn->getTokenNo(),
                    'serviceSha1'       => $serviceOptIn->getServiceSha1(),
                    'service'           => $serviceOptIn,
                    'frontendUser'      => $frontendUser,
                    'frontendUserGroup' => $frontendUserGroup,
                    'backendUser'       => $admin,
                    'pageUid'           => intval($pid),
                ),
            ));

            $mailService->getQueueMail()->setSubject(
                \RKW\RkwMailer\Utility\FrontendLocalizationUtility::translate(
                    'rkwMailService.adminServiceEvent.subject',
                    'rkw_registration',
                    null,
                    $admin->getLang()
                )
            );

            $mailService->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
            $mailService->getQueueMail()->setPlaintextTemplate('Email/ServiceOptInAdminRequest');
            $mailService->getQueueMail()->setHtmlTemplate('Email/ServiceOptInAdminRequest');
            $mailService->send();
        }
    }

    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getSettings($which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS)
    {
        return Common::getTyposcriptConfiguration('Rkwregistration', $which);
        //===
    }
}
