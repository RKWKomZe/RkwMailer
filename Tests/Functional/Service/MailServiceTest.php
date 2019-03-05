<?php
namespace RKW\RkwMailer\Tests\Functional\Service;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwMailer\Service\MailService;
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
    protected $testExtensionsToLoad = ['typo3conf/ext/rkw_mailer'];

    /**
     * @var \RKW\RkwMailer\Service\MailService
     */
    private $subject = null;

    /**
     * @var \RKW\RkwMailer\Domain\Repository\QueueMailRepository
     */
    private $queueMailRepository;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    private $persistenceManager = null;


    /**
     * Setup
     * @throws \ReflectionException
     */
    protected function setUp()
    {
        parent::setUp();

        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->queueMailRepository = $objectManager->get(QueueMailRepository::class);
        $this->subject = $objectManager->get(MailService::class);


        // inject configuration into protected property
        $class = new \ReflectionClass(MailService::class);
        $property = $class->getProperty("settings");
        $property->setAccessible(true);

        $property->setValue($this->subject, [
            'persistence' => [
                'storagePid' => 9999
            ],
            'view' => [
                'layoutRootPaths' => [
                    0 => 'EXT:rkw_mailer/Resources/Private/Layouts/'
                ],
                ' templateRootPaths' => [
                    0 => 'EXT:rkw_mailer/Resources/Private/Templates/'
                ],
                'partialRootPaths' => [
                    0 => 'EXT:rkw_mailer/Resources/Private/Partials/'
                ],
            ],
            'settings' => [
                'redirectPid' => 9999,
                'redirectDelay' => 100,
                'counterPixelPid' => 999,
                'baseUrl' => '',
                'basePathImages' => '',
                'basePathLogo' => ''
            ]
        ]);

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

    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }








}