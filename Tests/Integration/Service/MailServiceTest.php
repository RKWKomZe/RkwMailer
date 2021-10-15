<?php
namespace RKW\RkwMailer\Tests\Integration\Service;

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

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwMailer\Cache\MailBodyCache;
use RKW\RkwMailer\Domain\Model\MailingStatistics;
use RKW\RkwMailer\Domain\Model\QueueMail;
use RKW\RkwMailer\Domain\Model\QueueRecipient;
use RKW\RkwMailer\Domain\Repository\MailingStatisticsRepository;
use RKW\RkwMailer\Domain\Repository\QueueMailRepository;
use RKW\RkwMailer\Domain\Repository\QueueRecipientRepository;
use RKW\RkwMailer\Service\MailService;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * MailServiceTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MailServiceTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/MailServiceTest/Fixtures';

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_mailer'
    ];

    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [ ];


    /**
     * @var \RKW\RkwMailer\Service\MailService
     */
    private $subject;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager;

    
    /**
     * @var \RKW\RkwMailer\Domain\Repository\QueueMailRepository
     */
    private $queueMailRepository;

    
    /**
     * @var \RKW\RkwMailer\Domain\Repository\QueueRecipientRepository
     */
    private $queueRecipientRepository;

    
    /**
     * @var \RKW\RkwMailer\Domain\Repository\MailingStatisticsRepository
     */
    private $mailingStatisticsRepository;    

    
    /**
     * @var \RKW\RkwMailer\Cache\MailBodyCache
     */
    private $mailBodyCache;

    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp()
    {

        parent::setUp();

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailerConfiguration/TypoScript/setup.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $this->objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->queueMailRepository = $this->objectManager->get(QueueMailRepository::class);
        $this->queueRecipientRepository = $this->objectManager->get(QueueRecipientRepository::class);
        $this->mailingStatisticsRepository = $this->objectManager->get(MailingStatisticsRepository::class);
        $this->mailBodyCache = $this->objectManager->get(MailBodyCache::class);
        $this->subject = $this->objectManager->get(MailService::class);
    }


    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getQueueMailReturnsQueueMailObjectWithDefaultValues ()
    {
        /**
         * Scenario:
         *
         * Given all TYPO3_CONF_VARS for the mail-configuration are set
         * Given a page is loaded in frontend-context 
         * When the method is called
         * Then a queueMail-object is returned
         * Then this object has the storagePid-property set to the value in the configuration
         * Then this object has the settingsPid-property set to the uid of the loaded page
         * Then this object has the status-property set to the value one (=draft)
         * Then this object has the default properties for mailings set according to the TYPO3_CONF_VARS
         * Then this object has the mailingStatistic-property set with an instance of MailingStatistics
         * Then this instance of MailingStatistics has the tstampFavSending-property set
         */

        FrontendSimulatorUtility::simulateFrontendEnvironment(1);

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'RKW';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'service@mein.rkw.de';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailReplyAddress'] = 'reply@mein.rkw.de';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailReturnAddress'] = 'bounces@mein.rkw.de';
        
        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->subject->getQueueMail();
        static::assertInstanceOf(QueueMail::class, $queueMail);
        
        static::assertEquals(9999, $queueMail->getPid());
        static::assertEquals($queueMail->getStatus(), 1);
        static::assertEquals(1, $queueMail->getSettingsPid());

        self::assertEquals('RKW', $queueMail->getFromName());
        self::assertEquals('service@mein.rkw.de', $queueMail->getFromAddress());
        self::assertEquals('reply@mein.rkw.de', $queueMail->getReplyAddress());
        self::assertEquals('bounces@mein.rkw.de', $queueMail->getReturnPath());

        static::assertInstanceOf(MailingStatistics::class, $queueMail->getMailingStatistics());
        static::assertGreaterThan(0, $queueMail->getMailingStatistics()->getTstampFavSending());

        FrontendSimulatorUtility::resetFrontendEnvironment();
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getQueueMailReturnsSameObjectOnSecondCall ()
    {

        /**
         * Scenario:
         *
         * Given the method has been called before
         * When the method is called again
         * Then a queueMail-object is returned
         * Then this object is the same as was returned at the first call of the function
         */

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->subject->getQueueMail();
        static::assertSame($queueMail, $this->subject->getQueueMail());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getQueueMailSavesQueueMailToDatabase ()
    {
        /**
         * Scenario:
         *
         * When the method is called again
         * Then a queueMail-object is returned
         * Then this object is persisted in the database
         */

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->subject->getQueueMail();

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMailDb */
        $queueMailDb = $this->queueMailRepository->findAll()->getFirst();
        static::assertSame($queueMail, $queueMailDb);
    }


    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function setQueueMailGivenNonPersistedQueueMailThrowsException ()
    {

        /**
         * Scenario:
         *
         * Given a non-persisted queueMail-object
         * When the method is called
         * Then an exception is thrown
         * Then the code of the exception is 1540294116
         */
        
        static::expectException(\RKW\RkwMailer\Exception::class);
        static::expectExceptionCode(1540193242);

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = GeneralUtility::makeInstance(\RKW\RkwMailer\Domain\Model\QueueMail::class);
        
        $this->subject->setQueueMail($queueMail);
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function setToGivenExistingEmailReturnsFalse()
    {

        /**
         * Scenario:
         *
         * Given a frontendUser-object
         * Given that frontendUser-object has a valid email set
         * Given this e-mail-address has already been added as recipient to the queueMail before
         * When the method is called
         * Then false is returned
         * Then no new queueRecipient-object is added to the database
         */
       
        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $feUser */
        $feUser = GeneralUtility::makeInstance(FrontendUser::class);
        $feUser->setEmail('lauterbach@spd.de');
        
        $this->subject->setTo($feUser);
        static::assertFalse($this->subject->setTo($feUser));
        
        static::assertCount(1, $this->queueRecipientRepository->findAll());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function setToGivenNewEmailReturnsTrue()
    {

        /**
         * Scenario:
         *
         * Given a queueMail-object
         * Given this queueMail-object has templates and paths to the templates set
         * Given this queueMail-object is set to the mailService
         * Given a frontendUser-object
         * Given that frontendUser-object has a valid email set
         * Given that frontendUser-object has a first name
         * Given that frontendUser-object has a last name
         * Given as additionalData a marker array is set
         * Given as additionalData a subject is set
         * Given this e-mail-address has not been added as recipient to the queueMail before
         * When the method is called
         * Then true is returned
         * Then a new queueRecipient-object is added to the database
         * Then the given data is added as property-values to the queueRecipient-object in the database
         * Then this queueRecipient-object is added to the current queueMail-object
         * Then no templates are rendered
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(10);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $feUser */
        $feUser = GeneralUtility::makeInstance(FrontendUser::class);
        $feUser->setEmail('lauterbach@spd.de');
        $feUser->setFirstName('Karl');
        $feUser->setLastName('Lauterbach');

        $additionalData = [
            'marker' => [
                'test' => 'testen',
            ],
            'subject' => 'Wir testen den Betreff',
        ];

        static::assertTrue($this->subject->setTo($feUser, $additionalData));
        static::assertCount(1, $this->queueRecipientRepository->findAll());

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findAll()->getFirst();

        static::assertEquals('lauterbach@spd.de', $queueRecipient->getEmail());
        static::assertEquals('Karl', $queueRecipient->getFirstname());
        static::assertEquals('Lauterbach', $queueRecipient->getLastname());

        static::assertEquals($additionalData['marker'], $queueRecipient->getMarker());
        static::assertEquals($additionalData['subject'], $queueRecipient->getSubject());

        static::assertEquals($this->subject->getQueueMail(), $queueRecipient->getQueueMail());

        static::assertEmpty($this->mailBodyCache->getHtmlBody($queueRecipient));
        static::assertEmpty($this->mailBodyCache->getPlaintextBody($queueRecipient));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function setToGivenNewEmailRendersTemplates()
    {

        /**
         * Scenario:
         *
         * Given a queueMail-object
         * Given this queueMail-object has templates and paths to the templates set
         * Given this queueMail-object is set to the mailService
         * Given a frontendUser-object
         * Given that frontendUser-object has a valid email set
         * Given this e-mail-address has not been added as recipient to the queueMail 
         * Given the renderTemplates-parameter is set to true
         * When the method is called
         * Then true is returned
         * Then a new queueRecipient-object is added to the database
         * Then the given email is added as property-values to the queueRecipient-object in the database
         * Then this queueRecipient-object is added to the current queueMail-object
         * Then the templates are rendered
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(10);
        $this->subject->setQueueMail($queueMail);
        
        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $feUser */
        $feUser = GeneralUtility::makeInstance(FrontendUser::class);
        $feUser->setEmail('lauterbach@spd.de');

        static::assertTrue($this->subject->setTo($feUser, [], true));
        static::assertCount(1, $this->queueRecipientRepository->findAll());

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findAll()->getFirst();

        static::assertEquals('lauterbach@spd.de', $queueRecipient->getEmail());
        
        static::assertEquals($this->subject->getQueueMail(), $queueRecipient->getQueueMail());

        static::assertNotEmpty($this->mailBodyCache->getHtmlBody($queueRecipient));
        static::assertNotEmpty($this->mailBodyCache->getPlaintextBody($queueRecipient));
    }
    
    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function addQueueRecipientGivenInvalidQueueRecipientReturnsFalse()
    {

        /**
         * Scenario:
         *
         * Given a queueRecipient-object with no email-address
         * Given this queueRecipient-object has not been added to the queueMail yet
         * When the method is called
         * Then false is returned
         */
        
        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(QueueRecipient::class);
        static::assertFalse($this->subject->addQueueRecipient($queueRecipient));

    }


    /**
     * @test
     * @throws \Exception
     */
    public function addQueueRecipientGivenQueueRecipientTwiceReturnsFalse()
    {

        /**
         * Scenario:
         *
         * Given a queueRecipient-object
         * Given this queueRecipient-object has a valid email-address
         * Given this queueRecipient-object has been added to the queueMail before
         * When the method is called
         * Then false is returned
         */

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(QueueRecipient::class);
        $queueRecipient->setEmail('debug@rkw.de');
        $this->subject->addQueueRecipient($queueRecipient);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(QueueRecipient::class);
        $queueRecipient->setEmail('debug@rkw.de');
            
        static::assertFalse($this->subject->addQueueRecipient($queueRecipient));

    }

    /**
     * @test
     * @throws \Exception
     */
    public function addQueueRecipientGivenValidQueueRecipientReturnsTrue()
    {

        /**
         * Scenario:
         *
         * Given a queueRecipient-object 
         * Given this queueRecipient-object has a valid email-address
         * Given this queueRecipient-object has not been added to the queueMail yet
         * When the method is called
         * Then true is returned
         * Then the queueRecipient is added to the database
         * Then the email-property is set accordingly
         */

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(QueueRecipient::class);
        $queueRecipient->setEmail('debug@rkw.de');
        
        static::assertTrue($this->subject->addQueueRecipient($queueRecipient));
        static::assertCount(1, $this->queueRecipientRepository->findAll());

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipientDb */
        $queueRecipientDb = $this->queueRecipientRepository->findAll()->getFirst();
        static::assertEquals('debug@rkw.de', $queueRecipientDb->getEmail());


    }
    
    /**
     * @test
     * @throws \Exception
     */
    public function addQueueRecipientGivenValidQueueRecipientSetsAllProperties()
    {

        /**
         * Scenario:
         *
         * Given a queueRecipient-object
         * Given this queueRecipient-object has a valid email-address
         * Given this queueRecipient-object has a salutation
         * Given this queueRecipient-object has a first name
         * Given this queueRecipient-object has a last name
         * Given this queueRecipient-object has a title
         * Given this queueRecipient-object has not been added to the queueMail yet
         * When the method is called
         * Then true is returned
         * Then the queueRecipient is added to the database
         * Then the email-property is set accordingly
         * Then the salutation-property is set accordingly
         * Then the firstName-property is set accordingly
         * Then the lastNae-property is set accordingly
         * Then the title-property is set accordingly
         */

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(QueueRecipient::class);
        $queueRecipient->setEmail('debug@rkw.de');
        $queueRecipient->setSalutation(1);
        $queueRecipient->setFirstName('Karl');
        $queueRecipient->setLastName('Lauterbach');
        $queueRecipient->setTitle('Dr.');

        static::assertTrue($this->subject->addQueueRecipient($queueRecipient));
        static::assertCount(1, $this->queueRecipientRepository->findAll());

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipientDb */
        $queueRecipientDb = $this->queueRecipientRepository->findAll()->getFirst();
        static::assertEquals('debug@rkw.de', $queueRecipientDb->getEmail());
        static::assertEquals(1, $queueRecipientDb->getSalutation());
        static::assertEquals('Karl', $queueRecipientDb->getFirstName());
        static::assertEquals('Lauterbach', $queueRecipientDb->getLastName());
        static::assertEquals('Dr.', $queueRecipientDb->getTitle());
    }

    
    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function addQueueRecipientSetsDefaultValues()
    {

        /**
         * Scenario:
         *
         * Given a queueRecipient-object with a valid email-address
         * Given this queueRecipient-object has not been added to the queueMail yet
         * When the method is called
         * Then true is returned
         * Then the queueRecipient is added to the database
         * Then the status-property of the queueRecipient-object is set to waiting
         * Then the pid-property of the queueRecipient-object is set to according to configuration
         * Then the queueMail-property of the queueRecipient-object is set to the current queueMail
         */
        
        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(QueueRecipient::class);
        $queueRecipient->setEmail('debug@rkw.de');

        static::assertTrue($this->subject->addQueueRecipient($queueRecipient));
        static::assertCount(1, $this->queueRecipientRepository->findAll());

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipientDb */
        $queueRecipientDb = $this->queueRecipientRepository->findAll()->getFirst();
        static::assertEquals(2, $queueRecipientDb->getStatus());
        static::assertEquals(9999, $queueRecipientDb->getPid());
        
        $queueMail = $this->subject->getQueueMail();
        static::assertEquals($queueMail->getUid(), $queueRecipientDb->getQueueMail()->getUid());

    }
    
    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function hasQueueRecipientGivenExistingQueueRecipientReturnsTrue()
    {


        /**
         * Scenario:
         *
         * Given a queueRecipient-object
         * Given this queueRecipient-object has a valid email-address
         * Given this queueRecipient-object has been added to the queueMail before
         * When the method is called
         * Then true is returned
         */

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(QueueRecipient::class);
        $queueRecipient->setEmail('debug@rkw.de');
        $this->subject->addQueueRecipient($queueRecipient);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(QueueRecipient::class);
        $queueRecipient->setEmail('debug@rkw.de');

        static::assertTrue($this->subject->hasQueueRecipient($queueRecipient));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function hasQueueRecipientGivenExistingEmailReturnsTrue()
    {


        /**
         * Scenario:
         *
         * Given an e-mail-address
         * Given this e-mail-address has been added as queueRecipient to the queueMail before
         * When the method is called
         * Then true is returned
         */

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(QueueRecipient::class);
        $queueRecipient->setEmail('debug@rkw.de');
        $this->subject->addQueueRecipient($queueRecipient);

        static::assertTrue($this->subject->hasQueueRecipient('debug@rkw.de'));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function hasQueueRecipientGivenNewQueueRecipientReturnsTrue()
    {


        /**
         * Scenario:
         *
         * Given a queueRecipient-object
         * Given this queueRecipient-object has a valid email-address
         * Given this queueRecipient-object has not been added to the queueMail before
         * When the method is called
         * Then false is returned
         */
        
        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(QueueRecipient::class);
        $queueRecipient->setEmail('debug@rkw.de');

        static::assertFalse($this->subject->hasQueueRecipient($queueRecipient));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function hasQueueRecipientGivenNewEmailReturnsTrue()
    {
        
        /**
         * Scenario:
         *
         * Given an e-mail-address
         * Given this e-mail-address has not been added as queueRecipient to the queueMail before
         * When the method is called
         * Then false is returned
         */

        static::assertFalse($this->subject->hasQueueRecipient('debug@rkw.de'));
    }
    
    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function sendUsingInvalidQueueMailObjectThrowsException ()
    {

        /**
         * Scenario:
         *
         * Given an invalid queueMail-object
         * When the method is called
         * Then an exception is thrown
         * Then the code of the exception is 1540186577
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        static::expectException(\RKW\RkwMailer\Exception::class);
        static::expectExceptionCode(1540186577);

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(20);

        $this->subject->setQueueMail($queueMail);
        $this->subject->send();
        
    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendUsingQueueMailWithStatusNotDraftReturnsFalse ()
    {

        /**
         * Scenario:
         *
         * Given an valid queueMail-object
         * Given this queueMail-object has the status waiting
         * Given this queueMail-object has one recipient
         * When the method is called
         * Then false is returned
         * Then the status of the queueMail-object is not changed
         */
        
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(30);

        $this->subject->setQueueMail($queueMail);
        self::assertFalse($this->subject->send());
        self::assertEquals(2, $queueMail->getStatus());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendUsingQueueMailWithNoRecipientsReturnsFalse ()
    {

        /**
         * Scenario:
         *
         * Given a valid queueMail-object
         * Given this queueMail-object has the status draft
         * Given this queueMail has no recipients
         * When the method is called
         * Then false is returned
         * Then the status of the queueMail-object is not changed
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check40.xml');

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(40);

        $this->subject->setQueueMail($queueMail);
        self::assertFalse($this->subject->send());
        self::assertEquals(1, $queueMail->getStatus());
        
    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendUsingQueueMailWithNoRecipientsWithStatusWaitingReturnsFalse ()
    {

        /**
         * Scenario:
         *
         * Given a valid queueMail-object
         * Given this queueMail-object has the status draft
         * Given this queueMail has one recipient
         * Given this queueRecipient-object has the status draft
         * When the method is called
         * Then false is returned
         * Then the status of the queueMail-object is not changed
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check50.xml');

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(50);

        $this->subject->setQueueMail($queueMail);
        self::assertFalse($this->subject->send());
        self::assertEquals(1, $queueMail->getStatus());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendReturnsTrue ()
    {

        /**
         * Scenario:
         *
         * Given a valid queueMail-object
         * Given this queueMail-object has the status draft
         * Given this queueMail has one recipient
         * When the method is called
         * Then true is returned
         * Then the status of the queueMail-object is changed to waiting
         * Then the queueMail-object of the mailService is reset
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check60.xml');

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(60);

        $this->subject->setQueueMail($queueMail);
        self::assertTrue($this->subject->send());
        self::assertEquals(2, $queueMail->getStatus());
        self::assertNotEquals($this->subject->getQueueMail(), $queueMail);

    }


    //=============================================

    /**
     * TearDown
     */
    protected function tearDown()
    {
        $this->mailBodyCache->clearCache();
        parent::tearDown();
    }








}