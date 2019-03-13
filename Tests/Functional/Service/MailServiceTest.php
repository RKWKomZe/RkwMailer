<?php
namespace RKW\RkwMailer\Tests\Functional\Service;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwMailer\Service\MailService;
use RKW\RkwMailer\Domain\Repository\QueueMailRepository;
use RKW\RkwMailer\Domain\Repository\QueueRecipientRepository;
use RKW\RkwMailer\Domain\Repository\StatisticMailRepository;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Domain\Repository\BackendUserRepository;

use TYPO3\CMS\Core\Mail\MailMessage;
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
        'typo3conf/ext/rkw_registration',
        'typo3conf/ext/rkw_mailer',
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
     * @var \RKW\RkwMailer\Domain\Repository\StatisticMailRepository
     */
    private $statisticMailRepository;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\BackendUserRepository
     */
    private $backendUserRepository;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     */
    private $frontendUserRepository;

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
        $this->importDataSet(__DIR__ . '/Fixtures/Database/StatisticMail.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/BeUsers.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/FeUsers.xml');



        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Tests/Functional/Service/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        $this->setUpFrontendRootPage(
            2,
            [
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Tests/Functional/Service/Fixtures/Frontend/Configuration/Subpage.typoscript',
            ]
        );

        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->queueMailRepository = $this->objectManager->get(QueueMailRepository::class);
        $this->queueRecipientRepository = $this->objectManager->get(QueueRecipientRepository::class);
        $this->statisticMailRepository = $this->objectManager->get(StatisticMailRepository::class);
        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);
        $this->backendUserRepository = $this->objectManager->get(BackendUserRepository::class);


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
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function setQueueMailGivenQueueMailWithoutFromAddressThrowsException ()
    {
        static::expectException(\RKW\RkwMailer\Service\Exception\MailServiceQueueMailException::class);

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(1);
        $this->subject->setQueueMail($queueMail);

    }

    /**
     * @test
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function setQueueMailGivenNonPersistentQueueMailThrowsException ()
    {
        static::expectException(\RKW\RkwMailer\Service\Exception\MailServiceQueueMailException::class);

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = GeneralUtility::makeInstance(\RKW\RkwMailer\Domain\Model\QueueMail::class);
        $this->subject->setQueueMail($queueMail);

    }


    /**
     * @test
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
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
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
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
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
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
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
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
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
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
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
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
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function renderSingleTemplateGivenWrongTemplateTypeThrowsException()
    {
        static::expectException(\RKW\RkwMailer\Service\Exception\MailServiceException::class);

        $this->subject->renderSingleTemplate('test');
    }

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function renderSingleTemplateWithoutTemplateSetInQueueMailThrowsException()
    {
        static::expectException(\RKW\RkwMailer\Service\Exception\MailServiceException::class);

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(1);
        $this->subject->setQueueMail($queueMail);
        $this->subject->renderSingleTemplate('test');
    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
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
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
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
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
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
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
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
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
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
            'test4' => 'RKW_MAILER_NAMESPACES_ARRAY RKW\RkwMailer\Domain\Model\QueueMail:2,RKW\RkwMailer\Domain\Model\QueueMail:3',
        ];

        static::assertEquals($markerFixture, $this->subject->implodeMarker($marker));
    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
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
            'test4' => $objectStorage,
        ];

        static::assertEquals($markerFixture, $this->subject->implodeMarker($marker));
    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
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
            'test4' => 'RKW_MAILER_NAMESPACES_ARRAY RKW\RkwMailer\Domain\Model\QueueMail:1,RKW\RkwMailer\Domain\Model\QueueMail:3',
        ];

        static::assertEquals($markerFixture, $this->subject->implodeMarker($marker));
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
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
            'test4' => 'RKW_MAILER_NAMESPACES_ARRAY RKW\RkwMailer\Domain\Model\QueueMail:2,RKW\RkwMailer\Domain\Model\QueueMail:3',
        ];

        static::assertEquals($markerFixture, $this->subject->explodeMarker($marker));
    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
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
            'test4' => $objectStorage,
        ];

        static::assertEquals($markerFixture, $this->subject->explodeMarker($marker));
    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
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
            'test4' => 'RKW_MAILER_NAMESPACES_ARRAY RKW\RkwMailer\Domain\Model\QueueMail:1,RKW\RkwMailer\Domain\Model\QueueMail:3',
        ];

        static::assertEquals($markerFixture, $this->subject->explodeMarker($marker));
    }

    //=============================================


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function renderTemplatesGivenNonPersistentQueueRecipientThrowsException()
    {

        static::expectException(\RKW\RkwMailer\Service\Exception\MailServiceQueueRecipientException::class);

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
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function renderTemplatesWithNoTemplatesDoesNoUpdates()
    {

        static::expectException(\RKW\RkwMailer\Service\Exception\MailServiceException::class);

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(1);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(1);
        $this->subject->renderTemplates($queueRecipient);

        $queueRecipientAfter = $this->queueRecipientRepository->findByIdentifier(1);
        static::assertEquals($queueRecipient, $queueRecipientAfter);

    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
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
        $calendarBody = $queueRecipientFinished->getCalendarBody();

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

        static::assertContains('BEGIN:VCALENDAR', $calendarBody);
        static::assertContains('SUMMARY:Test Kalender', $calendarBody);

    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
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
        $calendarBody = $queueRecipientFinished->getCalendarBody();

        static::assertContains('queueRecipient.uid: 1', $htmlBody);
        static::assertContains('queueRecipient.firstName: Sebastian', $htmlBody);
        static::assertContains('queueRecipient.lastName: Schmidt', $htmlBody);
        static::assertContains('test1.uid: 1', $htmlBody);
        static::assertContains('test2.0.uid: 2', $htmlBody);

        static::assertContains('NON-RENDER', $plaintextBody);

        static::assertContains('BEGIN:VCALENDAR', $calendarBody);
        static::assertContains('SUMMARY:Test Kalender', $calendarBody);

    }

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function renderTemplatesGivenPersistentQueueRecipientAndNoOtherTemplateDoesRenderOnlyTemplate()
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
        $calendarBody = $queueRecipientFinished->getCalendarBody();

        static::assertEmpty($htmlBody);
        static::assertEmpty($calendarBody);

        static::assertContains('queueRecipient.uid: 1', $plaintextBody);
        static::assertContains('queueRecipient.firstName: Sebastian', $plaintextBody);
        static::assertContains('queueRecipient.lastName: Schmidt', $plaintextBody);
        static::assertContains('test1.uid: 1', $plaintextBody);
        static::assertContains('test2.0.uid: 2', $plaintextBody);

    }

    //=============================================


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function setToGivenFeUserAndAdditionalDataReturnsTrueAndAddsQueueRecipientRespectively()
    {

        $object = $this->queueMailRepository->findByIdentifier(1);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        $additionalData = [
            'marker' => [
                'test' => 'testen',
                'object' => $object,
            ],
            'subject' => 'Wir testen den Betreff',
        ];

        $markerFixture = [
            'test' => 'testen',
            'object' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:1',
        ];


        static::assertTrue($this->subject->setTo($frontendUser, $additionalData));

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $queueRecipients */
        $queueRecipients = $this->subject->getQueueMail()->getQueueRecipients();

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $queueRecipients->current();

        static::assertEquals(1, count($queueRecipients));
        static::assertEquals($frontendUser , $queueRecipient->getFrontendUser());

        static::assertEquals('Karl', $queueRecipient->getFirstname());
        static::assertEquals('Lauterbach', $queueRecipient->getLastname());

        static::assertEquals('lauterbach@spd.de', $queueRecipient->getEmail());
        static::assertEquals('Prof.', $queueRecipient->getTitle());
        static::assertEquals('fr', $queueRecipient->getLanguageCode());

        static::assertEquals(1, $queueRecipient->getSalutation());
        static::assertEquals('Mrs.', $queueRecipient->getSalutationText());

        static::assertEquals($markerFixture, $queueRecipient->getMarker());
        static::assertEquals('Wir testen den Betreff', $queueRecipient->getSubject());

        static::assertEmpty($queueRecipient->getHtmlBody());
        static::assertEmpty($queueRecipient->getPlaintextBody());
    }



    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function setToGivenFeUserAndAdditionalDataWithRenderTemplatesTrueReturnsTrueAndAddsQueueRecipientRespectivelyAndRendersTemplates()
    {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(2);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $object */
        $object = $this->queueMailRepository->findByIdentifier(1);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        $additionalData = [
            'marker' => [
                'test' => 'testen',
                'object' => $object,
            ],
            'subject' => 'Wir testen den Betreff',
        ];

        $markerFixture = [
            'test' => 'testen',
            'object' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:1',
        ];


        static::assertTrue($this->subject->setTo($frontendUser, $additionalData, true));

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $queueRecipients */
        $queueRecipients = $this->subject->getQueueMail()->getQueueRecipients();

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $queueRecipients->current();

        static::assertEquals(1, count($queueRecipients));
        static::assertEquals($frontendUser , $queueRecipient->getFrontendUser());

                static::assertEquals('Karl', $queueRecipient->getFirstname());
        static::assertEquals('Lauterbach', $queueRecipient->getLastname());

        static::assertEquals('lauterbach@spd.de', $queueRecipient->getEmail());
        static::assertEquals('Prof.', $queueRecipient->getTitle());
        static::assertEquals('fr', $queueRecipient->getLanguageCode());

        static::assertEquals(1, $queueRecipient->getSalutation());
        static::assertEquals('Mrs.', $queueRecipient->getSalutationText());

        static::assertEquals($markerFixture, $queueRecipient->getMarker());
        static::assertEquals('Wir testen den Betreff', $queueRecipient->getSubject());

        static::assertNotEmpty($queueRecipient->getHtmlBody());
        static::assertNotEmpty($queueRecipient->getPlaintextBody());
    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function setToGivenBeUserAndAdditionalDataReturnsTrueAndAddsQueueRecipientRespectively()
    {

        $object = $this->queueMailRepository->findByIdentifier(1);

        /** @var \RKW\RkwRegistration\Domain\Model\BackendUser $backendUser */
        $backendUser = $this->backendUserRepository->findByIdentifier(1);

        $additionalData = [
            'marker' => [
                'test' => 'testen',
                'object' => $object,
            ],
            'subject' => 'Wir testen den Betreff',
        ];

        $markerFixture = [
            'test' => 'testen',
            'object' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:1',
        ];


        static::assertTrue($this->subject->setTo($backendUser, $additionalData));

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $queueRecipients */
        $queueRecipients = $this->subject->getQueueMail()->getQueueRecipients();

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $queueRecipients->current();

        static::assertEquals(1, count($queueRecipients));
        static::assertNull($queueRecipient->getFrontendUser());

        static::assertEquals('Admins', $queueRecipient->getFirstname());
        static::assertEquals('Sohn', $queueRecipient->getLastname());

        static::assertEquals('admins@sohn.com', $queueRecipient->getEmail());
        static::assertEquals('de', $queueRecipient->getLanguageCode());

        static::assertEquals(99, $queueRecipient->getSalutation());
        static::assertEquals('', $queueRecipient->getSalutationText());

        static::assertEquals($markerFixture, $queueRecipient->getMarker());
        static::assertEquals('Wir testen den Betreff', $queueRecipient->getSubject());

        static::assertEmpty($queueRecipient->getHtmlBody());
        static::assertEmpty($queueRecipient->getPlaintextBody());
    }

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function setToGivenBeUserAndAdditionalDataWithRenderTemplatesTrueReturnsTrueAndAddsQueueRecipientRespectivelyAndRendersTemplates()
    {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(2);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $object */
        $object = $this->queueMailRepository->findByIdentifier(1);

        /** @var \RKW\RkwRegistration\Domain\Model\BackendUser $backendUser */
        $backendUser = $this->backendUserRepository->findByIdentifier(1);

        $additionalData = [
            'marker' => [
                'test' => 'testen',
                'object' => $object,
            ],
            'subject' => 'Wir testen den Betreff',
        ];

        $markerFixture = [
            'test' => 'testen',
            'object' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:1',
        ];


        static::assertTrue($this->subject->setTo($backendUser, $additionalData, true));

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $queueRecipients */
        $queueRecipients = $this->subject->getQueueMail()->getQueueRecipients();

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $queueRecipients->current();

        static::assertEquals(1, count($queueRecipients));
        static::assertNull($queueRecipient->getFrontendUser());

        static::assertEquals('Admins', $queueRecipient->getFirstname());
        static::assertEquals('Sohn', $queueRecipient->getLastname());

        static::assertEquals('admins@sohn.com', $queueRecipient->getEmail());
        static::assertEquals('de', $queueRecipient->getLanguageCode());

        static::assertEquals(99, $queueRecipient->getSalutation());
        static::assertEquals('', $queueRecipient->getSalutationText());

        static::assertEquals($markerFixture, $queueRecipient->getMarker());
        static::assertEquals('Wir testen den Betreff', $queueRecipient->getSubject());

        static::assertNotEmpty($queueRecipient->getHtmlBody());
        static::assertNotEmpty($queueRecipient->getPlaintextBody());
    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function setToGivenMultipleRecipientsReturnsMultipleRecipients()
    {
        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(2);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwRegistration\Domain\Model\BackendUser $backendUser */
        $backendUser = $this->backendUserRepository->findByIdentifier(1);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        static::assertTrue($this->subject->setTo($backendUser));
        static::assertTrue($this->subject->setTo($frontendUser));

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $queueRecipients */
        $queueRecipients = $this->subject->getQueueMail()->getQueueRecipients();
        static::assertEquals(2, count($queueRecipients));

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function sendWithInvalidQueueMailObjectThrowsException()
    {

        static::expectException(\RKW\RkwMailer\Service\Exception\MailServiceQueueMailException::class);

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(1);
        $this->subject->setQueueMail($queueMail);
        $this->subject->send();


    }

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function sendWithQueueMailHavingStatusUnequalOneReturnsFalse()
    {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(6);
        $this->subject->setQueueMail($queueMail);
        static::assertFalse($this->subject->send());
    }

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function sendWithQueueMailHavingNoRecipientsReturnsFalse()
    {
        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(7);
        $this->subject->setQueueMail($queueMail);
        static::assertFalse($this->subject->send());
    }

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function sendWithQueueMailHavingRecipientsWithStatusTwoReturnsTrueAndSetsStatusAndAddsStatisticsWithCorrectTotalCount()
    {
        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(8);
        $this->subject->setQueueMail($queueMail);

        static::assertTrue($this->subject->send());

        $queueMailUpdated = $this->queueMailRepository->findByIdentifier(8);
        static::assertNotNull($queueMailUpdated->getStatisticMail());
        static::assertEquals(2, $queueMailUpdated->getStatisticMail()->getTotalCount());

        static::assertEquals(2, $queueMailUpdated->getStatus());
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function prepareEmailForRecipientWithInvalidQueueMailObjectThrowsException()
    {

        static::expectException(\RKW\RkwMailer\Service\Exception\MailServiceQueueMailException::class);

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(1);

        $class = new \ReflectionClass(MailService::class);
        $property = $class->getProperty('queueMail');
        $property->setAccessible(true);
        $property->setValue($this->subject, $queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(1);

        $this->subject->prepareEmailForRecipient($queueRecipient);

    }

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function prepareEmailForRecipientWithInvalidQueueRecipientObjectThrowsException()
    {

        static::expectException(\RKW\RkwMailer\Service\Exception\MailServiceQueueRecipientException::class);

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(8);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(11);

        $this->subject->prepareEmailForRecipient($queueRecipient);

    }

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function prepareEmailForRecipientGivenRecipientWithStatusSentReturnsNull()
    {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(8);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(6);

        static::assertNull($this->subject->prepareEmailForRecipient($queueRecipient));
    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function prepareEmailForRecipientWithQueueMailWithTemplatesSetAddsRenderedTemplatesToMessageObject()
    {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(8);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(4);

        /** @var \TYPO3\CMS\Core\Mail\MailMessage $result */
        $result = $this->subject->prepareEmailForRecipient($queueRecipient);
        static::assertEquals(MailMessage::class, get_class($result));

        static::assertCount(3, $result->getChildren());

        /** @var \Swift_MimePart  $mimePartHtml */
        $mimePartHtml = $result->getChildren()[0];
        static::assertEquals(\Swift_MimePart::class, get_class($mimePartHtml));
        static::assertContains('TEST-TEMPLATE-HTML', $mimePartHtml->getBody());

        /** @var \Swift_MimePart  $mimePartPlaintext */
        $mimePartPlaintext = $result->getChildren()[1];
        static::assertEquals(\Swift_MimePart::class, get_class($mimePartPlaintext));
        static::assertContains('TEST-TEMPLATE-PLAINTEXT', $mimePartPlaintext->getBody());

        /** @var \Swift_Attachment  $mimePartCalendar */
        $mimePartCalendar = $result->getChildren()[2];
        static::assertEquals(\Swift_Attachment::class, get_class($mimePartCalendar));
        static::assertEquals('meeting.ics', $mimePartCalendar->getFilename());
        static::assertEquals('text/calendar', $mimePartCalendar->getContentType());
        static::assertContains('SUMMARY:Test Kalender', $mimePartCalendar->getBody());

        /** @var \Swift_Attachment  $mimePartAttachment
         * @toDo: not working!
        $mimePartAttachment = $result->getChildren()[3];
        static::assertEquals(\Swift_Attachment::class, get_class($mimePartAttachment));
        static::assertEquals('test.txt', $mimePartAttachment->getFilename());
        static::assertEquals('text/plain', $mimePartAttachment->getContentType());
        static::assertContains('Lorem Ipsum', $mimePartAttachment->getBody());
         */

    }

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function prepareEmailForRecipientWithQueueMailWithoutTemplatesSetDefaultBodyToMessageObject()
    {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(9);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(7);

        /** @var \TYPO3\CMS\Core\Mail\MailMessage $result */
        $result = $this->subject->prepareEmailForRecipient($queueRecipient);
        static::assertEquals(MailMessage::class, get_class($result));

        static::assertCount(0, $result->getChildren());
        static::assertContains('Fallback Body Text', $result->getBody());

    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function prepareEmailForRecipientSetsQueueRecipientStatusToSending()
    {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(9);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(7);

        /** @var \TYPO3\CMS\Core\Mail\MailMessage $result */
        $result = $this->subject->prepareEmailForRecipient($queueRecipient);
        static::assertEquals(MailMessage::class, get_class($result));

        static::assertEquals(3, $queueRecipient->getStatus());

    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function prepareEmailForRecipientSetCorrectSenderInformation()
    {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(9);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(7);

        /** @var \TYPO3\CMS\Core\Mail\MailMessage $result */
        $result = $this->subject->prepareEmailForRecipient($queueRecipient);
        static::assertEquals(MailMessage::class, get_class($result));

        static::assertEquals(['test@testen.de' => 'Test'], $result->getFrom());
        static::assertEquals(['reply@testen.de' => null], $result->getReplyTo());
        static::assertEquals('return@testen.de', $result->getReturnPath());

    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function prepareEmailForRecipientSetsCorrectPriority()
    {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(9);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(7);

        /** @var \TYPO3\CMS\Core\Mail\MailMessage $result */
        $result = $this->subject->prepareEmailForRecipient($queueRecipient);
        static::assertEquals(MailMessage::class, get_class($result));

        static::assertEquals(1, $result->getPriority());

    }

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function prepareEmailForRecipientGivenQueueRecipientWithFullNameSetsCorrectRecipientInformation()
    {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(9);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(7);

        /** @var \TYPO3\CMS\Core\Mail\MailMessage $result */
        $result = $this->subject->prepareEmailForRecipient($queueRecipient);
        static::assertEquals(MailMessage::class, get_class($result));

        static::assertEquals(['debuger1@rkw.de' => 'Sabine Hannebambel'], $result->getTo());
    }

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function prepareEmailForRecipientGivenQueueRecipientWithLastNameOnlySetsCorrectRecipientInformation()
    {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(9);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(10);

        /** @var \TYPO3\CMS\Core\Mail\MailMessage $result */
        $result = $this->subject->prepareEmailForRecipient($queueRecipient);
        static::assertEquals(MailMessage::class, get_class($result));

        static::assertEquals(['debuger1@rkw.de' => 'Hannebambel'], $result->getTo());
    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function prepareEmailForRecipientGivenQueueRecipientWithoutFullNameSetsCorrectRecipientInformation()
    {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(9);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(9);

        /** @var \TYPO3\CMS\Core\Mail\MailMessage $result */
        $result = $this->subject->prepareEmailForRecipient($queueRecipient);
        static::assertEquals(MailMessage::class, get_class($result));

        static::assertEquals(['debuger1@rkw.de' => null], $result->getTo());
    }

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function prepareEmailForRecipientGivenQueueRecipientWithOwnSubjectSetsSubjectOfRecipient()
    {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(9);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(7);

        /** @var \TYPO3\CMS\Core\Mail\MailMessage $result */
        $result = $this->subject->prepareEmailForRecipient($queueRecipient);
        static::assertEquals(MailMessage::class, get_class($result));

        static::assertEquals('Betreff für QueueRecipient', $result->getSubject());

    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function prepareEmailForRecipientGivenQueueRecipientWithoutOwnSubjectSetsSubjectOfRecipient()
    {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(9);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(8);

        /** @var \TYPO3\CMS\Core\Mail\MailMessage $result */
        $result = $this->subject->prepareEmailForRecipient($queueRecipient);
        static::assertEquals(MailMessage::class, get_class($result));

        static::assertEquals('Testbetreff der QueueMail', $result->getSubject());

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function sendToRecipientWithInvalidQueueMailThrowsException()
    {

        static::expectException(\RKW\RkwMailer\Service\Exception\MailServiceQueueMailException::class);

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(1);

        $class = new \ReflectionClass(MailService::class);
        $property = $class->getProperty('queueMail');
        $property->setAccessible(true);
        $property->setValue($this->subject, $queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(7);

        $this->subject->sendToRecipient($queueRecipient);

    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function sendToRecipientWithInvalidQueueRecipientThrowsException()
    {

        static::expectException(\RKW\RkwMailer\Service\Exception\MailServiceQueueRecipientException::class);

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(9);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(11);
        $this->subject->sendToRecipient($queueRecipient);

    }



    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function sendToRecipientWithNonPersistentQueueRecipientThrowsException()
    {

        static::expectException(\RKW\RkwMailer\Service\Exception\MailServiceQueueRecipientException::class);

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(9);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(\RKW\RkwMailer\Domain\Model\QueueRecipient::class);
        $this->subject->sendToRecipient($queueRecipient);

    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function sendToRecipientWithQueueRecipientWithoutStatisticMailThrowsException()
    {

        static::expectException(\RKW\RkwMailer\Service\Exception\MailServiceException::class);

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(9);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(10);
        $this->subject->sendToRecipient($queueRecipient);
    }

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function sendToRecipientWithValidQueueRecipientSetsQueueRecipientStatusToSentAndAddsOneToContactStatisticCounterAndReturnsTrue()
    {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(8);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(4);
        static::assertTrue($this->subject->sendToRecipient($queueRecipient));

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipientResult */
        $queueRecipientResult = $this->queueRecipientRepository->findByIdentifier(4);
        static::assertEquals(4, $queueRecipientResult->getStatus());

        /** @var \RKW\RkwMailer\Domain\Model\StatisticMail $statisticMailResult */
        $statisticMailResult = $this->statisticMailRepository->findByIdentifier(2);
        static::assertEquals(2, $statisticMailResult->getTotalCount());
        static::assertEquals(1, $statisticMailResult->getContactedCount());
        static::assertEquals(0, $statisticMailResult->getErrorCount());

    }

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function sendToRecipientWithNonCompliantQueueRecipientSetsQueueRecipientStatusToErrorAndAddsOneToErrorStatisticCounterAndReturnsFalse()
    {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(8);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(5);
        static::assertFalse($this->subject->sendToRecipient($queueRecipient));

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipientResult */
        $queueRecipientResult = $this->queueRecipientRepository->findByIdentifier(5);
        static::assertEquals(99, $queueRecipientResult->getStatus());

        /** @var \RKW\RkwMailer\Domain\Model\StatisticMail $statisticMailResult */
        $statisticMailResult = $this->statisticMailRepository->findByIdentifier(2);
        static::assertEquals(2, $statisticMailResult->getTotalCount());
        static::assertEquals(0, $statisticMailResult->getContactedCount());
        static::assertEquals(1, $statisticMailResult->getErrorCount());

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