<?php
namespace RKW\RkwMailer\Tests\Integration\Persistence;

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
use RKW\RkwMailer\Persistence\MarkerReducer;
use RKW\RkwMailer\Domain\Repository\QueueMailRepository;
use \RKW\RkwBasics\Domain\Repository\PagesRepository;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

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

    /**
     * @const
     */
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
     * @var \RKW\RkwMailer\Persistence\MarkerReducer
     */
    private $subject;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    private $persistenceManager;

    /**
     * @var \RKW\RkwBasics\Domain\Repository\PagesRepository
     */
    private $pagesRepository;


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
        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->subject = $this->objectManager->get(MarkerReducer::class);
        $this->pagesRepository = $this->objectManager->get(PagesRepository::class);
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

        /** @var \RKW\RkwBasics\Domain\Model\Pages $entityOne */
        $entityOne = $this->pagesRepository->findByIdentifier(1);

        /** @var \RKW\RkwBasics\Domain\Model\Pages $entityTwo */
        $entityTwo = $this->pagesRepository->findByIdentifier(2);

        /** @var \RKW\RkwBasics\Domain\Model\Pages $entityThree */
        $entityThree = $this->pagesRepository->findByIdentifier(3);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = $this->objectManager->get(ObjectStorage::class);
        $objectStorage->attach($entityTwo);
        $objectStorage->attach($entityThree);

        $marker = [
            'test1' => $entityOne,
            'test2' => $entityTwo,
            'test3' => $entityThree,
            'test4' => $objectStorage,
        ];

        $expected = [
            'test1' => 'RKW_MAILER_NAMESPACES RKW\RkwBasics\Domain\Model\Pages:1',
            'test2' => 'RKW_MAILER_NAMESPACES RKW\RkwBasics\Domain\Model\Pages:2',
            'test3' => 'RKW_MAILER_NAMESPACES RKW\RkwBasics\Domain\Model\Pages:3',
            'test4' => 'RKW_MAILER_NAMESPACES_ARRAY RKW\RkwBasics\Domain\Model\Pages:2,RKW\RkwBasics\Domain\Model\Pages:3',
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

        /** @var \RKW\RkwBasics\Domain\Model\Pages $entityOne */
        $entityOne = $this->pagesRepository->findByIdentifier(1);

        /** @var \RKW\RkwBasics\Domain\Model\Pages $entityTwo */
        $entityTwo = GeneralUtility::makeInstance(Pages::class);

        /** @var \RKW\RkwBasics\Domain\Model\Pages $entityThree */
        $entityThree = $this->pagesRepository->findByIdentifier(3);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = $this->objectManager->get(ObjectStorage::class);
        $objectStorage->attach($entityOne);
        $objectStorage->attach($entityTwo);

        $marker = [
            'test1' => $entityOne,
            'test2' => $entityTwo,
            'test3' => $entityThree,
            'test4' => $objectStorage,
        ];

        $expected = [
            'test1' => 'RKW_MAILER_NAMESPACES RKW\RkwBasics\Domain\Model\Pages:1',
            'test2' => $entityTwo,
            'test3' => 'RKW_MAILER_NAMESPACES RKW\RkwBasics\Domain\Model\Pages:3',
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

        /** @var \RKW\RkwBasics\Domain\Model\Pages $entityOne */
        $entityOne = $this->pagesRepository->findByIdentifier(1);

        $marker = [
            'test1' => $entityOne,
            'test2' => [0, 12],
            'test3' => 'example string',
        ];

        $expected = [
            'test1' => 'RKW_MAILER_NAMESPACES RKW\RkwBasics\Domain\Model\Pages:1',
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
    public function explodeMarkerConsistingOfPersistedObjectsReturnsCompleteObjectArray()
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
         * Then the first three items are contain objects of the given type
         * Then the first three items are contain the objects identified by the given uid
         * Then the fourth item contains an array with two objects of the given type 
         * Then the two objects of the fourth item are the objects identified by the given uid
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwBasics\Domain\Model\Pages $entityOne */
        $entityOne = $this->pagesRepository->findByIdentifier(1);

        /** @var \RKW\RkwBasics\Domain\Model\Pages $entityTwo */
        $entityTwo = $this->pagesRepository->findByIdentifier(2);

        /** @var \RKW\RkwBasics\Domain\Model\Pages $entityThree */
        $entityThree = $this->pagesRepository->findByIdentifier(3);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = $this->objectManager->get(ObjectStorage::class);
        $objectStorage->attach($entityTwo);
        $objectStorage->attach($entityThree);

        $marker = [
            'test1' => 'RKW_MAILER_NAMESPACES RKW\RkwBasics\Domain\Model\Pages:1',
            'test2' => 'RKW_MAILER_NAMESPACES RKW\RkwBasics\Domain\Model\Pages:2',
            'test3' => 'RKW_MAILER_NAMESPACES RKW\RkwBasics\Domain\Model\Pages:3',
            'test4' => 'RKW_MAILER_NAMESPACES_ARRAY RKW\RkwBasics\Domain\Model\Pages:2,RKW\RkwBasics\Domain\Model\Pages:3',
        ];

        $expected = [
            'test1' => $entityOne,
            'test2' => $entityTwo,
            'test3' => $entityThree,
            'test4' => $objectStorage,
        ];

        $result = $this->subject->explodeMarker($marker);

        static::assertCount(4, $expected);
        static::assertInstanceOf(Pages::class, $result['test1']);
        static::assertInstanceOf(Pages::class, $result['test2']);
        static::assertInstanceOf(Pages::class, $result['test3']);
        static::assertEquals($expected['test1']->getUid(), $result['test1']->getUid());
        static::assertEquals($expected['test2']->getUid(), $result['test2']->getUid());
        static::assertEquals($expected['test3']->getUid(), $result['test3']->getUid());

        /** @var ObjectStorage $resultStorage */
        $resultStorage = $result['test4'];
        static::assertInstanceOf(ObjectStorage::class, $resultStorage);
        static::assertCount(2, $resultStorage);

        static::assertInstanceOf(Pages::class, $resultStorage->current());
        static::assertEquals($entityTwo->getUid(), $resultStorage->current()->getUid());
        $resultStorage->next();
        static::assertInstanceOf(Pages::class, $resultStorage->current());
        static::assertEquals($entityThree->getUid(), $resultStorage->current()->getUid());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function explodeMarkerConsistingOfPersistedButDeletedObjectsReturnsCompleteObjectArray()
    {

        /**
         * Scenario:
         *
         * Given a marker array contains four items
         * Given three of the items are references to existing but deleted objects in the database
         * Given the fourth item is a reference to an objectStorage with two objects
         * Given these two objects are references to existing but deleted objects in the database
         * When the method is called
         * Then the marker array returns four items
         * Then the first three items are contain objects of the given type
         * Then the first three items are contain the objects identified by the given uid
         * Then the fourth item contains an array with two objects of the given type 
         * Then the two objects of the fourth item are the objects identified by the given uid
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwBasics\Domain\Model\Pages $entityOne */
        $entityOne = $this->pagesRepository->findByIdentifier(1);
        $this->pagesRepository->remove($entityOne);

        /** @var \RKW\RkwBasics\Domain\Model\Pages $entityTwo */
        $entityTwo = $this->pagesRepository->findByIdentifier(2);
        $this->pagesRepository->remove($entityTwo);

        /** @var \RKW\RkwBasics\Domain\Model\Pages $entityThree */
        $entityThree = $this->pagesRepository->findByIdentifier(3);
        $this->pagesRepository->remove($entityThree);
        $this->persistenceManager->persistAll();

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = $this->objectManager->get(ObjectStorage::class);
        $objectStorage->attach($entityTwo);
        $objectStorage->attach($entityThree);

        $marker = [
            'test1' => 'RKW_MAILER_NAMESPACES RKW\RkwBasics\Domain\Model\Pages:1',
            'test2' => 'RKW_MAILER_NAMESPACES RKW\RkwBasics\Domain\Model\Pages:2',
            'test3' => 'RKW_MAILER_NAMESPACES RKW\RkwBasics\Domain\Model\Pages:3',
            'test4' => 'RKW_MAILER_NAMESPACES_ARRAY RKW\RkwBasics\Domain\Model\Pages:2,RKW\RkwBasics\Domain\Model\Pages:3',
        ];
        
        $expected = [
            'test1' => $entityOne,
            'test2' => $entityTwo,
            'test3' => $entityThree,
            'test4' => $objectStorage,
        ];

        $result = $this->subject->explodeMarker($marker);

        static::assertCount(4, $expected);
        static::assertInstanceOf(Pages::class, $result['test1']);
        static::assertInstanceOf(Pages::class, $result['test2']);
        static::assertInstanceOf(Pages::class, $result['test3']);
        static::assertEquals($expected['test1']->getUid(), $result['test1']->getUid());
        static::assertEquals($expected['test2']->getUid(), $result['test2']->getUid());
        static::assertEquals($expected['test3']->getUid(), $result['test3']->getUid());

        /** @var ObjectStorage $resultStorage */
        $resultStorage = $result['test4'];
        static::assertInstanceOf(ObjectStorage::class, $resultStorage);
        static::assertCount(2, $resultStorage);

        static::assertInstanceOf(Pages::class, $resultStorage->current());
        static::assertEquals($entityTwo->getUid(), $resultStorage->current()->getUid());
        $resultStorage->next();
        static::assertInstanceOf(Pages::class, $resultStorage->current());
        static::assertEquals($entityThree->getUid(), $resultStorage->current()->getUid());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function explodeMarkerConsistingOfPersistedButHiddenObjectsReturnsCompleteObjectArray()
    {

        /**
         * Scenario:
         *
         * Given a marker array contains four items
         * Given three of the items are references to existing but hidden objects in the database
         * Given the fourth item is a reference to an objectStorage with two objects
         * Given these two objects are references to existing but hidden objects in the database
         * When the method is called
         * Then the marker array returns four items
         * Then the first three items are contain objects of the given type 
         * Then the first three items are contain the objects identified by the given uid
         * Then the fourth item contaons an array with two objects of the given type and uid
         * Then the two objects of the fourth item are the objects identified by the given uid
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwBasics\Domain\Model\Pages $entityOne */
        $entityOne = $this->pagesRepository->findByIdentifier(1);
        $entityOne->setHidden(true);
        $this->pagesRepository->update($entityOne);

        /** @var \RKW\RkwBasics\Domain\Model\Pages $entityTwo */
        $entityTwo = $this->pagesRepository->findByIdentifier(2);
        $entityTwo->setHidden(true);
        $this->pagesRepository->update($entityTwo);

        /** @var \RKW\RkwBasics\Domain\Model\Pages $entityThree */
        $entityThree = $this->pagesRepository->findByIdentifier(3);
        $entityThree->setHidden(true);
        $this->pagesRepository->update($entityThree);
        
        $this->persistenceManager->persistAll();

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = $this->objectManager->get(ObjectStorage::class);
        $objectStorage->attach($entityTwo);
        $objectStorage->attach($entityThree);

        $marker = [
            'test1' => 'RKW_MAILER_NAMESPACES RKW\RkwBasics\Domain\Model\Pages:1',
            'test2' => 'RKW_MAILER_NAMESPACES RKW\RkwBasics\Domain\Model\Pages:2',
            'test3' => 'RKW_MAILER_NAMESPACES RKW\RkwBasics\Domain\Model\Pages:3',
            'test4' => 'RKW_MAILER_NAMESPACES_ARRAY RKW\RkwBasics\Domain\Model\Pages:2,RKW\RkwBasics\Domain\Model\Pages:3',
        ];

        $expected = [
            'test1' => $entityOne,
            'test2' => $entityTwo,
            'test3' => $entityThree,
            'test4' => $objectStorage,
        ];

        $result = $this->subject->explodeMarker($marker);

        static::assertCount(4, $expected);
        static::assertInstanceOf(Pages::class, $result['test1']);
        static::assertInstanceOf(Pages::class, $result['test2']);
        static::assertInstanceOf(Pages::class, $result['test3']);
        static::assertEquals($expected['test1']->getUid(), $result['test1']->getUid());
        static::assertEquals($expected['test2']->getUid(), $result['test2']->getUid());
        static::assertEquals($expected['test3']->getUid(), $result['test3']->getUid());

        /** @var ObjectStorage $resultStorage */
        $resultStorage = $result['test4'];
        static::assertInstanceOf(ObjectStorage::class, $resultStorage);
        static::assertCount(2, $resultStorage);

        static::assertInstanceOf(Pages::class, $resultStorage->current());
        static::assertEquals($entityTwo->getUid(), $resultStorage->current()->getUid());
        $resultStorage->next();
        static::assertInstanceOf(Pages::class, $resultStorage->current());
        static::assertEquals($entityThree->getUid(), $resultStorage->current()->getUid());
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
         * Then the first two items contain objects of the given type 
         * Then the first two items contain the objects identified by the given uid
         * Then the third item is an object storage with one item
         * Then the first item of the object storage contains an object of the given type
         * Then the first item of the object storage is the object identified by the given uid
         * Then the non-existing object in the object storage is missing
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwBasics\Domain\Model\Pages $entityOne */
        $entityOne = $this->pagesRepository->findByIdentifier(1);

        /** @var \RKW\RkwBasics\Domain\Model\Pages $entityTwo */
        $entityTwo = $this->pagesRepository->findByIdentifier(2);

        /** @var \RKW\RkwBasics\Domain\Model\Pages $entityThree */
        $entityThree = $this->pagesRepository->findByIdentifier(3);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = $this->objectManager->get(ObjectStorage::class);
        $objectStorage->attach($entityThree);

        $marker = [
            'test1' => 'RKW_MAILER_NAMESPACES RKW\RkwBasics\Domain\Model\Pages:1',
            'test2' => 'RKW_MAILER_NAMESPACES RKW\RkwBasics\Domain\Model\Pages:2',
            'test3' => 'RKW_MAILER_NAMESPACES RKW\RkwBasics\Domain\Model\Pages:9999',
            'test4' => 'RKW_MAILER_NAMESPACES_ARRAY RKW\RkwBasics\Domain\Model\Pages:3,RKW\RkwMailer\Domain\Model\QueueMail:9999',
        ];

        $expected = [
            'test1' => $entityOne,
            'test2' => $entityTwo,
            'test4' => $objectStorage,
        ];

        $result = $this->subject->explodeMarker($marker);

        static::assertCount(3, $expected);
        static::assertInstanceOf(Pages::class, $result['test1']);
        static::assertInstanceOf(Pages::class, $result['test2']);
        static::assertEquals($expected['test1']->getUid(), $result['test1']->getUid());
        static::assertEquals($expected['test2']->getUid(), $result['test2']->getUid());

        /** @var ObjectStorage $resultStorage */
        $resultStorage = $result['test4'];
        static::assertInstanceOf(ObjectStorage::class, $resultStorage);
        static::assertCount(1, $resultStorage);

        static::assertInstanceOf(Pages::class, $resultStorage->current());
        static::assertEquals($entityThree->getUid(), $resultStorage->current()->getUid());
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
         * Then the first two items contain objects of the given type
         * Then the first two items contain the objects identified by the given uid
         * Then the non-existing object is missing
         * Then the third item is an object storage with no elements
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwBasics\Domain\Model\Pages $entityOne */
        $entityOne = $this->pagesRepository->findByIdentifier(1);

        /** @var \RKW\RkwBasics\Domain\Model\Pages $entityTwo */
        $entityTwo = $this->pagesRepository->findByIdentifier(2);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = $this->objectManager->get(ObjectStorage::class);

        $expected = [
            'test1' => $entityOne,
            'test2' => $entityTwo,
            'test4' => $objectStorage,
        ];

        $marker = [
            'test1' => 'RKW_MAILER_NAMESPACES RKW\RkwBasics\Domain\Model\Pages:1',
            'test2' => 'RKW_MAILER_NAMESPACES RKW\RkwBasics\Domain\Model\Pages:2',
            'test3' => 'RKW_MAILER_NAMESPACES RKW\RkwBasics\Domain\Model\Pages:9999',
            'test4' => 'RKW_MAILER_NAMESPACES_ARRAY RKW\RkwBasics\Domain\Model\Pages:8888,RKW\RkwMailer\Domain\Model\QueueMail:9999',
        ];

        $result = $this->subject->explodeMarker($marker);

        static::assertCount(3, $expected);
        static::assertInstanceOf(Pages::class, $result['test1']);
        static::assertInstanceOf(Pages::class, $result['test2']);
        static::assertEquals($expected['test1']->getUid(), $result['test1']->getUid());
        static::assertEquals($expected['test2']->getUid(), $result['test2']->getUid());

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
         * Then the first two items contain objects of the given type
         * Then the first two items contain the objects identified by the given uid
         * Then the third item is returned as untouched string
         * Then the fourth item is  returned as untouched string
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwBasics\Domain\Model\Pages $entityOne */
        $entityOne = $this->pagesRepository->findByIdentifier(1);

        /** @var \RKW\RkwBasics\Domain\Model\Pages $entityTwo */
        $entityTwo = $this->pagesRepository->findByIdentifier(2);

        $expected = [
            'test1' => $entityOne,
            'test2' => $entityTwo,
            'test3' => 'RKW_TESTER_NAMESPACES RKW\RkwBasics\Domain\Model\Pages:3',
            'test4' => 'RKW_TESTER_NAMESPACES_ARRAY RKW\RkwBasics\Domain\Model\Pages:8888,RKW\RkwMailer\Domain\Model\QueueMail:9999',

        ];

        $marker = [
            'test1' => 'RKW_MAILER_NAMESPACES RKW\RkwBasics\Domain\Model\Pages:1',
            'test2' => 'RKW_MAILER_NAMESPACES RKW\RkwBasics\Domain\Model\Pages:2',
            'test3' => 'RKW_TESTER_NAMESPACES RKW\RkwBasics\Domain\Model\Pages:3',
            'test4' => 'RKW_TESTER_NAMESPACES_ARRAY RKW\RkwBasics\Domain\Model\Pages:8888,RKW\RkwMailer\Domain\Model\QueueMail:9999',
        ];


        $result = $this->subject->explodeMarker($marker);

        static::assertCount(4, $expected);
        static::assertInstanceOf(Pages::class, $result['test1']);
        static::assertInstanceOf(Pages::class, $result['test2']);
        static::assertEquals($expected['test1']->getUid(), $result['test1']->getUid());
        static::assertEquals($expected['test2']->getUid(), $result['test2']->getUid());
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