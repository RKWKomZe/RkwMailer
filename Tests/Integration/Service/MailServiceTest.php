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
use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwMailer\Cache\MailBodyCache;
use RKW\RkwMailer\Domain\Model\MailingStatistics;
use RKW\RkwMailer\Domain\Model\QueueMail;
use RKW\RkwMailer\Domain\Model\QueueRecipient;
use RKW\RkwMailer\Domain\Repository\MailingStatisticsRepository;
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
     * TearDown
     */
    protected function tearDown()
    {
        $this->mailBodyCache->clearCache();
        parent::tearDown();
    }








}