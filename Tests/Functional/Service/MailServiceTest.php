<?php
namespace RKW\RkwMailer\Tests\Functional\Service;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwMailer\Service\MailService;
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
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_mailer'
    ];

    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [];

    /**
     * @var \RKW\RkwMailer\Service\MailService
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
        $this->importDataSet(__DIR__ . '/Fixtures/Database/FeUsers.xml');


        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Tests/Functional/Service/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        $this->setUpFrontendRootPage(
            2,
            [
                'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Tests/Functional/Service/Fixtures/Frontend/Configuration/Subpage.typoscript',
            ]
        );

        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->queueMailRepository = $this->objectManager->get(QueueMailRepository::class);
        $this->queueRecipientRepository = $this->objectManager->get(QueueRecipientRepository::class);

        $this->subject = $this->objectManager->get(MailService::class);

    }


    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function getQueueMailReturnsPersistedQueueMailWithDefaultValues ()
    {

        $queueMail = $this->subject->getQueueMail();
        static::assertEquals($queueMail->getPid(), 9999);
        static::assertEquals($queueMail->getStatus(), 1);
        static::assertGreaterThan(0, $queueMail->getTstampFavSending());
        static::assertGreaterThan(0, $queueMail->getCrDate());
    }


    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function getQueueMailReturnsSameObjectOnSecondCall ()
    {
        $queueMail = $this->subject->getQueueMail();
        static::assertSame($queueMail, $this->subject->getQueueMail());
    }

    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function getQueueMailSavesQueueMailToDatabase ()
    {
        $queueMail = $this->subject->getQueueMail();
        $result = $this->queueMailRepository->findAll()->toArray();
        static::assertSame($queueMail, $result[count($result)-1]);
    }

    //=============================================

    /**
     * @test
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function setQueueMailGivenQueueMailWithoutTemplatesThrowsException ()
    {
        static::expectException(\RKW\RkwMailer\Service\MailServiceException::class);

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(1);
        $this->subject->setQueueMail($queueMail);

    }

    /**
     * @test
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function setQueueMailGivenNewQueueMailThrowsException ()
    {
        static::expectException(\RKW\RkwMailer\Service\MailServiceException::class);

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = GeneralUtility::makeInstance(\RKW\RkwMailer\Domain\Model\QueueMail::class);
        $this->subject->setQueueMail($queueMail);

    }


    /**
     * @test
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function setQueueMailGivenSavedQueueMailWorks()
    {
        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(2);
        $this->subject->setQueueMail($queueMail);

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function addQueueRecipientGivenQueueRecipientWithoutEmailReturnsFalse()
    {
        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(\RKW\RkwMailer\Domain\Model\QueueRecipient::class);
        static::assertFalse($this->subject->addQueueRecipient($queueRecipient));

    }

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function addQueueRecipientGivenValidQueueRecipientReturnsTrue()
    {
        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(\RKW\RkwMailer\Domain\Model\QueueRecipient::class);
        $queueRecipient->setEmail('debug@rkw.de');
        static::assertTrue($this->subject->addQueueRecipient($queueRecipient));

    }

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function addQueueRecipientGivenValidQueueRecipientSetsStatusAndPidAndAddsObjectToQueueMail()
    {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(2);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(\RKW\RkwMailer\Domain\Model\QueueRecipient::class);
        $queueRecipient->setEmail('debug@rkw.de');

        $this->subject->addQueueRecipient($queueRecipient);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $fixture */
        $result = $this->queueRecipientRepository->findAll()->toArray();
        $fixture = $result[count($result)-1];

        static::assertEquals(9999, $fixture->getPid());
        static::assertEquals(2, $fixture->getStatus());
        static::assertEquals('debug@rkw.de', $fixture->getEmail());
        static::assertEquals(1, count($queueMail->getQueueRecipients()));

    }


    //=============================================

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function getImageUrlReturnsExpectedValueBasedOnConfiguration()
    {
        static::assertEquals('http://www.example.de/typo3conf/ext/rkw_mailer/Resources/Public/Images', $this->subject->getImageUrl());
    }

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function getLogoUrlReturnsExpectedValueBasedOnConfiguration()
    {
        static::assertEquals('http://www.example.de/typo3conf/ext/rkw_mailer/Resources/Public/Images/logo.png', $this->subject->getLogoUrl());
    }

    //=============================================
    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function renderSingleTemplateGivenWrongTemplateTypeThrowsException()
    {
        static::expectException(\RKW\RkwMailer\Service\MailServiceException::class);

        $this->subject->renderSingleTemplate('test');
    }

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function renderSingleTemplateWithoutTemplateSetInQueueMailThrowsException()
    {
        static::expectException(\RKW\RkwMailer\Service\MailServiceException::class);

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(1);
        $this->subject->setQueueMail($queueMail);
        $this->subject->renderSingleTemplate('test');
    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function renderSingleTemplateGivenHtmlTemplateTypeReturnsExpectedString()
    {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(2);
        $this->subject->setQueueMail($queueMail);

        $result = $this->subject->renderSingleTemplate('html');
        static::assertContains('TEST-TEMPLATE-HTML', $result);
        static::assertContains('ROOTPAGE', $result);
        static::assertContains('queueMail.uid: 2', $result);
        static::assertContains('queueMail.settingsPid: 0', $result);
        static::assertContains('mailType: Html', $result);
        static::assertContains('settings.redirectPid: 9999', $result);

        static::assertContains('<a href="http://www.example.de/test.html">Test</a>', $result);
        static::assertContains('<img src="http://www.example.de/test.png" width="30" height="30" alt="Test"/>', $result);
        static::assertContains('baseUrl: http://www.example.de', $result);
        static::assertContains('baseUrlImages: http://www.example.de/typo3conf/ext/rkw_mailer/Resources/Public/Images', $result);
        static::assertContains('baseUrlLogo: http://www.example.de/typo3conf/ext/rkw_mailer/Resources/Public/Images/logo.png', $result);

    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function renderSingleTemplateGivenPlaintextTemplateTypeReturnsExpectedString()
    {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(2);
        $this->subject->setQueueMail($queueMail);

        $result = $this->subject->renderSingleTemplate('plaintext');
        static::assertContains('TEST-TEMPLATE-PLAINTEXT', $result);
        static::assertContains('ROOTPAGE', $result);
        static::assertContains('queueMail.uid: 2', $result);
        static::assertContains('queueMail.settingsPid: 0', $result);
        static::assertContains('mailType: Plaintext', $result);
        static::assertContains('settings.redirectPid: 9999', $result);

        static::assertContains('baseUrl: http://www.example.de', $result);
        static::assertContains('baseUrlImages: http://www.example.de/typo3conf/ext/rkw_mailer/Resources/Public/Images', $result);
        static::assertContains('baseUrlLogo: http://www.example.de/typo3conf/ext/rkw_mailer/Resources/Public/Images/logo.png', $result);

    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function renderSingleTemplateGivenHtmlTemplateTypeWithSubpageConfigReturnsExpectedString()
    {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(3);
        $this->subject->setQueueMail($queueMail);

        $result = $this->subject->renderSingleTemplate('html');
        static::assertContains('TEST-TEMPLATE-HTML', $result);
        static::assertContains('SUBPAGE', $result);
        static::assertContains('queueMail.uid: 3', $result);
        static::assertContains('queueMail.settingsPid: 2', $result);
        static::assertContains('mailType: Html', $result);
        static::assertContains('settings.redirectPid: 8888', $result);

        static::assertContains('<a href="http://www.example.de/test.html">Test</a>', $result);
        static::assertContains('<img src="http://www.example.de/test.png" width="30" height="30" alt="Test"/>', $result);
        static::assertContains('baseUrl: http://www.example.de', $result);
        static::assertContains('baseUrlImages: http://www.example.de/typo3conf/ext/rkw_mailer/Resources/Public/Images', $result);
        static::assertContains('baseUrlLogo: http://www.example.de/typo3conf/ext/rkw_mailer/Resources/Public/Images/logo.png', $result);

    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function renderSingleTemplateGivenPlaintextTemplateWithSubpageConfigTypeReturnsExpectedString()
    {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(3);
        $this->subject->setQueueMail($queueMail);

        $result = $this->subject->renderSingleTemplate('plaintext');
        static::assertContains('TEST-TEMPLATE-PLAINTEXT', $result);
        static::assertContains('SUBPAGE', $result);
        static::assertContains('queueMail.uid: 3', $result);
        static::assertContains('queueMail.settingsPid: 2', $result);
        static::assertContains('mailType: Plaintext', $result);
        static::assertContains('settings.redirectPid: 8888', $result);

        static::assertContains('baseUrl: http://www.example.de', $result);
        static::assertContains('baseUrlImages: http://www.example.de/typo3conf/ext/rkw_mailer/Resources/Public/Images', $result);
        static::assertContains('baseUrlLogo: http://www.example.de/typo3conf/ext/rkw_mailer/Resources/Public/Images/logo.png', $result);

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function implodeMarkerWithPersistedObjectsReturnsCompletelyReducedArray()
    {

        /**
         * Things we need:
         * 1) \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
         * 2) ObjectStorage with at least one \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
         */
        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $abstractEntityOne = $this->queueMailRepository->findByIdentifier(1);
        $abstractEntityTwo = $this->queueMailRepository->findByIdentifier(2);
        $abstractEntityThree = $this->queueMailRepository->findByIdentifier(3);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage');
        $objectStorage->attach($abstractEntityTwo);
        $objectStorage->attach($abstractEntityThree);

        $marker = [
            'test1' => $abstractEntityOne,
            'test2' => $abstractEntityTwo,
            'test3' => $abstractEntityThree,
            'test4' => $objectStorage,
        ];

        $markerFixture = [
            'test1' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:1',
            'test2' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:2',
            'test3' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:3',
            'test4' => 'RKW_MAILER_NAMESPACES_ARRAY RKW\RkwMailer\Domain\Model\QueueMail:2,RKW\RkwMailer\Domain\Model\QueueMail:3'
        ];

        static::assertEquals($markerFixture, $this->subject->implodeMarker($marker));
    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function implodeMarkerWithMixedObjectsInObjectStorageLeavesObjectStorageUntouched()
    {

        /**
         * Things we need:
         * 1) \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
         * 2) ObjectStorage with at least one \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
         */
        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $abstractEntityOne = $this->queueMailRepository->findByIdentifier(1);
        $abstractEntityTwo = GeneralUtility::makeInstance(\RKW\RkwMailer\Domain\Model\QueueRecipient::class);
        $abstractEntityThree = $this->queueMailRepository->findByIdentifier(3);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage');
        $objectStorage->attach($abstractEntityTwo);
        $objectStorage->attach($abstractEntityThree);

        $marker = [
            'test1' => $abstractEntityOne,
            'test2' => $abstractEntityTwo,
            'test3' => $abstractEntityThree,
            'test4' => $objectStorage,
        ];

        $markerFixture = [
            'test1' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:1',
            'test2' => $abstractEntityTwo,
            'test3' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:3',
            'test4' => $objectStorage
        ];

        static::assertEquals($markerFixture, $this->subject->implodeMarker($marker));
    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function implodeMarkerWithMixedObjectsButNonMixedObjectStorageReducesObjectStorage()
    {

        /**
         * Things we need:
         * 1) \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
         * 2) ObjectStorage with at least one \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
         */
        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $abstractEntityOne = $this->queueMailRepository->findByIdentifier(1);
        $abstractEntityTwo = GeneralUtility::makeInstance(\RKW\RkwMailer\Domain\Model\QueueRecipient::class);
        $abstractEntityThree = $this->queueMailRepository->findByIdentifier(3);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage');
        $objectStorage->attach($abstractEntityOne);
        $objectStorage->attach($abstractEntityThree);

        $marker = [
            'test1' => $abstractEntityOne,
            'test2' => $abstractEntityTwo,
            'test3' => $abstractEntityThree,
            'test4' => $objectStorage,
        ];

        $markerFixture = [
            'test1' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:1',
            'test2' => $abstractEntityTwo,
            'test3' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:3',
            'test4' => 'RKW_MAILER_NAMESPACES_ARRAY RKW\RkwMailer\Domain\Model\QueueMail:1,RKW\RkwMailer\Domain\Model\QueueMail:3'
        ];

        static::assertEquals($markerFixture, $this->subject->implodeMarker($marker));
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function explodeMarkerWithPersistedObjectsReturnsCompleteObjectArray()
    {

        /**
         * Things we need:
         * 1) \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
         * 2) ObjectStorage with at least one \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
         */
        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $abstractEntityOne = $this->queueMailRepository->findByIdentifier(1);
        $abstractEntityTwo = $this->queueMailRepository->findByIdentifier(2);
        $abstractEntityThree = $this->queueMailRepository->findByIdentifier(3);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage');
        $objectStorage->attach($abstractEntityTwo);
        $objectStorage->attach($abstractEntityThree);

        $markerFixture = [
            'test1' => $abstractEntityOne,
            'test2' => $abstractEntityTwo,
            'test3' => $abstractEntityThree,
            'test4' => $objectStorage,
        ];

        $marker = [
            'test1' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:1',
            'test2' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:2',
            'test3' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:3',
            'test4' => 'RKW_MAILER_NAMESPACES_ARRAY RKW\RkwMailer\Domain\Model\QueueMail:2,RKW\RkwMailer\Domain\Model\QueueMail:3'
        ];

        static::assertEquals($markerFixture, $this->subject->explodeMarker($marker));
    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function explodeMarkerWithMixedObjectsInObjectStorageReturnsCompleteObjectArray()
    {

        /**
         * Things we need:
         * 1) \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
         * 2) ObjectStorage with at least one \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
         */
        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $abstractEntityOne = $this->queueMailRepository->findByIdentifier(1);
        $abstractEntityTwo = GeneralUtility::makeInstance(\RKW\RkwMailer\Domain\Model\QueueRecipient::class);
        $abstractEntityThree = $this->queueMailRepository->findByIdentifier(3);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage');
        $objectStorage->attach($abstractEntityTwo);
        $objectStorage->attach($abstractEntityThree);

        $markerFixture = [
            'test1' =>$abstractEntityOne,
            'test2' =>$abstractEntityTwo,
            'test3' =>$abstractEntityThree,
            'test4' =>$objectStorage,
        ];

        $marker = [
            'test1' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:1',
            'test2' => $abstractEntityTwo,
            'test3' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:3',
            'test4' => $objectStorage
        ];

        static::assertEquals($markerFixture, $this->subject->explodeMarker($marker));
    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function explodeMarkerWithMixedObjectsButNonMixedObjectStorageReturnsCompleteObjectArray()
    {

        /**
         * Things we need:
         * 1) \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
         * 2) ObjectStorage with at least one \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
         */
        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $abstractEntityOne = $this->queueMailRepository->findByIdentifier(1);
        $abstractEntityTwo = GeneralUtility::makeInstance(\RKW\RkwMailer\Domain\Model\QueueRecipient::class);
        $abstractEntityThree = $this->queueMailRepository->findByIdentifier(3);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage');
        $objectStorage->attach($abstractEntityOne);
        $objectStorage->attach($abstractEntityThree);

        $markerFixture = [
            'test1' => $abstractEntityOne,
            'test2' => $abstractEntityTwo,
            'test3' => $abstractEntityThree,
            'test4' => $objectStorage,
        ];

        $marker = [
            'test1' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:1',
            'test2' => $abstractEntityTwo,
            'test3' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:3',
            'test4' => 'RKW_MAILER_NAMESPACES_ARRAY RKW\RkwMailer\Domain\Model\QueueMail:1,RKW\RkwMailer\Domain\Model\QueueMail:3'
        ];

        static::assertEquals($markerFixture, $this->subject->explodeMarker($marker));
    }

    //=============================================


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function renderTemplatesGivenNonPersistentQueueRecipientThrowsException()
    {

        static::expectException(\RKW\RkwMailer\Service\MailServiceException::class);

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(3);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(\RKW\RkwMailer\Domain\Model\QueueRecipient::class);
        $this->subject->renderTemplates($queueRecipient);
    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function renderTemplatesWithNoTemplatesInQueueMailThrowsException()
    {

        static::expectException(\RKW\RkwMailer\Service\MailServiceException::class);

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(1);
        $this->subject->setQueueMail($queueMail);

    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function renderTemplatesGivenPersistentQueueRecipientRendersAndStoresTemplates()
    {
        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(2);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(1);

        // prepare marker
        $abstractEntityOne = $this->queueMailRepository->findByIdentifier(1);
        $abstractEntityTwo = $this->queueMailRepository->findByIdentifier(2);
        $abstractEntityThree = $this->queueMailRepository->findByIdentifier(3);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage');
        $objectStorage->attach($abstractEntityTwo);
        $objectStorage->attach($abstractEntityThree);

        $marker = [
            'test1' => $abstractEntityOne,
            'test2' => $objectStorage,
        ];

        $queueRecipient->setMarker($this->subject->implodeMarker($marker));

        // render template
        $this->subject->renderTemplates($queueRecipient);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipientFinished */
        $queueRecipientFinished = $this->queueRecipientRepository->findByIdentifier(1);
        $htmlBody = $queueRecipientFinished->getHtmlBody();
        $plaintextBody = $queueRecipientFinished->getPlaintextBody();

        static::assertContains('queueRecipient.uid: 1', $htmlBody);
        static::assertContains('queueRecipient.firstName: Sebastian', $htmlBody);
        static::assertContains('queueRecipient.lastName: Schmidt', $htmlBody);
        static::assertContains('test1.uid: 1', $htmlBody);
        static::assertContains('test2.0.uid: 2', $htmlBody);

        static::assertContains('queueRecipient.uid: 1', $plaintextBody);
        static::assertContains('queueRecipient.firstName: Sebastian', $plaintextBody);
        static::assertContains('queueRecipient.lastName: Schmidt', $plaintextBody);
        static::assertContains('test1.uid: 1', $plaintextBody);
        static::assertContains('test2.0.uid: 2', $plaintextBody);

    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function renderTemplatesGivenPersistentQueueRecipientWithAlreadySetPlaintextDoesNotRenderPlaintextTemplate()
    {
        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(2);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(1);

        // prepare marker
        $abstractEntityOne = $this->queueMailRepository->findByIdentifier(1);
        $abstractEntityTwo = $this->queueMailRepository->findByIdentifier(2);
        $abstractEntityThree = $this->queueMailRepository->findByIdentifier(3);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage');
        $objectStorage->attach($abstractEntityTwo);
        $objectStorage->attach($abstractEntityThree);

        $marker = [
            'test1' => $abstractEntityOne,
            'test2' => $objectStorage,
        ];

        $queueRecipient->setMarker($this->subject->implodeMarker($marker));
        $queueRecipient->setPlaintextBody('NON-RENDER');

        // render template
        $this->subject->renderTemplates($queueRecipient);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipientFinished */
        $queueRecipientFinished = $this->queueRecipientRepository->findByIdentifier(1);
        $htmlBody = $queueRecipientFinished->getHtmlBody();
        $plaintextBody = $queueRecipientFinished->getPlaintextBody();

        static::assertContains('queueRecipient.uid: 1', $htmlBody);
        static::assertContains('queueRecipient.firstName: Sebastian', $htmlBody);
        static::assertContains('queueRecipient.lastName: Schmidt', $htmlBody);
        static::assertContains('test1.uid: 1', $htmlBody);
        static::assertContains('test2.0.uid: 2', $htmlBody);

        static::assertContains('NON-RENDER', $plaintextBody);

    }

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function renderTemplatesGivenPersistentQueueRecipientAndNoHtmlTemplateDoesNotRenderPlaintextTemplate()
    {
        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(4);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(1);

        // prepare marker
        $abstractEntityOne = $this->queueMailRepository->findByIdentifier(1);
        $abstractEntityTwo = $this->queueMailRepository->findByIdentifier(2);
        $abstractEntityThree = $this->queueMailRepository->findByIdentifier(3);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage');
        $objectStorage->attach($abstractEntityTwo);
        $objectStorage->attach($abstractEntityThree);

        $marker = [
            'test1' => $abstractEntityOne,
            'test2' => $objectStorage,
        ];

        $queueRecipient->setMarker($this->subject->implodeMarker($marker));

        // render template
        $this->subject->renderTemplates($queueRecipient);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipientFinished */
        $queueRecipientFinished = $this->queueRecipientRepository->findByIdentifier(1);
        $htmlBody = $queueRecipientFinished->getHtmlBody();
        $plaintextBody = $queueRecipientFinished->getPlaintextBody();

        static::assertEmpty($htmlBody);

        static::assertContains('queueRecipient.uid: 1', $plaintextBody);
        static::assertContains('queueRecipient.firstName: Sebastian', $plaintextBody);
        static::assertContains('queueRecipient.lastName: Schmidt', $plaintextBody);
        static::assertContains('test1.uid: 1', $plaintextBody);
        static::assertContains('test2.0.uid: 2', $plaintextBody);

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