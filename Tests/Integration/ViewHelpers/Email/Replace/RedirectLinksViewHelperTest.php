<?php
namespace RKW\RkwMailer\Tests\Integration\ViewHelpers\Email\Replace;

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
use RKW\RkwMailer\View\EmailStandaloneView;
use RKW\RkwMailer\Domain\Repository\QueueMailRepository;
use RKW\RkwMailer\Domain\Repository\QueueRecipientRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;


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
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/RedirectLinksViewHelperTest/Fixtures';


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
     * @var \RKW\RkwMailer\View\EmailStandaloneView
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
        define('TX_REALURL_AUTOCONF_FILE', 'typo3conf/ext/rkw_mailer/Tests/Integration/ViewHelpers/Email/Replace/RedirectLinksViewHelperTest/Fixtures/RealUrlConfiguration.php');

        parent::setUp();

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Global.xml');
        $this->setUpFrontendRootPage(
            100,
            [
                'EXT:realurl/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_basics/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->queueMailRepository = $this->objectManager->get(QueueMailRepository::class);
        $this->queueRecipientRepository = $this->objectManager->get(QueueRecipientRepository::class);

        $this->standAloneViewHelper = $this->objectManager->get(EmailStandaloneView::class, 100);
        $this->standAloneViewHelper->setTemplateRootPaths(
            [
                0 => self::FIXTURE_PATH . '/Frontend/Templates'
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
        * Given a queueRecipient-object is defined
        * Given there is no queueMail-object defined
        * When the ViewHelper is rendered
        * Then the links are returned unchanged
        */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(1);

        $this->standAloneViewHelper->setTemplate('Check10.html');
        $this->standAloneViewHelper->assign('queueRecipient', $queueRecipient);

        $expected = file_get_contents(self::FIXTURE_PATH . '/Expected/Check10.txt');
        $result = $this->standAloneViewHelper->render();

        self::assertEquals($expected, $result);
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
         * Given a queueMail-object is defined
         * Given there is no queueRecipient-object defined
         * When the ViewHelper is rendered
         * Then all normal links are replaced by a redirect link
         * Then anchor- and e-mail-links are left unchanged
         * Then a queueMail-parameter is set in the redirect-links
         * Then no queueRecipient-parameter is set in the redirect-links
         * Then no cHash-parameter is set
         * Then a noCache-parameter is set 
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        $queueMail = $this->queueMailRepository->findByIdentifier(1);

        $this->standAloneViewHelper->setTemplate('Check20.html');
        $this->standAloneViewHelper->assign('queueMail', $queueMail);

        $expected = file_get_contents(self::FIXTURE_PATH . '/Expected/Check20.txt');
        $result = $this->standAloneViewHelper->render();

        self::assertEquals($expected, $result);
        self::assertContains('tx_rkwmailer_rkwmailer%5Bmid%5D=1', $result);
        self::assertNotContains('tx_rkwmailer_rkwmailer%5Buid%5D', $result);
        self::assertNotContains('cHash=', $result);
        self::assertContains('/nc/', $result);
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
         * Given a queueMail-object is defined
         * Given a queueRecipient-object is defined
         * When the ViewHelper is rendered
         * Then all normal links are replaced by a redirect link
         * Then anchor- and e-mail-links are left unchanged 
         * Then a queueMail-parameter is set in the redirect-links
         * Then a queueRecipient-parameter is set in the redirect-links
         * Then no cHash-parameter is set
         * Then a noCache-parameter is set
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        $queueMail = $this->queueMailRepository->findByIdentifier(1);
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(1);

        $this->standAloneViewHelper->setTemplate('Check30.html');
        $this->standAloneViewHelper->assign('queueMail', $queueMail);
        $this->standAloneViewHelper->assign('queueRecipient', $queueRecipient);

        $expected = file_get_contents(self::FIXTURE_PATH . '/Expected/Check30.txt');
        $result = $this->standAloneViewHelper->render();

        self::assertEquals($expected, $result);
        self::assertContains('tx_rkwmailer_rkwmailer%5Bmid%5D=1', $result);
        self::assertContains('tx_rkwmailer_rkwmailer%5Buid%5D=1', $result);
        self::assertNotContains('cHash=', $result);
        self::assertContains('/nc/', $result);
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