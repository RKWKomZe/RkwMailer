<?php
namespace RKW\RkwMailer\Example;

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

use Madj2k\CoreExtended\Utility\GeneralUtility;
use RKW\RkwMailer\Service\MailService;
use RKW\RkwMailer\Utility\FrontendLocalizationUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\OptIn;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * RkwMailService
 * This is an example file for using the mailer-API as a mail-service
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RkwMailService implements \TYPO3\CMS\Core\SingletonInterface
{


    /**
     * Handles create user event
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwRegistration\Domain\Model\OptIn $optIn
     * @return void
     * @throws \Exception
     * @throws \RKW\RkwMailer\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function sendOptInEmail(FrontendUser $frontendUser, OptIn $optIn): void
    {

        /** Load configuration an template path */
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $settingsDefault = $this->getSettings();

        if ($settings['view']['templateRootPaths']) {

            /** @var \RKW\RkwMailer\Service\MailService $mailService */
            $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MailService::class);

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
                    'tokenYes'        => $optIn->getTokenYes(),
                    'tokenNo'         => $optIn->getTokenNo(),
                    'tokenUser'       => $optIn->getTokenUser(),
                    'frontendUser'    => $frontendUser,
                    'settings'        => $settingsDefault,
                    'pageUid'         => intval($GLOBALS['TSFE']->id),
                ),
            ));

            /**
             * Set the globally used subject
             * Here we use a user-specific translation based on the languageKey of the user.
             */
            $mailService->getQueueMail()->setSubject(
                FrontendLocalizationUtility::translate(
                    'rkwMailService.optIn.subject',
                    'rkw_registration',
                    null,
                    $frontendUser->getTxRkwregistrationLanguageKey()
                )
            );

            /**
             * Set the templates. The templates are to be placed in the extension that uses the service.
             */
            $mailService->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
            $mailService->getQueueMail()->setPlaintextTemplate('Email/Example/OptIn');
            $mailService->getQueueMail()->setHtmlTemplate('Email/Example/OptIn');

            /**
             * send the email.
             * If you have set more than one recipient, the mail will be queued and send via cronjob
             */
            $mailService->send();
        }
    }


    /**
     * Handles optIn-event for group-admins
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwRegistration\Domain\Model\OptIn $optIn
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRegistration\Domain\Model\BackendUser> $approvals
     * @return void
     * @throws \RKW\RkwMailer\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function sendGroupOptInEmailAdmin(FrontendUser $frontendUser, OptIn $optIn, ObjectStorage $approvals): void
    {

        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $settingsDefault = $this->getSettings();

        if ($settings['view']['templateRootPaths']) {

            /** @var \RKW\RkwMailer\Service\MailService $mailService */
            $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MailService::class);

            /** @var \RKW\RkwRegistration\Domain\Model\BackendUser $backendUser */
            foreach ($approvals as $backendUser) {

                // send new user an email with token
                $mailService->setTo($backendUser, array(
                    'marker' => array(
                        'tokenYes' => $optIn->getAdminTokenYes(),
                        'tokenNo' => $optIn->getAdminTokenNo(),
                        'tokenUser' => $optIn->getTokenUser(),
                        'frontendUser' => $frontendUser,
                        'backendUser' => $backendUser,
                        'frontendUserGroup' => $optIn->getData(),
                        'settings' => $settingsDefault,
                        'pageUid' => intval($GLOBALS['TSFE']->id),
                    ),

                    /**
                     * Set the specific subject based on the language of the backendUser
                     */
                    'subject' => FrontendLocalizationUtility::translate(
                        'rkwMailService.group.optInAdmin.subject',
                        'rkw_registration',
                        null,
                        $backendUser->getLang()
                    ),
                ));
            }

            /**
             * Set the globally used subject
             * Here we use a user-specific translation based on the languageKey of the user.
             */
            $mailService->getQueueMail()->setSubject(
                FrontendLocalizationUtility::translate(
                    'rkwMailService.group.optInAdmin.subject',
                    'rkw_registration',
                )
            );

            $mailService->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
            $mailService->getQueueMail()->setPlaintextTemplate('Email/Example/OptInAdmin');
            $mailService->getQueueMail()->setHtmlTemplate('Email/Example/OptInAdmin');
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
    protected function getSettings(string $which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS): array
    {
        return GeneralUtility::getTypoScriptConfiguration('Rkwregistration', $which);
    }
}
