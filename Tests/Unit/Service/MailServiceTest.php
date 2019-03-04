<?php
namespace RKW\RkwMailer\Tests\Unit\Service;

use Nimut\TestingFramework\TestCase\UnitTestCase;
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
    protected $subject;


    /**
     * @var \RKW\RkwMailer\Domain\Model\QueueRecipient
     */
    protected $queueRecipient;


    /**
     * Setup
     */
    protected function setUp()
    {
        parent::setUp();
        $this->subject = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwMailer\\Service\\MailService');
    }

    /**
     * @test
     */
    public function setQueueRecipientByFrontendUserGivenExtbaseFrontendUserWithUsernameOnlySetsEmail()
    {
        $queueRecipient = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $fixture = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $frontendUser = new \TYPO3\CMS\Extbase\Domain\Model\FrontendUser();

        $additionalData = [];
        $frontendUser->setUsername('lauterbach@spd.de');
        $fixture->setEmail('lauterbach@spd.de');

        $this->subject->setQueueRecipientByFrontendUser($queueRecipient, $frontendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
    }



    //=============================================


    /**
     * @test
     */
    public function setQueueRecipientByFrontendUserGivenExtbaseFrontendUserWithUsernameAndEmailDoesNotUseUsername()
    {
        $queueRecipient = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $fixture = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $frontendUser = new \TYPO3\CMS\Extbase\Domain\Model\FrontendUser();

        $additionalData = [];
        $frontendUser->setUsername('testen@test.de');
        $frontendUser->setEmail('lauterbach@spd.de');
        $fixture->setEmail('lauterbach@spd.de');

        $this->subject->setQueueRecipientByFrontendUser($queueRecipient, $frontendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
    }

    //=========================

    /**
     * @test
     */
    public function setQueueRecipientByFrontendUserGivenExtbaseFrontendUserWithAllValuesSetsExpectedValues()
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

        $this->subject->setQueueRecipientByFrontendUser($queueRecipient, $frontendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
    }

    /**
     * @test
     */
    public function setQueueRecipientByFrontendUserGivenRegistrationFrontendUserWithAllValuesSetsExpectedValues()
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
        $frontendUser->setTxRkwregistrationGender(1);
        $frontendUser->setTxRkwregistrationLanguageKey('fr');

        $fixture->setEmail('lauterbach@spd.de');
        $fixture->setTitle('Dr.');
        $fixture->setFirstName('Karl');
        $fixture->setLastName('Lauterbach');
        $fixture->setSalutation(1);
        $fixture->setLanguageCode('fr');

        $this->subject->setQueueRecipientByFrontendUser($queueRecipient, $frontendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
    }


    //=========================
    /**
     * @test
     */
    public function setQueueRecipientByFrontendUserGivenExtbaseFrontendUserWithAllValuesAndAdditionalDataSetsExpectedValuesAndClearsAdditionalDataArray()
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

        $fixtureAdditionalData = [
            'test' => 'Merkel'
        ];

        $this->subject->setQueueRecipientByFrontendUser($queueRecipient, $frontendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
        static::assertEquals($fixtureAdditionalData, $additionalData);

    }

    /**
     * @test
     */
    public function setQueueRecipientByFrontendUserGivenRegistrationFrontendUserWithAllValuesAndAdditionalDataSetsExpectedValuesAndClearsAdditionalDataArray()
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
        ];
        $frontendUser->setUsername('testen@test.de');
        $frontendUser->setEmail('lauterbach@spd.de');
        $frontendUser->setTitle('Master');
        $frontendUser->setFirstName('Karl');
        $frontendUser->setLastName('Lauterbach');
        $frontendUser->setTxRkwregistrationTitle($title);
        $frontendUser->setTxRkwregistrationGender(1);
        $frontendUser->setTxRkwregistrationLanguageKey('fr');


        $fixture->setEmail('lauterbach@spd.de');
        $fixture->setTitle('Prof.');
        $fixture->setFirstName('Karl');
        $fixture->setLastName('Lauterbach');
        $fixture->setSalutation(1);
        $fixture->setLanguageCode('fr');


        $fixtureAdditionalData = [
            'test' => 'Merkel'
        ];

        $this->subject->setQueueRecipientByFrontendUser($queueRecipient, $frontendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
        static::assertEquals($fixtureAdditionalData, $additionalData);
    }


    //=========================
    /**
     * @test
     */
    public function setQueueRecipientByFrontendUserGivenExtbaseFrontendUserAndAdditionalDataSetsExpectedValuesAndClearsAdditionalDataArray()
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

        $fixtureAdditionalData = [
            'test' => 'Merkel'
        ];

        $this->subject->setQueueRecipientByFrontendUser($queueRecipient, $frontendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
        static::assertEquals($fixtureAdditionalData, $additionalData);

    }


    /**
     * @test
     */
    public function setQueueRecipientByFrontendUserGivenRegistrationFrontendUserAndAdditionalDataSetsExpectedValuesAndClearsAdditionalDataArray()
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

        $fixtureAdditionalData = [
            'test' => 'Merkel'
        ];

        $this->subject->setQueueRecipientByFrontendUser($queueRecipient, $frontendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
        static::assertEquals($fixtureAdditionalData, $additionalData);

    }

    //################################################################################


    /**
     * @test
     */
    public function setQueueRecipientByBackendUserGivenExtbaseBackendUserWithEmailOnlySetsEmail()
    {
        $queueRecipient = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $fixture = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $backendUser = new \TYPO3\CMS\Extbase\Domain\Model\BackendUser();

        $additionalData = [];
        $backendUser->setEmail('lauterbach@spd.de');
        $fixture->setEmail('lauterbach@spd.de');

        $this->subject->setQueueRecipientByBackendUser($queueRecipient, $backendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
    }


    //=============================================


    /**
     * @test
     */
    public function setQueueRecipientByBackendUserGivenExtbaseBackendUserWithEmailAndOneWordRealNameOnlySetsEmailAndLastName()
    {
        $queueRecipient = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $fixture = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $backendUser = new \TYPO3\CMS\Extbase\Domain\Model\BackendUser();

        $additionalData = [];
        $backendUser->setEmail('lauterbach@spd.de');
        $backendUser->setRealName('Karl');

        $fixture->setEmail('lauterbach@spd.de');
        $fixture->setLastName('Karl');

        $this->subject->setQueueRecipientByBackendUser($queueRecipient, $backendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
    }


    /**
     * @test
     */
    public function setQueueRecipientByBackendUserGivenExtbaseBackendUserWithEmailAndTwoWordRealNameOnlySetsEmailAndFirstNameAndLastName()
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

        $this->subject->setQueueRecipientByBackendUser($queueRecipient, $backendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
    }

    /**
     * @test
     */
    public function setQueueRecipientByBackendUserGivenExtbaseBackendUserWithEmailAndThreeWordRealNameOnlySetsEmailAndFirstNameAndLastNameAndTitle()
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

        $this->subject->setQueueRecipientByBackendUser($queueRecipient, $backendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
    }

    //=========================

    /**
     * @test
     */
    public function setQueueRecipientByBackendUserGivenExtbaseBackendUserWithAllDataAndAdditionalDataSetsExpectedValuesAndClearsAdditionalDataArray()
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

        $fixtureAdditionalData = [
            'test' => 'Merkel'
        ];

        $this->subject->setQueueRecipientByBackendUser($queueRecipient, $backendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
        static::assertEquals($fixtureAdditionalData, $additionalData);
    }


    /**
     * @test
     */
    public function setQueueRecipientByBackendUserGivenRegistrationBackendUserWithAllDataAndAdditionalDataSetsExpectedValuesAndClearsAdditionalDataArray()
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

        $fixtureAdditionalData = [
            'test' => 'Merkel'
        ];

        $this->subject->setQueueRecipientByBackendUser($queueRecipient, $backendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
        static::assertEquals($fixtureAdditionalData, $additionalData);
    }

    //=========================


    /**
     * @test
     */
    public function setQueueRecipientByBackendUserGivenExtbaseBackendUserAndAdditionalDataSetsExpectedValuesAndClearsAdditionalDataArray()
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

        $fixtureAdditionalData = [
            'test' => 'Merkel'
        ];

        $this->subject->setQueueRecipientByBackendUser($queueRecipient, $backendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
        static::assertEquals($fixtureAdditionalData, $additionalData);
    }

    /**
     * @test
     */
    public function setQueueRecipientByBackendUserGivenRegistrationBackendUserAndAdditionalDataSetsExpectedValuesAndClearsAdditionalDataArray()
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

        $fixtureAdditionalData = [
            'test' => 'Merkel'
        ];

        $this->subject->setQueueRecipientByBackendUser($queueRecipient, $backendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
        static::assertEquals($fixtureAdditionalData, $additionalData);
    }

    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }








}