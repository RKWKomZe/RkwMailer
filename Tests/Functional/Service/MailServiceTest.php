<?php
namespace RKW\RkwMailer\Tests\Functional\Service;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwMailer\Cache\MailBodyCache;
use RKW\RkwMailer\Persistence\MarkerReducer;
use RKW\RkwMailer\Service\MailService;
use RKW\RkwMailer\Domain\Repository\QueueMailRepository;
use RKW\RkwMailer\Domain\Repository\QueueRecipientRepository;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Domain\Repository\BackendUserRepository;

use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

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
     * @var \RKW\RkwMailer\Cache\MailBodyCache
     */
    private $mailBodyCache;

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
        $this->importDataSet(__DIR__ . '/Fixtures/Database/BounceMail.xml');
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
        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);
        $this->backendUserRepository = $this->objectManager->get(BackendUserRepository::class);
        $this->markerReducer = $this->objectManager->get(MarkerReducer::class);
        $this->mailBodyCache = $this->objectManager->get(MailBodyCache::class);

        $this->subject = $this->objectManager->get(MailService::class);

    }

    

    //=============================================

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function addQueueRecipient_GivenQueueRecipientWithoutEmail_ReturnsFalse()
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
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function addQueueRecipient_GivenValidQueueRecipient_ReturnsTrue()
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
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function addQueueRecipient_GivenValidQueueRecipient_SetsStatusAndPidAndAddsObjectToQueueMail()
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
        static::assertEquals(1, count($this->queueRecipientRepository->findByQueueMail($queueMail)));

    }


    //=============================================

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function hasQueueRecipient_GivenExistingQueueRecipient_ReturnsTrue()
    {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(15);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(\RKW\RkwMailer\Domain\Model\QueueRecipient::class);
        $queueRecipient->setEmail('mail@rkw.de');

        static::assertTrue($this->subject->hasQueueRecipient($queueRecipient));
    }

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function hasQueueRecipient_GivenNonExistingQueueRecipient_ReturnsFalse()
    {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(15);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(\RKW\RkwMailer\Domain\Model\QueueRecipient::class);
        $queueRecipient->setEmail('mail2@rkw.de');

        static::assertFalse($this->subject->hasQueueRecipient($queueRecipient));
    }

    
    //=============================================


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function setTo_GivenFeUserAndGivenAdditionalData_ReturnsTrueAndAddsQueueRecipientRespectively()
    {
        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(2);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        $additionalData = [
            'marker' => [
                'test' => 'testen',
                'object' => $queueMail,
            ],
            'subject' => 'Wir testen den Betreff',
        ];

        $markerFixture = [
            'test' => 'testen',
            'object' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:2',
        ];


        static::assertTrue($this->subject->setTo($frontendUser, $additionalData));

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $queueRecipients */
        $queueRecipients = $this->queueRecipientRepository->findByQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $queueRecipients->current();

        static::assertEquals(1, $queueRecipients->count());
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

        static::assertEmpty($this->mailBodyCache->getHtmlBody($queueRecipient));
        static::assertEmpty($this->mailBodyCache->getPlaintextBody($queueRecipient));
    }



    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function setTo_GivenFeUserAndGivenAdditionalDataWithRenderTemplatesTrue_ReturnsTrueAndAddsQueueRecipientRespectivelyAndRendersTemplates()
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
        $queueRecipients = $this->queueRecipientRepository->findByQueueMail($queueMail);

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

        static::assertNotEmpty($this->mailBodyCache->getHtmlBody($queueRecipient));
        static::assertNotEmpty($this->mailBodyCache->getPlaintextBody($queueRecipient));
    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function setTo_GivenBeUserAndGivenAdditionalData_ReturnsTrueAndAddsQueueRecipientRespectively()
    {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(2);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwRegistration\Domain\Model\BackendUser $backendUser */
        $backendUser = $this->backendUserRepository->findByIdentifier(1);

        $additionalData = [
            'marker' => [
                'test' => 'testen',
                'object' => $queueMail,
            ],
            'subject' => 'Wir testen den Betreff',
        ];

        $markerFixture = [
            'test' => 'testen',
            'object' => 'RKW_MAILER_NAMESPACES RKW\RkwMailer\Domain\Model\QueueMail:2',
        ];


        static::assertTrue($this->subject->setTo($backendUser, $additionalData));

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $queueRecipients */
        $queueRecipients = $this->queueRecipientRepository->findByQueueMail($queueMail);

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

        static::assertEmpty($this->mailBodyCache->getHtmlBody($queueRecipient));
        static::assertEmpty($this->mailBodyCache->getPlaintextBody($queueRecipient));
    }

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function setTo_GivenBeUserAndGivenAdditionalDataAndGivenRenderTemplatesTrue_ReturnsTrueAndAddsQueueRecipientRespectivelyAndRendersTemplates()
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
        $queueRecipients = $this->queueRecipientRepository->findByQueueMail($queueMail);

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

        static::assertNotEmpty($this->mailBodyCache->getHtmlBody($queueRecipient));
        static::assertNotEmpty($this->mailBodyCache->getPlaintextBody($queueRecipient));
    }


    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function setTo_GivenMultipleRecipients_ReturnsMultipleRecipients()
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
        $queueRecipients = $this->queueRecipientRepository->findByQueueMail($queueMail);
        static::assertEquals(2, count($queueRecipients));

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function send_UsingInvalidQueueMailObject_ThrowsException()
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
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function send_UsingQueueMailHavingStatusUnequalOne_ReturnsFalse()
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
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function send_UsingQueueMailHavingNoRecipients_ReturnsFalse()
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
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function send_UsingQueueMailHavingRecipientsWithStatusTwo_ReturnsTrueAndSetsStatus()
    {
        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(8);
        $this->subject->setQueueMail($queueMail);

        static::assertTrue($this->subject->send());

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMailUpdated */
        $queueMailUpdated = $this->queueMailRepository->findByIdentifier(8);

        static::assertEquals(2, $queueMailUpdated->getStatus());
    }

    //=============================================
    
    /**
     * @test
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\Exception\MailServiceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function prepareEmailForRecipient_UsingQueueMailWithTemplatesAndAttachmentsSetAndGivenQueueRecipient_AddsAttachmentsToMessageObject()
    {

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(14);
        $this->subject->setQueueMail($queueMail);

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(4);

        /** @var \TYPO3\CMS\Core\Mail\MailMessage $result */
        $result = $this->subject->prepareEmailForRecipient($queueRecipient);
        static::assertEquals(MailMessage::class, get_class($result));

        static::assertCount(4, $result->getChildren());

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

        /** @var \Swift_Attachment  $mimePartAttachment */
        $mimePartAttachment = $result->getChildren()[3];
        static::assertEquals(\Swift_Attachment::class, get_class($mimePartAttachment));
        static::assertEquals('Attachment.pdf', $mimePartAttachment->getFilename());
        static::assertEquals('application/pdf', $mimePartAttachment->getContentType());
        static::assertContains('Lorem Ipsum', $mimePartAttachment->getBody());

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