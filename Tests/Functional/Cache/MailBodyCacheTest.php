<?php
namespace RKW\RkwMailer\Tests\Functional\Utility;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwMailer\Cache\MailBodyCache;
use RKW\RkwMailer\Domain\Repository\QueueMailRepository;
use RKW\RkwMailer\Domain\Repository\QueueRecipientRepository;

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
 * StatisticsUtilityTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MailBodyCacheTest extends FunctionalTestCase
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
     * @var \RKW\RkwMailer\Cache\MailBodyCache
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

        $this->importDataSet(__DIR__ . '/Fixtures/Database/Pages.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/QueueMail.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/QueueRecipient.xml');

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
        $this->queueRecipientRepository = $this->objectManager->get(QueueRecipientRepository::class);

        $this->subject = $this->objectManager->get(MailBodyCache::class);

    }


    /**
     * @test
     */
    public function setPlaintextBodyAndGetPlaintextBody_UsingSameQueueRecipient_ReturnsGivenString()
    {
        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findbyUid(1);
        $this->subject->setPlaintextBody($queueRecipient, 'Abc');
        
        static::assertEquals('Abc', $this->subject->getPlaintextBody($queueRecipient));
    }


    /**
     * @test
     */
    public function setPlaintextBodyAndGetPlaintextBody_UsingAnotherQueueRecipient_ReturnsEmptyString()
    {
        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findbyUid(1);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipientTwo */
        $queueRecipientTwo = $this->queueRecipientRepository->findbyUid(2);
        $this->subject->setPlaintextBody($queueRecipient, 'Abc');

        static::assertEmpty($this->subject->getPlaintextBody($queueRecipientTwo));
    }

    //=============================================

    /**
     * @test
     */
    public function setHtmlBodyAndGetHtmlBody_UsingSameQueueRecipient_ReturnsGivenString()
    {
        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findbyUid(1);
        $this->subject->setHtmlBody($queueRecipient, 'Abc');

        static::assertEquals('Abc', $this->subject->getHtmlBody($queueRecipient));
    }


    /**
     * @test
     */
    public function setHtmlBodyAndGetHtmlBody_UsingAnotherQueueRecipient_ReturnsEmptyString()
    {
        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findbyUid(1);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipientTwo */
        $queueRecipientTwo = $this->queueRecipientRepository->findbyUid(2);
        $this->subject->setHtmlBody($queueRecipient, 'Abc');

        static::assertEmpty($this->subject->getHtmlBody($queueRecipientTwo));
    }

    //=============================================

    /**
     * @test
     */
    public function setCalendarBodyAndGetCalendarBody_UsingSameQueueRecipient_ReturnsGivenString()
    {
        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findbyUid(1);
        $this->subject->setCalendarBody($queueRecipient, 'Abc');

        static::assertEquals('Abc', $this->subject->getCalendarBody($queueRecipient));
    }


    /**
     * @test
     */
    public function setCalendarBodyAndGetCalendarBody_UsingAnotherQueueRecipient_ReturnsEmptyString()
    {
        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findbyUid(1);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipientTwo */
        $queueRecipientTwo = $this->queueRecipientRepository->findbyUid(2);
        $this->subject->setCalendarBody($queueRecipient, 'Abc');

        static::assertEmpty($this->subject->getCalendarBody($queueRecipientTwo));
    }

    //=============================================

    /**
     * TearDown
     */
    protected function tearDown()
    {

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

        /** @var \RKW\RkwMailer\Cache\MailBodyCache $mailBodyCache */
        $mailBodyCache = $objectManager->get('RKW\\RkwMailer\\Cache\\MailBodyCache');
        $mailBodyCache->clearCache();

        parent::tearDown();
    }








}