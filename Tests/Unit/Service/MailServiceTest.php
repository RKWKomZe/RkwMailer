<?php
namespace RKW\RkwMailer\Tests\Unit\Service;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use RKW\RkwMailer\Service\MailService;
use RKW\RkwMailer\Domain\Repository\QueueMailRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

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
 * MailService
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MailServiceTest extends UnitTestCase
{

    /**
     * @var \RKW\RkwMailer\Service\MailService
     */
    private $subject;


    /**
     * Setup
     */
    protected function setUp()
    {
        parent::setUp();
        $this->subject = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MailService::class, ['unitTest' => true]);

    }

    //=============================================

    /**
     * @test
     */
    public function setQueueRecipientPropertiesSubGivenExtbaseFrontendUserWithUsernameOnlySetsEmail()
    {
        $queueRecipient = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $fixture = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $frontendUser = new \TYPO3\CMS\Extbase\Domain\Model\FrontendUser();

        $additionalData = [];
        $frontendUser->setUsername('lauterbach@spd.de');
        $fixture->setEmail('lauterbach@spd.de');

        $this->subject->setQueueRecipientPropertiesByFrontendUser($queueRecipient, $frontendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
    }

    //=============================================


    /**
     * @test
     */
    public function setQueueRecipientPropertiesByFrontendUserGivenExtbaseFrontendUserWithUsernameAndEmailDoesNotUseUsername()
    {
        $queueRecipient = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $fixture = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $frontendUser = new \TYPO3\CMS\Extbase\Domain\Model\FrontendUser();

        $additionalData = [];
        $frontendUser->setUsername('testen@test.de');
        $frontendUser->setEmail('lauterbach@spd.de');
        $fixture->setEmail('lauterbach@spd.de');

        $this->subject->setQueueRecipientPropertiesByFrontendUser($queueRecipient, $frontendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
    }

    //=========================

    /**
     * @test
     */
    public function setQueueRecipientPropertiesByFrontendUserGivenExtbaseFrontendUserWithAllValuesSetsExpectedValues()
    {
        $queueRecipient = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $fixture = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $frontendUser = new \TYPO3\CMS\Extbase\Domain\Model\FrontendUser();

        $additionalData = [];
        $frontendUser->setUsername('testen@test.de');
        $frontendUser->setEmail('lauterbach@spd.de');
        $frontendUser->setTitle('Prof.');
        $frontendUser->setFirstName('Karl');
        $frontendUser->setLastName('Lauterbach');

        $fixture->setEmail('lauterbach@spd.de');
        $fixture->setTitle('Prof.');
        $fixture->setFirstName('Karl');
        $fixture->setLastName('Lauterbach');

        $this->subject->setQueueRecipientPropertiesByFrontendUser($queueRecipient, $frontendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
    }

    /**
     * @test
     */
    public function setQueueRecipientPropertiesByFrontendUserGivenRegistrationFrontendUserWithAllValuesSetsExpectedValues()
    {
        $queueRecipient = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $fixture = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $frontendUser = new \RKW\RkwRegistration\Domain\Model\FrontendUser();
        $title = new \RKW\RkwRegistration\Domain\Model\Title();
        $additionalData = [];

        $title->setName('Dr.');

        $frontendUser->setUsername('testen@test.de');
        $frontendUser->setEmail('lauterbach@spd.de');
        $frontendUser->setTitle('Prof.');
        $frontendUser->setFirstName('Karl');
        $frontendUser->setLastName('Lauterbach');
        $frontendUser->setTxRkwregistrationTitle($title);
        $frontendUser->setTxRkwregistrationGender(0);
        $frontendUser->setTxRkwregistrationLanguageKey('fr');

        $fixture->setEmail('lauterbach@spd.de');
        $fixture->setTitle('Dr.');
        $fixture->setFirstName('Karl');
        $fixture->setLastName('Lauterbach');
        $fixture->setSalutation(0);
        $fixture->setLanguageCode('fr');

        $this->subject->setQueueRecipientPropertiesByFrontendUser($queueRecipient, $frontendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
    }


    //=========================
    /**
     * @test
     */
    public function setQueueRecipientPropertiesByFrontendUserGivenExtbaseFrontendUserWithAllValuesAndAdditionalDataSetsExpectedValues()
    {
        $queueRecipient = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $fixture = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $frontendUser = new \TYPO3\CMS\Extbase\Domain\Model\FrontendUser();

        $additionalData = [
            'username' => 'AddTesten@test.de',
            'email' => 'merkel@cdu.de',
            'title' => 'Dr.',
            'firstName' => 'Angela',
            'lastName' => 'Merkel',
            'test' => 'Merkel',
        ];
        $frontendUser->setUsername('testen@test.de');
        $frontendUser->setEmail('lauterbach@spd.de');
        $frontendUser->setTitle('Prof.');
        $frontendUser->setFirstName('Karl');
        $frontendUser->setLastName('Lauterbach');

        $fixture->setEmail('lauterbach@spd.de');
        $fixture->setTitle('Prof.');
        $fixture->setFirstName('Karl');
        $fixture->setLastName('Lauterbach');

        $this->subject->setQueueRecipientPropertiesByFrontendUser($queueRecipient, $frontendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);

    }

    /**
     * @test
     */
    public function setQueueRecipientPropertiesByFrontendUserGivenRegistrationFrontendUserWithAllValuesAndAdditionalDataSetsExpectedValues()
    {
        $queueRecipient = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $fixture = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $frontendUser = new \RKW\RkwRegistration\Domain\Model\FrontendUser();
        $title = new \RKW\RkwRegistration\Domain\Model\Title();

        $title->setName('Prof.');

        $additionalData = [
            'username' => 'angie@cdu.de',
            'email' => 'merkel@cdu.de',
            'title' => 'Dr.',
            'firstName' => 'Angela',
            'lastName' => 'Merkel',
            'test' => 'Merkel',
            'txRkwregistrationGender' => 1
        ];
        $frontendUser->setUsername('testen@test.de');
        $frontendUser->setEmail('lauterbach@spd.de');
        $frontendUser->setTitle('Master');
        $frontendUser->setFirstName('Karl');
        $frontendUser->setLastName('Lauterbach');
        $frontendUser->setTxRkwregistrationTitle($title);
        $frontendUser->setTxRkwregistrationLanguageKey('fr');

        $fixture->setEmail('lauterbach@spd.de');
        $fixture->setTitle('Prof.');
        $fixture->setFirstName('Karl');
        $fixture->setLastName('Lauterbach');
        $fixture->setSalutation(1);
        $fixture->setLanguageCode('fr');

        $this->subject->setQueueRecipientPropertiesByFrontendUser($queueRecipient, $frontendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
    }


    //=========================
    /**
     * @test
     */
    public function setQueueRecipientPropertiesByFrontendUserGivenExtbaseFrontendUserAndAdditionalDataSetsExpectedValues()
    {
        $queueRecipient = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $fixture = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $frontendUser = new \TYPO3\CMS\Extbase\Domain\Model\FrontendUser();

        $additionalData = [
            'username' => 'AddTesten@test.de',
            'email' => 'merkel@cdu.de',
            'title' => 'Dr.',
            'firstName' => 'Angela',
            'lastName' => 'Merkel',
            'test' => 'Merkel',
        ];

        $frontendUser->setFirstName('Karl');
        $frontendUser->setLastName('Lauterbach');

        $fixture->setEmail('merkel@cdu.de');
        $fixture->setTitle('Dr.');
        $fixture->setFirstName('Karl');
        $fixture->setLastName('Lauterbach');

        $this->subject->setQueueRecipientPropertiesByFrontendUser($queueRecipient, $frontendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);

    }


    /**
     * @test
     */
    public function setQueueRecipientPropertiesByFrontendUserGivenRegistrationFrontendUserAndAdditionalDataSetsExpectedValues()
    {
        $queueRecipient = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $fixture = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $frontendUser = new \RKW\RkwRegistration\Domain\Model\FrontendUser();

        $additionalData = [
            'username' => 'AddTesten@test.de',
            'email' => 'merkel@cdu.de',
            'title' => 'Prof.',
            'firstName' => 'Angela',
            'lastName' => 'Merkel',
            'test' => 'Merkel',
            'txRkwregistrationGender' => 1,
            'txRkwregistrationLanguageKey' => 'fr'
        ];

        $frontendUser->setFirstName('Karl');
        $frontendUser->setLastName('Lauterbach');

        $fixture->setEmail('merkel@cdu.de');
        $fixture->setTitle('Prof.');
        $fixture->setFirstName('Karl');
        $fixture->setLastName('Lauterbach');
        $fixture->setSalutation(1);
        $fixture->setLanguageCode('fr');

        $this->subject->setQueueRecipientPropertiesByFrontendUser($queueRecipient, $frontendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);

    }

    //################################################################################


    /**
     * @test
     */
    public function setQueueRecipientPropertiesByBackendUserGivenExtbaseBackendUserWithEmailOnlySetsEmail()
    {
        $queueRecipient = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $fixture = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $backendUser = new \TYPO3\CMS\Extbase\Domain\Model\BackendUser();

        $additionalData = [];
        $backendUser->setEmail('lauterbach@spd.de');
        $fixture->setEmail('lauterbach@spd.de');

        $this->subject->setQueueRecipientPropertiesByBackendUser($queueRecipient, $backendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
    }


    //=============================================


    /**
     * @test
     */
    public function setQueueRecipientPropertiesByBackendUserGivenExtbaseBackendUserWithEmailAndOneWordRealNameOnlySetsEmailAndLastName()
    {
        $queueRecipient = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $fixture = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $backendUser = new \TYPO3\CMS\Extbase\Domain\Model\BackendUser();

        $additionalData = [];
        $backendUser->setEmail('lauterbach@spd.de');
        $backendUser->setRealName('Karl');

        $fixture->setEmail('lauterbach@spd.de');
        $fixture->setLastName('Karl');

        $this->subject->setQueueRecipientPropertiesByBackendUser($queueRecipient, $backendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
    }


    /**
     * @test
     */
    public function setQueueRecipientPropertiesByBackendUserGivenExtbaseBackendUserWithEmailAndTwoWordRealNameOnlySetsEmailAndFirstNameAndLastName()
    {
        $queueRecipient = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $fixture = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $backendUser = new \TYPO3\CMS\Extbase\Domain\Model\BackendUser();

        $additionalData = [];
        $backendUser->setEmail('lauterbach@spd.de');
        $backendUser->setRealName('Karl Lauterbach');

        $fixture->setEmail('lauterbach@spd.de');
        $fixture->setFirstName('Karl');
        $fixture->setLastName('Lauterbach');

        $this->subject->setQueueRecipientPropertiesByBackendUser($queueRecipient, $backendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
    }

    /**
     * @test
     */
    public function setQueueRecipientPropertiesByBackendUserGivenExtbaseBackendUserWithEmailAndThreeWordRealNameOnlySetsEmailAndFirstNameAndLastNameAndTitle()
    {
        $queueRecipient = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $fixture = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $backendUser = new \TYPO3\CMS\Extbase\Domain\Model\BackendUser();

        $additionalData = [];
        $backendUser->setEmail('lauterbach@spd.de');
        $backendUser->setRealName('Prof. Karl Lauterbach');

        $fixture->setEmail('lauterbach@spd.de');
        $fixture->setFirstName('Karl');
        $fixture->setLastName('Lauterbach');
        $fixture->setTitle('Prof.');

        $this->subject->setQueueRecipientPropertiesByBackendUser($queueRecipient, $backendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
    }

    //=========================

    /**
     * @test
     */
    public function setQueueRecipientPropertiesByBackendUserGivenExtbaseBackendUserWithAllDataAndAdditionalDataSetsExpectedValues()
    {
        $queueRecipient = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $fixture = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $backendUser = new \TYPO3\CMS\Extbase\Domain\Model\BackendUser();

        $additionalData = [
            'realName' => 'Dr. Angela Merkel',
            'email'    => 'merkel@cdu.de',
            'test'     => 'Merkel'
        ];
        $backendUser->setEmail('lauterbach@spd.de');
        $backendUser->setRealName('Prof. Karl Lauterbach');

        $fixture->setEmail('lauterbach@spd.de');
        $fixture->setTitle('Prof.');
        $fixture->setFirstName('Karl');
        $fixture->setLastName('Lauterbach');

        $this->subject->setQueueRecipientPropertiesByBackendUser($queueRecipient, $backendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
    }


    /**
     * @test
     */
    public function setQueueRecipientPropertiesByBackendUserGivenRegistrationBackendUserWithAllDataAndAdditionalDataSetsExpectedValues()
    {
        $queueRecipient = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $fixture = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $backendUser = new \RKW\RkwRegistration\Domain\Model\BackendUser();

        $additionalData = [
            'realName'      => 'Dr. Angela Merkel',
            'email'         => 'merkel@cdu.de',
            'languageCode'  => 'ru',
            'test'          => 'Merkel'
        ];
        $backendUser->setEmail('lauterbach@spd.de');
        $backendUser->setRealName('Prof. Karl Lauterbach');
        $backendUser->setLang('fr');

        $fixture->setEmail('lauterbach@spd.de');
        $fixture->setTitle('Prof.');
        $fixture->setFirstName('Karl');
        $fixture->setLastName('Lauterbach');
        $fixture->setLanguageCode('fr');

        $this->subject->setQueueRecipientPropertiesByBackendUser($queueRecipient, $backendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
    }

    //=========================


    /**
     * @test
     */
    public function setQueueRecipientPropertiesByBackendUserGivenExtbaseBackendUserAndAdditionalDataSetsExpectedValues()
    {
        $queueRecipient = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $fixture = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $backendUser = new \TYPO3\CMS\Extbase\Domain\Model\BackendUser();

        $additionalData = [
            'realName' => 'Prof. Karl Lauterbach',
            'test'     => 'Merkel'
        ];
        $backendUser->setEmail('lauterbach@spd.de');

        $fixture->setEmail('lauterbach@spd.de');
        $fixture->setTitle('Prof.');
        $fixture->setFirstName('Karl');
        $fixture->setLastName('Lauterbach');

        $this->subject->setQueueRecipientPropertiesByBackendUser($queueRecipient, $backendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
    }

    /**
     * @test
     */
    public function setQueueRecipientPropertiesByBackendUserGivenRegistrationBackendUserAndAdditionalDataSetsExpectedValues()
    {
        $queueRecipient = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $fixture = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $backendUser = new \RKW\RkwRegistration\Domain\Model\BackendUser();

        $additionalData = [
            'realName'      => 'Dr. Angela Merkel',
            'email'         => 'merkel@cdu.de',
            'languageCode'  => 'ru',
            'test'          => 'Merkel'
        ];

        $backendUser->setEmail('lauterbach@spd.de');

        $fixture->setEmail('lauterbach@spd.de');
        $fixture->setTitle('Dr.');
        $fixture->setFirstName('Angela');
        $fixture->setLastName('Merkel');
        $fixture->setLanguageCode('en');

        $this->subject->setQueueRecipientPropertiesByBackendUser($queueRecipient, $backendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
    }

    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }








}