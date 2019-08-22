<?php
namespace RKW\RkwMailer\Tests\Functional\Domain\Repository;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;
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
 * QueueMailRepositoryTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class QueueMailRepositoryTest extends FunctionalTestCase
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
     * @var \RKW\RkwMailer\Domain\Repository\QueueMailRepository
     */
    private $subject = null;
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
        $this->importDataSet(__DIR__ . '/Fixtures/Database/QueueMailRepository/Pages.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/QueueMailRepository/Link.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/QueueMailRepository/QueueMail.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/QueueMailRepository/QueueRecipient.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/QueueMailRepository/StatisticOpening.xml');


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
        $this->subject = $this->objectManager->get(QueueMailRepository::class);
    }


    /**
     * @test
     */
    public function findAllSentWithStatistics_GivenNothing_ReturnsExpectedStatisticValues()
    {

        $result = $this->subject->findAllSentOrSendingWithStatistics();

        self::assertCount(2, $result->toArray());

        $resultAsArray = $result->toArray();
        self::assertEquals('2', $resultAsArray[1]->getUid());
        self::assertEquals('9', $resultAsArray[1]->getTotal());
        self::assertEquals('5', $resultAsArray[1]->getSent());
        self::assertEquals('2', $resultAsArray[1]->getSuccessful());
        self::assertEquals('1', $resultAsArray[1]->getFailed());
        self::assertEquals('1', $resultAsArray[1]->getDeferred());
        self::assertEquals('2', $resultAsArray[1]->getBounced());
        self::assertEquals('2', $resultAsArray[1]->getOpened());
        self::assertEquals('1', $resultAsArray[1]->getClicked());

    }


    /**
     * @test
     */
    public function findAllSentWithStatistics_GivenFromValue_ReturnsFilteredQueueMails()
    {

        $result = $this->subject->findAllSentOrSendingWithStatistics(1001);
        self::assertCount(1, $result->toArray());
        self::assertEquals(3, $result->getFirst()->getUid());
    }

    /**
     * @test
     */
    public function findAllSentWithStatistics_GivenToValue_ReturnsFilteredQueueMails()
    {

        $result = $this->subject->findAllSentOrSendingWithStatistics(0, 1999);
        self::assertCount(1, $result->toArray());
        self::assertEquals(2, $result->getFirst()->getUid());
    }


    /**
     * @test
     */
    public function findAllSentWithStatistics_GivenType_ReturnsFilteredQueueMails()
    {

        $result = $this->subject->findAllSentOrSendingWithStatistics(0, 0, 1);
        self::assertCount(1, $result->toArray());
        self::assertEquals(3, $result->getFirst()->getUid());

    }

    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}