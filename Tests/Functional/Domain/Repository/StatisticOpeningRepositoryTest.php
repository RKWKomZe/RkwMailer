<?php
namespace RKW\RkwMailer\Tests\Functional\Domain\Repository;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwMailer\Domain\Repository\StatisticOpeningRepository;
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
 * StatisticOpeningRepositoryTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class StatisticOpeningRepositoryTest extends FunctionalTestCase
{
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
     * @var \RKW\RkwMailer\Domain\Repository\StatisticOpeningRepository
     */
    private $subject = null;

    /**
     * @var \RKW\RkwMailer\Domain\Repository\QueueMailRepository
     */
    private $queueMailRepository;

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
        $this->importDataSet(__DIR__ . '/Fixtures/Database/StatisticOpeningRepository/Pages.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/StatisticOpeningRepository/QueueMail.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/StatisticOpeningRepository/StatisticOpening.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/StatisticOpeningRepository/Link.xml');


        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Tests/Functional/Utility/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );
        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->queueMailRepository = $this->objectManager->get(QueueMailRepository::class);
        $this->subject = $this->objectManager->get(StatisticOpeningRepository::class);
    }


    /**
     * @test
     */
    public function findAllWithStatisticsIgnoresMailOpeningPixelAndClicksFromOtherQueueMails()
    {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(1);

        $result = $this->subject->findAllWithStatistics($queueMail);
        static::assertEquals(3, $result[0]['clicked']);

        /*;
        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $objectOne
        $objectOne = $result[0];

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $objectTwo
        $objectTwo = $result[1];

        static::assertEquals(2, count($result));
        static::assertEquals(8, $object);
        static::assertEquals(9, $objectTwo->getUid());
        */

    }


    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}