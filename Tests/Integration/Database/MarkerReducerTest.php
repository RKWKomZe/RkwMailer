<?php
namespace RKW\RkwMailer\Tests\Integration\Database;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwAjax\Helper\AjaxHelper;
use RKW\RkwMailer\Database\MarkerReducer;
use RKW\RkwMailer\Domain\Repository\QueueMailRepository;
use RKW\RkwMailer\View\MailStandaloneView;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;


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
 * MarkerReducerTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MarkerReducerTest extends FunctionalTestCase
{

    const FIXTURE_PATH = __DIR__ . '/MarkerReducerTest/Fixtures';

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
     * @var \RKW\RkwMailer\Database\MarkerReducer
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
                'EXT:rkw_mailer/Tests/Integration/View/MarkerReducerTest/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $this->objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->subject = $this->objectManager->get(MarkerReducer::class);
        $this->queueMailRepository = $this->objectManager->get(QueueMailRepository::class);

    }



    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function implodeMarkerUsingPersistedObjectsReturnsCompletelyReducedArray()
    {

        /**
         * Scenario:
         *
         * Given a marker array contains four items
         * Given three items are are persisted objects in the database
         * Given the fourth item is an objectStorage with two objects
         * Given these two objects are persisted in the database
         * When the method is called
         * Then the marker array returns four items
         * Then the first three items are reduced to object-placeholders consisting of namespace and the uid
         * Then the fourth item is reduced to an array-placeholder with a comma-separated list of object-placeholders consisting of namespace and the uid
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $objectOne = $this->queueMailRepository->findByIdentifier(1);
        $objectTwo = $this->queueMailRepository->findByIdentifier(2);
        $objectThree = $this->queueMailRepository->findByIdentifier(3);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = $this->objectManager->get(ObjectStorage::class);
        $objectStorage->attach($objectTwo);
        $objectStorage->attach($objectThree);

        $marker = [
            'test1' => $objectOne,
            'test2' => $objectTwo,
            'test3' => $objectThree,
            'test4' => $objectStorage,
        ];

        $expected = [
            'test1' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:1',
            'test2' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:2',
            'test3' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:3',
            'test4' => 'RKW_MAILER_NAMESPACES_ARRAY RKW\RkwMailer\Domain\Model\QueueMail:2,RKW\RkwMailer\Domain\Model\QueueMail:3',
        ];

        $result = $this->subject->implodeMarker($marker);

        static::assertCount(4, $expected);
        static::assertEquals($expected['test1'], $result['test1']);
        static::assertEquals($expected['test1'], $result['test1']);
        static::assertEquals($expected['test3'], $result['test3']);
        static::assertEquals($expected['test4'], $result['test4']);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function implodeMarkerUsingMixedObjectsInObjectStorageLeavesObjectStorageUntouched()
    {

        /**
         * Scenario:
         *
         * Given a marker array contains four items
         * Given the first and third item are persisted objects in the database
         * Given the second of the objects is not persisted in the database
         * Given the fourth item is an objectStorage with two objects
         * Given the first of the objects is persisted in the database
         * Given the second of the objects is not persisted in the database
         * When the method is called
         * Then the marker array returns four items
         * Then the first and the third item are reduced to object-placeholders consisting of namespace and the uid
         * Then the second item contains the complete non-persisted object
         * Then the fourth item contains the object-storage with the complete objects
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $abstractEntityOne = $this->queueMailRepository->findByIdentifier(1);
        $abstractEntityTwo = GeneralUtility::makeInstance(\RKW\RkwMailer\Domain\Model\QueueRecipient::class);
        $abstractEntityThree = $this->queueMailRepository->findByIdentifier(3);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = $this->objectManager->get(ObjectStorage::class);
        $objectStorage->attach($abstractEntityOne);
        $objectStorage->attach($abstractEntityTwo);

        $marker = [
            'test1' => $abstractEntityOne,
            'test2' => $abstractEntityTwo,
            'test3' => $abstractEntityThree,
            'test4' => $objectStorage,
        ];

        $expected = [
            'test1' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:1',
            'test2' => $abstractEntityTwo,
            'test3' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:3',
            'test4' => $objectStorage,
        ];

        $result = $this->subject->implodeMarker($marker);

        static::assertCount(4, $expected);
        static::assertEquals($expected['test1'], $result['test1']);
        static::assertEquals($expected['test1'], $result['test1']);
        static::assertEquals($expected['test3'], $result['test3']);
        static::assertEquals($expected['test4'], $result['test4']);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function implodeMarkerLeavesNonObjectsUntouched()
    {

        /**
         * Scenario:
         *
         * Given a marker array contains three items
         * Given the first item is a persisted object in the database
         * Given the second item is an array
         * Given the third item is a simple string
         * When the method is called
         * Then the marker array returns three items
         * Then the first and the third item are reduced to object-placeholders consisting of namespace and the uid
         * Then the second item contains the complete non-persisted object
         * Then the fourth item contains the object-storage with the complete objects
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $abstractEntityOne = $this->queueMailRepository->findByIdentifier(1);

        $marker = [
            'test1' => $abstractEntityOne,
            'test2' => [0, 12],
            'test3' => 'example string',
        ];

        $expected = [
            'test1' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:1',
            'test2' => [0, 12],
            'test3' => 'example string',
        ];

        $result = $this->subject->implodeMarker($marker);

        static::assertCount(3, $expected);
        static::assertEquals($expected['test1'], $result['test1']);
        static::assertEquals($expected['test1'], $result['test1']);
        static::assertEquals($expected['test3'], $result['test3']);
    }



    //=============================================


    /**
     * @test
     * @throws \Exception
     */
    public function explodeMarkerConsistingOfPersistedObjectsOnlyReturnsCompleteObjectArray()
    {

        /**
         * Scenario:
         *
         * Given a marker array contains four items
         * Given three of the items are references to existing objects in the database
         * Given the fourth item is a reference to an objectStorage with two objects
         * Given these two objects are references to existing objects in the database
         * When the method is called
         * Then the marker array returns four items
         * Then the first three items are reduced to object-placeholders consisting of namespace and the uid
         * Then the fourth item is reduced to an array-placeholder with a comma-separated list of object-placeholders consisting of namespace and the uid
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $abstractEntityOne = $this->queueMailRepository->findByIdentifier(1);
        $abstractEntityTwo = $this->queueMailRepository->findByIdentifier(2);
        $abstractEntityThree = $this->queueMailRepository->findByIdentifier(3);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = $this->objectManager->get(ObjectStorage::class);
        $objectStorage->attach($abstractEntityTwo);
        $objectStorage->attach($abstractEntityThree);

        $marker = [
            'test1' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:1',
            'test2' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:2',
            'test3' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:3',
            'test4' => 'RKW_MAILER_NAMESPACES_ARRAY RKW\RkwMailer\Domain\Model\QueueMail:2,RKW\RkwMailer\Domain\Model\QueueMail:3',
        ];

        $expected = [
            'test1' => $abstractEntityOne,
            'test2' => $abstractEntityTwo,
            'test3' => $abstractEntityThree,
            'test4' => $objectStorage,
        ];

        $result = $this->subject->explodeMarker($marker);

        static::assertCount(4, $expected);
        static::assertEquals($expected['test1'], $result['test1']);
        static::assertEquals($expected['test1'], $result['test1']);
        static::assertEquals($expected['test3'], $result['test3']);

        /** @var ObjectStorage $resultStorage */
        $resultStorage = $result['test4'];
        static::assertInstanceOf(ObjectStorage::class, $resultStorage);
        static::assertCount(2, $resultStorage);

        static::assertEquals($abstractEntityTwo, $resultStorage->current());
        $resultStorage->next();
        static::assertEquals($abstractEntityThree, $resultStorage->current());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function explodeMarkerUsingNonExistingObjectsReturnsReducedObjectArray()
    {

        /**
         * Scenario:
         *
         * Given a marker array contains four items
         * Given the first two of the items are references to existing objects in the database
         * Given the third item is a reference to non-existing object in the database
         * Given the fourth item is a reference to an objectStorage with two objects
         * Given the first object is a reference to existing object in the database
         * Given the second object is a reference to non-existing object in the database
         * When the method is called
         * Then the marker array returns three items
         * Then the first two items contain the existing objects
         * Then the non-existing object is missing
         * Then the third item is an object storage with one element
         * Then the first item of the object storage contains the existing object
         * Then the non-existing object in the object storage is missing
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $abstractEntityOne = $this->queueMailRepository->findByIdentifier(1);
        $abstractEntityTwo = $this->queueMailRepository->findByIdentifier(2);
        $abstractEntityThree = $this->queueMailRepository->findByIdentifier(3);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = $this->objectManager->get(ObjectStorage::class);
        $objectStorage->attach($abstractEntityThree);

        $marker = [
            'test1' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:1',
            'test2' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:2',
            'test3' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:9999',
            'test4' => 'RKW_MAILER_NAMESPACES_ARRAY RKW\RkwMailer\Domain\Model\QueueMail:3,RKW\RkwMailer\Domain\Model\QueueMail:9999',
        ];

        $expected = [
            'test1' => $abstractEntityOne,
            'test2' => $abstractEntityTwo,
            'test4' => $objectStorage,
        ];

        $result = $this->subject->explodeMarker($marker);

        static::assertCount(3, $expected);
        static::assertEquals($expected['test1'], $result['test1']);
        static::assertEquals($expected['test2'], $result['test2']);

        /** @var ObjectStorage $resultStorage */
        $resultStorage = $result['test4'];
        static::assertInstanceOf(ObjectStorage::class, $resultStorage);
        static::assertCount(1, $resultStorage);

        static::assertEquals($abstractEntityThree, $resultStorage->current());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function explodeMarkerUsingOnlyNonExistingObjectsInObjectStorageReturnsEmptyObjectStorage()
    {

        /**
         * Scenario:
         *
         * Given a marker array contains four items
         * Given the first two of the items are references to existing objects in the database
         * Given the third item is a reference to non-existing object in the database
         * Given the fourth item is a reference to an objectStorage with two objects
         * Given the two objects are a reference to non-existing objects in the database
         * When the method is called
         * Then the marker array returns three items
         * Then the first two items contain the existing objects
         * Then the non-existing object is missing
         * Then the third item is an object storage with no elements
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $abstractEntityOne = $this->queueMailRepository->findByIdentifier(1);
        $abstractEntityTwo = $this->queueMailRepository->findByIdentifier(2);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = $this->objectManager->get(ObjectStorage::class);

        $expected = [
            'test1' => $abstractEntityOne,
            'test2' => $abstractEntityTwo,
            'test4' => $objectStorage,
        ];

        $marker = [
            'test1' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:1',
            'test2' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:2',
            'test3' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:9999',
            'test4' => 'RKW_MAILER_NAMESPACES_ARRAY RKW\RkwMailer\Domain\Model\QueueMail:8888,RKW\RkwMailer\Domain\Model\QueueMail:9999',
        ];

        $result = $this->subject->explodeMarker($marker);

        static::assertCount(3, $expected);
        static::assertEquals($expected['test1'], $result['test1']);
        static::assertEquals($expected['test2'], $result['test2']);

        /** @var ObjectStorage $resultStorage */
        $resultStorage = $result['test4'];
        static::assertInstanceOf(ObjectStorage::class, $resultStorage);
        static::assertCount(0, $resultStorage);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function explodeMarkerLeavesItemsWithoutKeywordsUntouched()
    {

        /**
         * Scenario:
         *
         * Given a marker array contains four items
         * Given the first two of the items are references to existing objects in the database
         * Given the third item is a reference to an existing object in the database, but with wrong keyword-prefix
         * Given the fourth item is a reference to an objectStorage with two existing object in the database, but with wrong keyword-prefix
         * When the method is called
         * Then the marker array returns four items
         * Then the first two items contain the existing objects
         * Then the third item is returned as untouched string
         * Then the fourth item is is returned as untouched string
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $abstractEntityOne = $this->queueMailRepository->findByIdentifier(1);
        $abstractEntityTwo = $this->queueMailRepository->findByIdentifier(2);

        $expected = [
            'test1' => $abstractEntityOne,
            'test2' => $abstractEntityTwo,
            'test3' => 'RKW_TESTER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:3',
            'test4' => 'RKW_TESTER_NAMESPACES_ARRAY RKW\RkwMailer\Domain\Model\QueueMail:8888,RKW\RkwMailer\Domain\Model\QueueMail:9999',

        ];

        $marker = [
            'test1' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:1',
            'test2' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:2',
            'test3' => 'RKW_TESTER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:3',
            'test4' => 'RKW_TESTER_NAMESPACES_ARRAY RKW\RkwMailer\Domain\Model\QueueMail:8888,RKW\RkwMailer\Domain\Model\QueueMail:9999',
        ];


        $result = $this->subject->explodeMarker($marker);

        static::assertCount(4, $expected);
        static::assertEquals($expected['test1'], $result['test1']);
        static::assertEquals($expected['test2'], $result['test2']);
        static::assertEquals($expected['test3'], $result['test3']);
        static::assertEquals($expected['test4'], $result['test4']);

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