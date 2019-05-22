<?php
namespace RKW\RkwMailer\Tests\Functional\Domain\Repository;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

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
 * QueueRecipientRepositoryTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class QueueRecipientRepositoryTest extends FunctionalTestCase
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
     * @var \RKW\RkwMailer\Domain\Repository\QueueRecipientRepository
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
        $this->subject = $this->objectManager->get(QueueRecipientRepository::class);

    }


    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findLastSentByEMailOfQueueRecipientWithRightStatusWithGivenEmailFromQueueMailWithWrongStatusReturnsNull()
    {
        static::assertNull($this->subject->findLastSentInMailingByEMail('nothing@rkw.de'));

    }

    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findLastSentByEMailOfQueueRecipientWithRightStatusWithGivenEmailFromQueueMailWithWrongTypeReturnsNull()
    {
        static::assertNull($this->subject->findLastSentInMailingByEMail('nothing1@rkw.de'));

    }


    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findLastSentByEMailWithGivenEmailOfQueueRecipientWithWrongStatusFromQueueMailWithRightTypeAndStatusReturnsNull()
    {
        static::assertNull($this->subject->findLastSentInMailingByEMail('nothing2@rkw.de'));

    }

    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findLastSentByEMailWithGivenEmailOfQueueRecipientWithRightStatusFromQueueMailWithRightTypeAndStatusReturnsQueueRecipient()
    {
        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->subject->findLastSentInMailingByEMail('nothing3@rkw.de');
        static::assertInstanceOf('\RKW\RkwMailer\Domain\Model\QueueRecipient', $queueRecipient);
        static::assertEquals($queueRecipient->getUid(), 4);

    }


    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findLastSentByEMailWithGivenEmailOfQueueRecipientWithRightStatusFromQueueMailWithRightTypeAndStatusReturnsRightQueueRecipient()
    {
        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->subject->findLastSentInMailingByEMail('nothing4@rkw.de');
        static::assertInstanceOf('\RKW\RkwMailer\Domain\Model\QueueRecipient', $queueRecipient);
        static::assertEquals($queueRecipient->getUid(), 6);

    }

    /**
     * TearDown
     */
    protected function tearDown()
    {

        parent::tearDown();
    }








}