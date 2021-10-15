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
 * @deprecated 
 * @toDo: rework
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


        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->queueMailRepository = $this->objectManager->get(QueueMailRepository::class);
        $this->queueRecipientRepository = $this->objectManager->get(QueueRecipientRepository::class);

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
        parent::tearDown();
    }








}