<?php
namespace RKW\RkwMailer\Tests\Integration\ViewHelpers\Frontend\Replace;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use TYPO3\CMS\Fluid\View\StandaloneView;
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
 * RedirectLinksViewHelperTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RedirectLinksViewHelperTest extends FunctionalTestCase
{

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_mailer',
        'typo3conf/ext/realurl'
    ];

    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [ ];

    /**
     * @var \TYPO3\CMS\Fluid\View\StandaloneView
     */
    private $standAloneViewHelper;

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
    private $persistenceManager;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager;


    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp()
    {

        // define realUrl-config
        define('TX_REALURL_AUTOCONF_FILE', 'typo3conf/ext/rkw_mailer/Tests/Integration/ViewHelpers/Frontend/Replace/RedirectLinksViewHelperTest/Fixtures/RealUrlConfiguration.php');

        parent::setUp();

        $this->importDataSet(__DIR__ . '/RedirectLinksViewHelperTest/Fixtures/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:realurl/Configuration/TypoScript/setup.txt',
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Tests/Integration/ViewHelpers/Frontend/Replace/RedirectLinksViewHelperTest/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->queueMailRepository = $this->objectManager->get(QueueMailRepository::class);
        $this->queueRecipientRepository = $this->objectManager->get(QueueRecipientRepository::class);

        $this->standAloneViewHelper = $this->objectManager->get(StandaloneView::class);
        $this->standAloneViewHelper->setTemplateRootPaths(
            [
                0 => __DIR__ . '/RedirectLinksViewHelperTest/Fixtures/Frontend/Templates'
            ]
        );


    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReplacesNoLinkWhenNoQueueMailGiven ()
    {

        /**
        * Scenario:
        *
        * Given the ViewHelper is used in a template
        * Given a queueRecipient is defined
        * Given there is no queueMail given
        * When the ViewHelper is rendered
        * Then no replacement takes place
        */
        $this->importDataSet(__DIR__ . '/RedirectLinksViewHelperTest/Fixtures/Database/Check10.xml');

        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(1);

        $this->standAloneViewHelper->setTemplate('Check10.html');
        $this->standAloneViewHelper->assign('queueRecipient', $queueRecipient);

        $expected = file_get_contents(__DIR__ . '/RedirectLinksViewHelperTest/Fixtures/Expected/Check10.txt');
        $result = $this->standAloneViewHelper->render();

        static::assertEquals($expected, $result);
    }

    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReplacesLinkWhenQueueMailButNoQueueRecipientGiven ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a queueMail is defined
         * Given there is no queueRecipient given
         * When the ViewHelper is rendered
         * Then the replacement takes place
         */
        $this->importDataSet(__DIR__ . '/RedirectLinksViewHelperTest/Fixtures/Database/Check20.xml');

        $queueMail = $this->queueMailRepository->findByIdentifier(1);

        $this->standAloneViewHelper->setTemplate('Check20.html');
        $this->standAloneViewHelper->assign('queueMail', $queueMail);

        $expected = file_get_contents(__DIR__ . '/RedirectLinksViewHelperTest/Fixtures/Expected/Check20.txt');
        $result = $this->standAloneViewHelper->render();

        static::assertEquals($expected, $result);
    }

    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReplacesLinkWhenQueueMailAndQueueRecipientGiven ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a queueMail is defined
         * Given a queueRecipient is defined
         * When the ViewHelper is rendered
         * Then the replacement takes place
         */
        $this->importDataSet(__DIR__ . '/RedirectLinksViewHelperTest/Fixtures/Database/Check30.xml');

        $queueMail = $this->queueMailRepository->findByIdentifier(1);
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(1);

        $this->standAloneViewHelper->setTemplate('Check30.html');
        $this->standAloneViewHelper->assign('queueMail', $queueMail);
        $this->standAloneViewHelper->assign('queueRecipient', $queueRecipient);

        $expected = file_get_contents(__DIR__ . '/RedirectLinksViewHelperTest/Fixtures/Expected/Check30.txt');
        $result = $this->standAloneViewHelper->render();

        static::assertEquals($expected, $result);
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