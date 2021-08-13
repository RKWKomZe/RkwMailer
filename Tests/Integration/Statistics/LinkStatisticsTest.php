<?php
namespace RKW\RkwMailer\Tests\Integration\Statistics;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwMailer\Statistics\LinkStatistics;
use RKW\RkwMailer\Domain\Repository\QueueMailRepository;
use RKW\RkwMailer\Domain\Repository\QueueRecipientRepository;
use RKW\RkwMailer\Domain\Repository\LinkRepository;
use RKW\RkwMailer\Domain\Repository\StatisticOpeningRepository;

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

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Pages.xml');
        $this->importDataSet(self::FIXTURE_PATH . '/Database/QueueMail.xml');
        $this->importDataSet(self::FIXTURE_PATH . '/Database/QueueRecipient.xml');
        $this->importDataSet(self::FIXTURE_PATH . '//Database/Link.xml');
        $this->importDataSet(self::FIXTURE_PATH . '/Database/StatisticOpening.xml');

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
    public function getRedirectLink_GivenValidMailAndGivenInvalidHash_ReturnsFalse()
    {
        
        
        static::assertFalse($this->subject->getRedirectLink('abc', 1));
    }

    /**
     * @test
     */
    public function getRedirectLink_GivenInValidMailAndGivenValidHash_ReturnsLinkAndCreatesNoStatistic()
    {
        static::assertEquals(
            'http://aprodi-projekt.de',
            $this->subject->getRedirectLink('48723b1aa49952c291e71078d6690caabd1370ae', 99999)
        );
        static::assertEquals(
            self::NUMBER_OF_STATISTIC_OPENINGS,
            $this->statisticOpeningRepository->findAll()->count()
        );
    }


    /**
     * @test
     */
    public function getRedirectLink_GivenValidMailAndGivenValidNonMatchingHash_ReturnsLinkAndCreatesNoStatistic()
    {
        static::assertEquals(
            'http://aprodi-projekt.de',
            $this->subject->getRedirectLink('48723b1aa49952c291e71078d6690caabd1370ae', 1)
        );
        static::assertEquals(
            self::NUMBER_OF_STATISTIC_OPENINGS,
            $this->statisticOpeningRepository->findAll()->count()
        );

    }

    /**
     * @test
     */
    public function getRedirectLink_GivenValidMailAndGivenValidNonMatchingHash_ReturnsLinkWithAnchorAndCreatesNoStatistic()
    {
        static::assertEquals(
            'http://aprodi-projekt.de/#Anker-Link',
            $this->subject->getRedirectLink('bd18b69edccbc3a02b92e341e4cb72fc80ebf0c5', 1)
        );
        static::assertEquals(
            self::NUMBER_OF_STATISTIC_OPENINGS,
            $this->statisticOpeningRepository->findAll()->count()
        );

    }


    /**
     * @test
     */
    public function getRedirectLink_GivenValidMailAndGivenValidMatchingHashForUntrackedLink_ReturnsLinkAndCreatesStatisticWithCountOneForQueueMailGiven()
    {
        static::assertEquals(
            'http://aprodi-projekt.de?tx_rkwmailer[mid]=2',
            $this->subject->getRedirectLink('48723b1aa49952c291e71078d6690caabd1370ae', 2)
        );
        static::assertEquals(
            self::NUMBER_OF_STATISTIC_OPENINGS + 1,
            $this->statisticOpeningRepository->countAll()
        );

        /** @var  \RKW\RkwMailer\Domain\Model\StatisticOpening $statisticOpening */
        $statisticOpening = $this->statisticOpeningRepository->findByUid(self::NUMBER_OF_STATISTIC_OPENINGS + 1);
        static::assertEquals(
            1,
            $statisticOpening->getClickCount()
        );
        static::assertEquals(
            2,
            $statisticOpening->getQueueMail()->getUid()
        );

    }

    /**
     * @test
     */
    public function getRedirectLink_GivenValidMailAndGivenValidMatchingHashForUntrackedLink_ReturnsLinkWithAnchorAndCreatesStatisticWithCountOneForQueueMailGiven()
    {
        static::assertEquals(
            'http://aprodi-projekt.de/?tx_rkwmailer[mid]=2#Anker-Link',
            $this->subject->getRedirectLink('bd18b69edccbc3a02b92e341e4cb72fc80ebf0c5', 2)
        );
        static::assertEquals(
            self::NUMBER_OF_STATISTIC_OPENINGS + 1,
            $this->statisticOpeningRepository->countAll()
        );

        /** @var  \RKW\RkwMailer\Domain\Model\StatisticOpening $statisticOpening */
        $statisticOpening = $this->statisticOpeningRepository->findByUid(self::NUMBER_OF_STATISTIC_OPENINGS + 1);
        static::assertEquals(
            1,
            $statisticOpening->getClickCount()
        );
        static::assertEquals(
            2,
            $statisticOpening->getQueueMail()->getUid()
        );

    }


    /**
     * @test
     */
    public function getRedirect_GivenLinkWithValidMailAndGivenValidMatchingHashForTrackedLink_ReturnsLinkAndUpdatesStatisticWithCountTwoForQueueMailGiven()
    {
        static::assertEquals(
            'http://aprodi-projekt.de/test?tx_rkwmailer[mid]=2',
            $this->subject->getRedirectLink('cc217a5c99c6bade038ca01bbeb21aa62c65477f', 2)
        );
        static::assertEquals(
            self::NUMBER_OF_STATISTIC_OPENINGS,
            $this->statisticOpeningRepository->countAll()
        );
        /** @var  \RKW\RkwMailer\Domain\Model\StatisticOpening $statisticOpening */
        $statisticOpening = $this->statisticOpeningRepository->findByUid(2);
        static::assertEquals(
            2,
            $statisticOpening->getClickCount()
        );
        static::assertEquals(
            2,
            $statisticOpening->getQueueMail()->getUid()
        );
    }


    /**
     * @test
     */
    public function getRedirectLink_GivenValidMailAndGivenValidRecipientAndGivenValidNonMatchingHash_ReturnsLinkAndCreatesNoStatistic()
    {
        static::assertEquals(
            'http://aprodi-projekt.de',
            $this->subject->getRedirectLink('48723b1aa49952c291e71078d6690caabd1370ae', 1, 1)
        );
        static::assertEquals(
            self::NUMBER_OF_STATISTIC_OPENINGS,
            $this->statisticOpeningRepository->findAll()->count()
        );

    }

    /**
     * @test
     */
    public function getRedirectLink_GivenValidMailAndGivenValidRecipientAndGivenValidNonMatchingHash_ReturnsLinkWithAnchorAndCreatesNoStatistic()
    {
        static::assertEquals(
            'http://aprodi-projekt.de/#Anker-Link',
            $this->subject->getRedirectLink('bd18b69edccbc3a02b92e341e4cb72fc80ebf0c5', 1, 1)
        );
        static::assertEquals(
            self::NUMBER_OF_STATISTIC_OPENINGS,
            $this->statisticOpeningRepository->findAll()->count()
        );

    }


    /**
     * @test
     */
    public function getRedirectLink_GivenValidMailAndGivenValidRecipientAndGivenValidMatchingHashForUntrackedLink_ReturnsLinkAndCreatesStatisticWithCountOne()
    {
        static::assertEquals(
            'http://aprodi-projekt.de?tx_rkwmailer[mid]=2&tx_rkwmailer[uid]=1',
            $this->subject->getRedirectLink('48723b1aa49952c291e71078d6690caabd1370ae', 2, 1)
        );
        static::assertEquals(
            self::NUMBER_OF_STATISTIC_OPENINGS + 1,
            $this->statisticOpeningRepository->findAll()->count()
        );
        static::assertEquals(
            1,
            $this->statisticOpeningRepository->findByUid(self::NUMBER_OF_STATISTIC_OPENINGS + 1)->getClickCount()
        );

    }

    /**
     * @test
     */
    public function getRedirectLink_GivenValidMailAndGivenValidRecipientAndGivenValidMatchingHashForUntrackedLink_ReturnsLinkWithAnchorAndCreatesStatisticWithCountOneForQueueMailGiven()
    {
        static::assertEquals(
            'http://aprodi-projekt.de/?tx_rkwmailer[mid]=2&tx_rkwmailer[uid]=1#Anker-Link',
            $this->subject->getRedirectLink('bd18b69edccbc3a02b92e341e4cb72fc80ebf0c5', 2,1)
        );
        static::assertEquals(
            self::NUMBER_OF_STATISTIC_OPENINGS +1,
            $this->statisticOpeningRepository->countAll()
        );

        /** @var  \RKW\RkwMailer\Domain\Model\StatisticOpening $statisticOpening */
        $statisticOpening = $this->statisticOpeningRepository->findByUid(self::NUMBER_OF_STATISTIC_OPENINGS + 1);
        static::assertEquals(
            1,
            $statisticOpening->getClickCount()
        );
        static::assertEquals(
            2,
            $statisticOpening->getQueueMail()->getUid()
        );

    }


    /**
     * @test
     */
    public function getRedirectLink_GivenValidMailAndGivenValidRecipientAndGivenValidMatchingHashForTrackedLink_ReturnsLinkAndUpdatesStatisticWithCountTwoAndLeavesMailStatisticUnchanged()
    {
        static::assertEquals(
            'http://aprodi-projekt.de/test?tx_rkwmailer[mid]=2&tx_rkwmailer[uid]=1',
            $this->subject->getRedirectLink('cc217a5c99c6bade038ca01bbeb21aa62c65477f', 2,1 )
        );
        static::assertEquals(
            self::NUMBER_OF_STATISTIC_OPENINGS,
            $this->statisticOpeningRepository->findAll()->count()
        );
        static::assertEquals(
            2,
            $this->statisticOpeningRepository->findByUid(3)->getClickCount()
        );
        static::assertEquals(
            1,
            $this->statisticOpeningRepository->findByUid(2)->getClickCount()
        );

    }


    /**
     * @test
     */
    public function getRedirectLink_GivenValidMailAndGivenInValidRecipientAndGivenValidMatchingHashForTrackedLink_ReturnsLinkAndUpdatesStatisticWithCountTwoAndLeavesRecipientStatisticUnchanged()
    {
        static::assertEquals(
            'http://aprodi-projekt.de/test?tx_rkwmailer[mid]=2',
            $this->subject->getRedirectLink('cc217a5c99c6bade038ca01bbeb21aa62c65477f', 2,2 )
        );
        static::assertEquals(
            self::NUMBER_OF_STATISTIC_OPENINGS,
            $this->statisticOpeningRepository->findAll()->count()
        );
        static::assertEquals(
            1,
            $this->statisticOpeningRepository->findByUid(3)->getClickCount()
        );
        static::assertEquals(
            2,
            $this->statisticOpeningRepository->findByUid(2)->getClickCount()
        );

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