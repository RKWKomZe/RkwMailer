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
use RKW\RkwBasics\Domain\Model\Pages;
use RKW\RkwMailer\Cache\MailBodyCache;
use RKW\RkwMailer\Domain\Model\QueueRecipient;
use RKW\RkwMailer\Domain\Repository\QueueMailRepository;
use RKW\RkwMailer\Domain\Repository\QueueRecipientRepository;
use RKW\RkwMailer\Persistence\MarkerReducer;
use RKW\RkwMailer\Service\MailService;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
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
        $this->mailBodyCache = $this->objectManager->get(MailBodyCache::class);
        $this->subject = $this->objectManager->get(MailService::class);
    }



    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function renderTemplatesThrowsExceptionIfQueueRecipientNotPersisted()
    {

        /**
         * Scenario:
         *
         * Given a non-persisted queueRecipient-object
         * When the method is called
         * Then an exception is thrown
         * Then the code of the exception is 1540294116
         */
        static::expectException(\RKW\RkwMailer\Exception::class);
        static::expectExceptionCode(1540294116);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = new QueueRecipient();

        $this->subject->renderTemplates($queueRecipient);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function renderTemplatesWithNoTemplatesSetDoesNothing()
    {

        /**
         * Scenario:
         *
         * Given a persisted queueRecipient-object
         * Given a persisted queueMail-object
         * Given that queueMail-object has no templates set
         * When the method is called
         * Then no templates are rendered into the cache
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');
        
        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(10);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(10);
        
        $this->subject->setQueueMail($queueMail);
        $this->subject->renderTemplates($queueRecipient);
        
        self::assertEmpty($this->mailBodyCache->getPlaintextBody($queueRecipient));
        self::assertEmpty($this->mailBodyCache->getHtmlBody($queueRecipient));
        self::assertEmpty($this->mailBodyCache->getCalendarBody($queueRecipient));


    }


    /**
     * @test
     * @throws \Exception
     */
    public function renderTemplatesRendersAllTemplates()
    {

        /**
         * Scenario:
         *
         * Given a persisted queueRecipient-object
         * Given a persisted queueMail-object
         * Given that queueMail-object has templates for all three types set
         * When the method is called
         * Then all three template-types are rendered into the cache
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(20);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(20);

        $this->subject->setQueueMail($queueMail);
        $this->subject->renderTemplates($queueRecipient);

        self::assertNotEmpty($this->mailBodyCache->getPlaintextBody($queueRecipient));
        self::assertNotEmpty($this->mailBodyCache->getHtmlBody($queueRecipient));
        self::assertNotEmpty($this->mailBodyCache->getCalendarBody($queueRecipient));
        
    }

    /**
     * @test
     * @throws \Exception
     */
    public function renderTemplatesRendersHtmlTemplateWithAllMarkers()
    {

        /**
         * Scenario:
         *
         * Given a persisted queueRecipient-object
         * Given that queueRecipient-object has the marker-property set
         * Given a persisted queueMail-object
         * Given that queueMail-object has only a html-template set
         * When the method is called
         * Then the html-template is rendered only
         * Then all markers that where stored in the queueRecipient-object are replaced
         * Then all default markers are replaced
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(30);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(30);

        $this->subject->setQueueMail($queueMail);
        $this->subject->renderTemplates($queueRecipient);
        
        self::assertEmpty($this->mailBodyCache->getPlaintextBody($queueRecipient));
        self::assertEmpty($this->mailBodyCache->getCalendarBody($queueRecipient));
        self::assertNotEmpty($this->mailBodyCache->getHtmlBody($queueRecipient));
        
        $result = $this->mailBodyCache->getHtmlBody($queueRecipient);
        static::assertContains('TEST-TEMPLATE-HTML', $result);
        static::assertContains('ROOTPAGE', $result);
        static::assertContains('queueMail.uid: 30', $result);
        static::assertContains('queueMail.settingsPid: 0', $result);
        static::assertContains('mailType: Html', $result);
        static::assertContains('settings.redirectPid: 9999', $result);

        static::assertContains('baseUrl: http://www.example.de', $result);
        static::assertContains('baseUrlImages: http://www.example.de/typo3conf/ext/rkw_mailer/Resources/Public/Images', $result);
        static::assertContains('baseUrlLogo: http://www.example.de/typo3conf/ext/rkw_mailer/Resources/Public/Images/logo.png', $result);

        static::assertContains('queueRecipient.uid: 30', $result);
        static::assertContains('queueRecipient.firstName: Sebastian', $result);
        static::assertContains('queueRecipient.lastName: Schmidt', $result);

        static::assertContains('test1.uid: 30', $result);
        static::assertContains('test2: Hello!', $result);
    }
    
    /**
     * @test
     * @throws \Exception
     */
    public function renderTemplatesRendersPlaintextTemplateWithAllMarkers()
    {

        /**
         * Scenario:
         *
         * Given a persisted queueRecipient-object
         * Given that queueRecipient-object has the marker-property set
         * Given a persisted queueMail-object
         * Given that queueMail-object has only a plaintext-template set
         * When the method is called
         * Then the plaintext-template is rendered only
         * Then all markers that where stored in the queueRecipient-object are replaced
         * Then all default markers are replaced
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check40.xml');

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(40);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(40);

        $this->subject->setQueueMail($queueMail);
        $this->subject->renderTemplates($queueRecipient);

        self::assertEmpty($this->mailBodyCache->getHtmlBody($queueRecipient));
        self::assertEmpty($this->mailBodyCache->getCalendarBody($queueRecipient));
        self::assertNotEmpty($this->mailBodyCache->getPlaintextBody($queueRecipient));

        $result = $this->mailBodyCache->getPlaintextBody($queueRecipient);
        static::assertContains('TEST-TEMPLATE-PLAINTEXT', $result);
        static::assertContains('ROOTPAGE', $result);
        static::assertContains('queueMail.uid: 40', $result);
        static::assertContains('queueMail.settingsPid: 0', $result);
        static::assertContains('mailType: Plaintext', $result);
        static::assertContains('settings.redirectPid: 9999', $result);

        static::assertContains('baseUrl: http://www.example.de', $result);
        static::assertContains('baseUrlImages: http://www.example.de/typo3conf/ext/rkw_mailer/Resources/Public/Images', $result);
        static::assertContains('baseUrlLogo: http://www.example.de/typo3conf/ext/rkw_mailer/Resources/Public/Images/logo.png', $result);

        static::assertContains('queueRecipient.uid: 40', $result);
        static::assertContains('queueRecipient.firstName: Sebastian', $result);
        static::assertContains('queueRecipient.lastName: Schmidt', $result);

        static::assertContains('test1.uid: 40', $result);
        static::assertContains('test2: Hello!', $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function renderTemplatesRendersCalendarTemplateWithAllMarkers()
    {

        /**
         * Scenario:
         *
         * Given a persisted queueRecipient-object
         * Given that queueRecipient-object has the marker-property set
         * Given a persisted queueMail-object
         * Given that queueMail-object has only a calendar-template set
         * When the method is called
         * Then the calendar-template is rendered only
         * Then all markers that where stored in the queueRecipient-object are replaced
         * Then all default markers are replaced
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check50.xml');

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(50);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(50);

        $this->subject->setQueueMail($queueMail);
        $this->subject->renderTemplates($queueRecipient);

        self::assertEmpty($this->mailBodyCache->getHtmlBody($queueRecipient));
        self::assertEmpty($this->mailBodyCache->getPlaintextBody($queueRecipient));
        self::assertNotEmpty($this->mailBodyCache->getCalendarBody($queueRecipient));

        $result = $this->mailBodyCache->getCalendarBody($queueRecipient);
        static::assertContains('BEGIN:VCALENDAR', $result);
        static::assertContains('ROOTPAGE', $result);
        static::assertContains('queueMail.uid: 50', $result);
        static::assertContains('queueMail.settingsPid: 0', $result);
        static::assertContains('mailType: Calendar', $result);
        static::assertContains('settings.redirectPid: 9999', $result);

        static::assertContains('baseUrl: http://www.example.de', $result);
        static::assertContains('baseUrlImages: http://www.example.de/typo3conf/ext/rkw_mailer/Resources/Public/Images', $result);
        static::assertContains('baseUrlLogo: http://www.example.de/typo3conf/ext/rkw_mailer/Resources/Public/Images/logo.png', $result);

        static::assertContains('queueRecipient.uid: 50', $result);
        static::assertContains('queueRecipient.firstName: Sebastian', $result);
        static::assertContains('queueRecipient.lastName: Schmidt', $result);

        static::assertContains('test1.uid: 50', $result);
        static::assertContains('test2: Hello!', $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function renderTemplatesDoesNotRenderTwiceForTheSameQueueRecipient()
    {

        /**
         * Scenario:
         *
         * Given a persisted queueRecipient-object
         * Given that queueRecipient-object has the marker-property set
         * Given to that markers the current timestamp is added
         * Given a persisted queueMail-object
         * Given that queueMail-object has templates for all three types set
         * Given the method has already been called with the same queueRecipient
         * Given the timestamp has been changed in the markers of the queueRecipient-object after that first call
         * When the method is called a second time
         * Then all three template-types were rendered into the cache
         * Then all three template-codes returned after both calls of the method are identical for each type
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check60.xml');

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(60);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(60);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipientTwo */
        $queueRecipientTwo = $this->queueRecipientRepository->findByIdentifier(60);
        $queueRecipient->setMarker(['currentTime' => time()]);
            
        $this->subject->setQueueMail($queueMail);
        $this->subject->renderTemplates($queueRecipient);
        
        $resultPlaintextFirst = $this->mailBodyCache->getPlaintextBody($queueRecipient);
        $resultHtmlFirst = $this->mailBodyCache->getHtmlBody($queueRecipient);
        $resultCalendarFirst = $this->mailBodyCache->getCalendarBody($queueRecipient);

        $queueRecipientTwo->setMarker(['currentTime' => time() + 20000]);
        $this->subject->renderTemplates($queueRecipientTwo);

        $resultPlaintextSecond = $this->mailBodyCache->getPlaintextBody($queueRecipientTwo);
        $resultHtmlSecond = $this->mailBodyCache->getHtmlBody($queueRecipientTwo);
        $resultCalendarSecond = $this->mailBodyCache->getCalendarBody($queueRecipientTwo);

        self::assertEquals($resultPlaintextFirst, $resultPlaintextSecond);
        self::assertEquals($resultHtmlFirst, $resultHtmlSecond);
        self::assertEquals($resultCalendarFirst, $resultCalendarSecond);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function renderTemplatesDoesRenderTwiceForTheSameQueueRecipientWhenCacheFlushed()
    {

        /**
         * Scenario:
         *
         * Given a persisted queueRecipient-object
         * Given that queueRecipient-object has the marker-property set
         * Given to that markers the current timestamp is added
         * Given a persisted queueMail-object
         * Given that queueMail-object has templates for all three types set
         * Given the method has already been called with the same queueRecipient
         * Given the timestamp has been changed in the markers of the queueRecipient-object after that first call
         * Given the cache has been flushed after the first call of the method
         * When the method is called a second time
         * Then all three template-types were rendered into the cache
         * Then all three template-codes returned after both calls of the method are not identical for each type
         * Then all three template-codes of the first call of the method contain the first timestamp
         * Then all three template-codes of the second call of the method contain the second timestamp 
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check60.xml');

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(60);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(60);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipientTwo */
        $queueRecipientTwo = $this->queueRecipientRepository->findByIdentifier(60);

        $firstTimestamp = time();
        $queueRecipient->setMarker(['currentTime' => $firstTimestamp]);

        $this->subject->setQueueMail($queueMail);
        $this->subject->renderTemplates($queueRecipient);

        $resultPlaintextFirst = $this->mailBodyCache->getPlaintextBody($queueRecipient);
        $resultHtmlFirst = $this->mailBodyCache->getHtmlBody($queueRecipient);
        $resultCalendarFirst = $this->mailBodyCache->getCalendarBody($queueRecipient);

        $this->mailBodyCache->clearCache();

        $secondTimestamp = time() + 20000;
        $queueRecipientTwo->setMarker(['currentTime' => $secondTimestamp]);
        $this->subject->renderTemplates($queueRecipientTwo);

        $resultPlaintextSecond = $this->mailBodyCache->getPlaintextBody($queueRecipientTwo);
        $resultHtmlSecond = $this->mailBodyCache->getHtmlBody($queueRecipientTwo);
        $resultCalendarSecond = $this->mailBodyCache->getCalendarBody($queueRecipientTwo);

        self::assertNotEquals($resultPlaintextFirst, $resultPlaintextSecond);
        self::assertNotEquals($resultHtmlFirst, $resultHtmlSecond);
        self::assertNotEquals($resultCalendarFirst, $resultCalendarSecond);

        self::assertContains("$firstTimestamp", $resultPlaintextFirst);
        self::assertContains("$firstTimestamp", $resultHtmlFirst);
        self::assertContains("$firstTimestamp", $resultCalendarFirst);

        self::assertContains("$secondTimestamp", $resultPlaintextSecond);
        self::assertContains("$secondTimestamp", $resultHtmlSecond);
        self::assertContains("$secondTimestamp", $resultCalendarSecond);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function renderTemplatesDoesRenderTwiceForDifferentQueueRecipients()
    {

        /**
         * Scenario:
         *
         * Given a persisted queueRecipient-object
         * Given that queueRecipient-object has the marker-property set
         * Given to that markers the current timestamp is added
         * Given a persisted queueMail-object
         * Given that queueMail-object has templates for all three types set
         * Given the method has already been called with the another queueRecipient
         * Given the timestamp has been changed in the markers of the queueRecipient-object after that first call
         * When the method is called a second time
         * Then all three template-types were rendered into the cache
         * Then all three template-codes returned after both calls of the method are not identical for each type
         * Then all three template-codes of the first call of the method contain the first timestamp
         * Then all three template-codes of the second call of the method contain the second timestamp
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check70.xml');

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(70);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(70);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipientTwo */
        $queueRecipientTwo = $this->queueRecipientRepository->findByIdentifier(71);

        $firstTimestamp = time();
        $queueRecipient->setMarker(['currentTime' => $firstTimestamp]);

        $this->subject->setQueueMail($queueMail);
        $this->subject->renderTemplates($queueRecipient);

        $resultPlaintextFirst = $this->mailBodyCache->getPlaintextBody($queueRecipient);
        $resultHtmlFirst = $this->mailBodyCache->getHtmlBody($queueRecipient);
        $resultCalendarFirst = $this->mailBodyCache->getCalendarBody($queueRecipient);

        $secondTimestamp = time() + 20000;
        $queueRecipientTwo->setMarker(['currentTime' => $secondTimestamp]);
        $this->subject->renderTemplates($queueRecipientTwo);

        $resultPlaintextSecond = $this->mailBodyCache->getPlaintextBody($queueRecipientTwo);
        $resultHtmlSecond = $this->mailBodyCache->getHtmlBody($queueRecipientTwo);
        $resultCalendarSecond = $this->mailBodyCache->getCalendarBody($queueRecipientTwo);

        self::assertNotEquals($resultPlaintextFirst, $resultPlaintextSecond);
        self::assertNotEquals($resultHtmlFirst, $resultHtmlSecond);
        self::assertNotEquals($resultCalendarFirst, $resultCalendarSecond);
        
        self::assertContains("$firstTimestamp", $resultPlaintextFirst);
        self::assertContains("$firstTimestamp", $resultHtmlFirst);
        self::assertContains("$firstTimestamp", $resultCalendarFirst);

        self::assertContains("$secondTimestamp", $resultPlaintextSecond);
        self::assertContains("$secondTimestamp", $resultHtmlSecond);
        self::assertContains("$secondTimestamp", $resultCalendarSecond);
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