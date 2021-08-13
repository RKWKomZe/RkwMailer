<?php
namespace RKW\RkwMailer\Tests\Integration\Statistics;

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
use RKW\RkwMailer\Statistics\LinkStatistics;
use RKW\RkwMailer\Domain\Repository\QueueMailRepository;
use RKW\RkwMailer\Domain\Repository\QueueRecipientRepository;
use RKW\RkwMailer\Domain\Repository\LinkRepository;
use RKW\RkwMailer\Domain\Repository\StatisticOpeningRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * LinkStatisticsTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class LinkStatisticsTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/LinkStatisticsTest/Fixtures';


    /**
     * Signal name
     *
     * @const string
     */
    const NUMBER_OF_STATISTIC_OPENINGS = 3;

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_registration',
        'typo3conf/ext/rkw_mailer',
    ];

    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [];

    /**
     * @var \RKW\RkwMailer\Statistics\LinkStatistics
     */
    private $subject = null;

    /**
     * @var \RKW\RkwMailer\Domain\Repository\QueueMailRepository
     */
    private $queueMailRepository;

    /**
     * @var \RKW\RkwMailer\Domain\Repository\QueueRecipientRepository
     */
    private $queueRecipientRepository;

    /**
     * @var \RKW\RkwMailer\Domain\Repository\LinkRepository
     */
    private $linkRepository;

    /**
     * @var \RKW\RkwMailer\Domain\Repository\StatisticOpeningRepository
     */
    private $statisticOpeningRepository;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    private $persistenceManager = null;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager = null;


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
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->queueMailRepository = $this->objectManager->get(QueueMailRepository::class);
        $this->queueRecipientRepository = $this->objectManager->get(QueueRecipientRepository::class);
        $this->linkRepository = $this->objectManager->get(LinkRepository::class);
        $this->statisticOpeningRepository = $this->objectManager->get(StatisticOpeningRepository::class);

        $this->subject = $this->objectManager->get(LinkStatistics::class);

    }


    /**
     * @test
     */
    public function getRedirectLinkReturnsEmptyOnInvalidLinkHashButValidQueueMail()
    {

        /**
         * Scenario:
         *
         * Given a valid queueMail-uid
         * Given an invalid link-hash
         * When the method is called
         * Then an empty string is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');
        static::assertEmpty($this->subject->getRedirectLink('abc', 100));
    }

    /**
     * @test
     */
    public function getRedirectLinkReturnsLinkOnInValidQueueMailButValidLinkHash ()
    {

        /**
         * Scenario:
         *
         * Given an invalid queueMail-uid
         * Given a valid link-hash
         * When the method is called
         * Then the corresponding link is returned
         * Then no queueMail-parameter is added to the link
         * Then no queueRecipient-parameter is added to the link
         * Then no entry is created in the statistic table
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        $result = $this->subject->getRedirectLink('48723b1aa49952c291e71078d6690caabd1370ae', 99999);
        static::assertEquals('http://aprodi-projekt.de', $result);
        static::assertNotContains('tx_rkwmailer[mid]=', $result);
        static::assertNotContains('tx_rkwmailer[uid]=', $result);
        static::assertCount(0,$this->statisticOpeningRepository->findAll());
    }


    /**
     * @test
     */
    public function getRedirectLinkReturnsLinkOnNonMatchingQueueMailLinkHashCombination()
    {

        /**
         * Scenario:
         *
         * Given a valid queueMail-uid
         * Given a valid link-hash
         * Given the link-hash does not belong to the given queueMail-uid
         * When the method is called
         * Then the corresponding link is returned
         * Then no queueMail-parameter is added to the link
         * Then no queueRecipient-parameter is added to the link
         * Then no entry is created in the statistic table
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        $result = $this->subject->getRedirectLink('48723b1aa49952c291e71078d6690caabd1370ae', 100);
        static::assertEquals('http://aprodi-projekt.de', $result);
        static::assertNotContains('tx_rkwmailer[mid]=', $result);
        static::assertNotContains('tx_rkwmailer[uid]=', $result);
        static::assertEquals(0, $this->statisticOpeningRepository->findAll()->count());

    }


    /**
     * @test
     */
    public function getRedirectLinkReturnsAnchorLinkOnNonMatchingQueueMailLinkHashCombination()
    {

        /**
         * Scenario:
         *
         * Given a valid queueMail-uid
         * Given a valid link-hash
         * Given the link-hash does not belong to the given queueMail-uid
         * When the method is called
         * Then the corresponding link with an anchor attached is returned
         * Then no queueMail-parameter is added to the link
         * Then no queueRecipient-parameter is added to the link
         * Then no entry is created in the statistic table
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check40.xml');

        $result = $this->subject->getRedirectLink('48723b1aa49952c291e71078d6690caabd1370ae', 100);
        static::assertEquals('http://aprodi-projekt.de#anchor-1', $result);
        static::assertNotContains('tx_rkwmailer[mid]=', $result);
        static::assertNotContains('tx_rkwmailer[uid]=', $result);
        static::assertEquals(0, $this->statisticOpeningRepository->findAll()->count());

    }


    /**
     * @test
     */
    public function getRedirectLinkReturnsLinkAndCreatesTracking ()
    {
        /**
         * Scenario:
         *
         * Given a valid queueMail-uid
         * Given a valid link-hash
         * Given the link-hash belongs to the given queueMail-uid
         * When the method is called
         * Then the corresponding link is returned
         * Then a queueMail-parameter with the given queueMail-uid is added to the link
         * Then no queueRecipient-parameter is added to the link
         * Then a new entry is created in the statistic table
         * Then the counter of the entry in the statistic table is set to one
         * Then the entry in the statistic table is referenced to the queueMail given
         * Then the entry in the statistic table is not referenced to any queueRecipient
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check50.xml');

        $result =  $this->subject->getRedirectLink('48723b1aa49952c291e71078d6690caabd1370ae', 100);
        static::assertEquals('http://aprodi-projekt.de?tx_rkwmailer[mid]=100', $result);
        static::assertNotContains('tx_rkwmailer[uid]=', $result);

        static::assertEquals(1,$this->statisticOpeningRepository->countAll());
        
        /** @var  \RKW\RkwMailer\Domain\Model\StatisticOpening $statisticOpening */
        $statisticOpening = $this->statisticOpeningRepository->findByUid(1);
        
        static::assertEquals(1, $statisticOpening->getClickCount());
        static::assertEquals(100, $statisticOpening->getQueueMail()->getUid());
        static::assertEmpty($statisticOpening->getQueueRecipient());


    }

    /**
     * @test
     */
    public function getRedirectLinkReturnsAnchorLinkAndCreatesTracking ()
    {
        
        /**
         * Scenario:
         *
         * Given a valid queueMail-uid
         * Given a valid link-hash
         * Given the link-hash belongs to the given queueMail-uid
         * When the method is called
         * Then the corresponding link with an anchor is returned
         * Then a queueMail-parameter with the given queueMail-uid is added to the link
         * Then the anchor is placed after the queueMail-parameter
         * Then no queueRecipient-parameter is added to the link
         * Then a new entry is created in the statistic table
         * Then the counter of the entry in the statistic table is set to one
         * Then the entry in the statistic table is referenced to the queueMail given
         * Then the entry in the statistic table is not referenced to any queueRecipient
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check60.xml');

        $result = $this->subject->getRedirectLink('48723b1aa49952c291e71078d6690caabd1370ae', 100);
        static::assertEquals('http://aprodi-projekt.de?tx_rkwmailer[mid]=100#anchor-1', $result);
        static::assertNotContains('tx_rkwmailer[uid]=', $result);

        static::assertEquals(1,$this->statisticOpeningRepository->countAll());

        /** @var  \RKW\RkwMailer\Domain\Model\StatisticOpening $statisticOpening */
        $statisticOpening = $this->statisticOpeningRepository->findByUid(1);

        static::assertEquals(1, $statisticOpening->getClickCount());
        static::assertEquals(100, $statisticOpening->getQueueMail()->getUid());
        static::assertEmpty($statisticOpening->getQueueRecipient());

    }


    /**
     * @test
     */
    public function getRedirectLinkReturnsLinkAndUpdatesExistingTracking ()
    {
        /**
         * Scenario:
         *
         * Given a valid queueMail-uid
         * Given a valid link-hash
         * Given the link-hash belongs to the given queueMail-uid
         * Given this combination of queueMail-uid and link-hash has already been tracked one time
         * Given this combination of queueMail-uid and link-hash has already been tracked one time together with a queueRecipient-uid
         * When the method is called
         * Then the corresponding link is returned
         * Then a queueMail-parameter with the given queueMail-uid is added to the link
         * Then no queueRecipient-parameter is added to the link
         * Then no new entry is created in the statistic table
         * Then the counter of the existing entry in the statistic table for the queueMail-uid and link-hash-combination is updated to two
         * Then the updated entry in the statistic table is referenced to the queueMail given
         * Then the updated entry in the statistic table is not referenced to any queueRecipient
         * Then the counter of the existing entry in the statistic table for the queueMail-uid, queueRecipient-uid and link-hash-combination is not updated
         * Then the non-updated entry in the statistic table is referenced to the queueMail given
         * Then the non-updated entry in the statistic table is referenced to a queueRecipient
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check70.xml');

        $result = $this->subject->getRedirectLink('48723b1aa49952c291e71078d6690caabd1370ae', 100);
        static::assertEquals('http://aprodi-projekt.de?tx_rkwmailer[mid]=100', $result);
        static::assertNotContains('tx_rkwmailer[uid]=', $result);

        static::assertEquals(2, $this->statisticOpeningRepository->countAll());

        /** @var  \RKW\RkwMailer\Domain\Model\StatisticOpening $statisticOpening */
        $statisticOpening = $this->statisticOpeningRepository->findByUid(100);

        static::assertEquals(2, $statisticOpening->getClickCount());
        static::assertEquals(100, $statisticOpening->getQueueMail()->getUid());
        static::assertEmpty($statisticOpening->getQueueRecipient());

        /** @var  \RKW\RkwMailer\Domain\Model\StatisticOpening $statisticOpening */
        $statisticOpening = $this->statisticOpeningRepository->findByUid(110);

        static::assertEquals(1, $statisticOpening->getClickCount());
        static::assertEquals(100, $statisticOpening->getQueueMail()->getUid());
        static::assertEquals(100, $statisticOpening->getQueueRecipient()->getUid());

    }

    /**
     * @test
     */
    public function getRedirectLinkReturnsLinkAndIgnoresNonMatchingQueueRecipient ()
    {
        /**
         * Scenario:
         *
         * Given a valid queueMail-uid
         * Given a valid queueRecipient-uid
         * Given a valid link-hash
         * Given the link-hash belongs to the given queueMail-uid
         * Given the queueRecipient-uid does not belong to the given queueMail-uid
         * When the method is called
         * Then the corresponding link is returned
         * Then a queueMail-parameter with the given queueMail-uid is added to the link
         * Then no queueRecipient-parameter is added to the link
         * Then a new entry is created in the statistic table
         * Then the counter of the entry in the statistic table is set to one
         * Then the entry in the statistic table is referenced to the queueMail given
         * Then the entry in the statistic table is not referenced to any queueRecipient
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check80.xml');

        $result = $this->subject->getRedirectLink('48723b1aa49952c291e71078d6690caabd1370ae', 100, 100);
        static::assertEquals('http://aprodi-projekt.de?tx_rkwmailer[mid]=100', $result );
        static::assertNotContains('tx_rkwmailer[uid]=', $result);

        static::assertEquals(1,$this->statisticOpeningRepository->countAll());

        /** @var  \RKW\RkwMailer\Domain\Model\StatisticOpening $statisticOpening */
        $statisticOpening = $this->statisticOpeningRepository->findByUid(1);

        static::assertEquals(1, $statisticOpening->getClickCount());
        static::assertEquals(100, $statisticOpening->getQueueMail()->getUid());
        static::assertEmpty($statisticOpening->getQueueRecipient());

    }


    /**
     * @test
     */
    public function getRedirectLinkReturnsLinkAddsMatchingQueueRecipientAsParameter ()
    {
        /**
         * Scenario:
         *
         * Given a valid queueMail-uid
         * Given a valid queueRecipient-uid
         * Given a valid link-hash
         * Given the link-hash belongs to the given queueMail-uid
         * Given the queueRecipient-uid belongs to the given queueMail-uid
         * When the method is called
         * Then the corresponding link is returned
         * Then a queueMail-parameter with the given queueMail-uid is added to the link
         * Then a queueRecipient-parameter with the given queueRecipient-uid is added to the link
         * Then a new entry is created in the statistic table
         * Then the counter of the entry in the statistic table is set to one
         * Then the entry in the statistic table is referenced to the queueMail given
         * Then the entry in the statistic table is not referenced to any queueRecipient
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check90.xml');

        $result = $this->subject->getRedirectLink('48723b1aa49952c291e71078d6690caabd1370ae', 100, 100);
        static::assertEquals('http://aprodi-projekt.de?tx_rkwmailer[mid]=100&tx_rkwmailer[uid]=100', $result );

        static::assertEquals(1,$this->statisticOpeningRepository->countAll());

        /** @var  \RKW\RkwMailer\Domain\Model\StatisticOpening $statisticOpening */
        $statisticOpening = $this->statisticOpeningRepository->findByUid(1);

        static::assertEquals(1, $statisticOpening->getClickCount());
        static::assertEquals(100, $statisticOpening->getQueueMail()->getUid());
        static::assertEmpty($statisticOpening->getQueueRecipient());

    }
    
    //=============================================


    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }








}