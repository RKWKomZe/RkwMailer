<?php
namespace RKW\RkwMailer\Tests\Integration\Cache;

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
use RKW\RkwMailer\Cache\MailBodyCache;
use RKW\RkwMailer\Domain\Model\QueueRecipient;
use RKW\RkwMailer\Domain\Repository\QueueMailRepository;
use RKW\RkwMailer\Domain\Repository\QueueRecipientRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * MailBodyTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MailBodyTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/MailBodyCacheTest/Fixtures';

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
     * @var \RKW\RkwMailer\Cache\MailBodyCache
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
        $this->subject = $this->objectManager->get(MailBodyCache::class);
        $this->subject->clearCache();

    }
    

    //=============================================


    /**
     * @test
     * @throws \Exception
     */
    public function setPlaintextBodyUsingNonPersistentQueueRecipientThrowsException()
    {

        /**
         * Scenario:
         *
         * Given a non-persisted queueRecipient-object
         * When the method is called
         * Then an exception is thrown
         * Then the code of the exception is 1634308452
         */
        
        static::expectException(\RKW\RkwMailer\Exception::class);
        static::expectExceptionCode(1634308452);
        
        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(QueueRecipient::class);
        $this->subject->setPlaintextBody($queueRecipient, 'Abc');

    }
    
    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getPlaintextBodyUsingNonPersistentQueueRecipientThrowsException()
    {

        /**
         * Scenario:
         *
         * Given a non-persisted queueRecipient-object
         * When the method is called
         * Then an exception is thrown
         * Then the code of the exception is 1634308452
         */

        static::expectException(\RKW\RkwMailer\Exception::class);
        static::expectExceptionCode(1634308452);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(QueueRecipient::class);
        $this->subject->getPlaintextBody($queueRecipient);

    }
    
    /**
     * @test
     * @throws \Exception
     */
    public function getPlaintextBodyUsingSameQueueRecipientReturnsSameString()
    {

        /**
         * Scenario:
         *
         * Given a persisted queueRecipient-object
         * Given the cache for the queueRecipient-object has already been set
         * When method is called
         * Then the cached content is returned
         */
        
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');
        
        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findbyUid(10);
        $this->subject->setPlaintextBody($queueRecipient, 'Abc');

        static::assertEquals('Abc', $this->subject->getPlaintextBody($queueRecipient));

    }

    /**
     * @test
     * @throws \Exception
     */
    public function getPlaintextBodyUsingAnotherQueueRecipientReturnsAnotherString()
    {

        /**
         * Scenario:
         *
         * Given two persisted queueRecipient-objects
         * Given the cache for the first queueRecipient-object has already been set
         * Given the second queueRecipient-object as parameter
         * When method is called 
         * Then an empty string is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipientOne */
        $queueRecipientOne = $this->queueRecipientRepository->findbyUid(20);
        $this->subject->setPlaintextBody($queueRecipientOne, 'Abc');

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipientTwo */
        $queueRecipientTwo = $this->queueRecipientRepository->findbyUid(21);

        static::assertEmpty($this->subject->getPlaintextBody($queueRecipientTwo));

    }

    //=============================================


    /**
     * @test
     * @throws \Exception
     */
    public function setHtmlBodyUsingNonPersistentQueueRecipientThrowsException()
    {

        /**
         * Scenario:
         *
         * Given a non-persisted queueRecipient-object
         * When the method is called
         * Then an exception is thrown
         * Then the code of the exception is 1634308452
         */

        static::expectException(\RKW\RkwMailer\Exception::class);
        static::expectExceptionCode(1634308452);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(QueueRecipient::class);
        $this->subject->setHtmlBody($queueRecipient, 'Abc');

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getHtmlBodyUsingNonPersistentQueueRecipientThrowsException()
    {

        /**
         * Scenario:
         *
         * Given a non-persisted queueRecipient-object
         * When the method is called
         * Then an exception is thrown
         * Then the code of the exception is 1634308452
         */

        static::expectException(\RKW\RkwMailer\Exception::class);
        static::expectExceptionCode(1634308452);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(QueueRecipient::class);
        $this->subject->getHtmlBody($queueRecipient);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function getHtmlBodyUsingSameQueueRecipientReturnsSameString()
    {

        /**
         * Scenario:
         *
         * Given a persisted queueRecipient-object
         * Given the cache for the queueRecipient-object has already been set
         * When method is called
         * Then the cached content is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findbyUid(10);
        $this->subject->setHtmlBody($queueRecipient, 'Abc');

        static::assertEquals('Abc', $this->subject->getHtmlBody($queueRecipient));

    }

    /**
     * @test
     * @throws \Exception
     */
    public function getHtmlBodyUsingAnotherQueueRecipientReturnsAnotherString()
    {

        /**
         * Scenario:
         *
         * Given two persisted queueRecipient-objects
         * Given the cache for the first queueRecipient-object has already been set
         * Given the second queueRecipient-object as parameter
         * When method is called
         * Then an empty string is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipientOne */
        $queueRecipientOne = $this->queueRecipientRepository->findbyUid(20);
        $this->subject->setHtmlBody($queueRecipientOne, 'Abc');

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipientTwo */
        $queueRecipientTwo = $this->queueRecipientRepository->findbyUid(21);

        static::assertEmpty($this->subject->getHtmlBody($queueRecipientTwo));

    }
    
    //=============================================
    /**
     * @test
     * @throws \Exception
     */
    public function setCalendarBodyUsingNonPersistentQueueRecipientThrowsException()
    {

        /**
         * Scenario:
         *
         * Given a non-persisted queueRecipient-object
         * When the method is called
         * Then an exception is thrown
         * Then the code of the exception is 1634308452
         */

        static::expectException(\RKW\RkwMailer\Exception::class);
        static::expectExceptionCode(1634308452);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(QueueRecipient::class);
        $this->subject->setCalendarBody($queueRecipient, 'Abc');

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getCalendarBodyUsingNonPersistentQueueRecipientThrowsException()
    {

        /**
         * Scenario:
         *
         * Given a non-persisted queueRecipient-object
         * When the method is called
         * Then an exception is thrown
         * Then the code of the exception is 1634308452
         */

        static::expectException(\RKW\RkwMailer\Exception::class);
        static::expectExceptionCode(1634308452);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(QueueRecipient::class);
        $this->subject->getCalendarBody($queueRecipient);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function getCalendarBodyUsingSameQueueRecipientReturnsSameString()
    {

        /**
         * Scenario:
         *
         * Given a persisted queueRecipient-object
         * Given the cache for the queueRecipient-object has already been set
         * When method is called
         * Then the cached content is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findbyUid(10);
        $this->subject->setCalendarBody($queueRecipient, 'Abc');

        static::assertEquals('Abc', $this->subject->getCalendarBody($queueRecipient));

    }

    /**
     * @test
     * @throws \Exception
     */
    public function getCalendarBodyUsingAnotherQueueRecipientReturnsAnotherString()
    {

        /**
         * Scenario:
         *
         * Given two persisted queueRecipient-objects
         * Given the cache for the first queueRecipient-object has already been set
         * Given the second queueRecipient-object as parameter
         * When method is called
         * Then an empty string is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipientOne */
        $queueRecipientOne = $this->queueRecipientRepository->findbyUid(20);
        $this->subject->setCalendarBody($queueRecipientOne, 'Abc');

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipientTwo */
        $queueRecipientTwo = $this->queueRecipientRepository->findbyUid(21);

        static::assertEmpty($this->subject->getCalendarBody($queueRecipientTwo));

    }

    //=============================================
    /**
     * TearDown
     */
    protected function tearDown()
    {
        $this->subject->clearCache();
        parent::tearDown();
    }








}