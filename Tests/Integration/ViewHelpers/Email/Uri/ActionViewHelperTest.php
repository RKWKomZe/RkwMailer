<?php
namespace RKW\RkwMailer\Tests\Integration\ViewHelpers\Email\Uri;

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
use Madj2k\CoreExtended\Utility\FrontendSimulatorUtility;
use RKW\RkwMailer\View\EmailStandaloneView;
use RKW\RkwMailer\Domain\Repository\QueueMailRepository;
use RKW\RkwMailer\Domain\Repository\QueueRecipientRepository;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * ActionViewHelperTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ActionViewHelperTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/ActionViewHelperTest/Fixtures';

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
    protected function setUp(): void
    {
        parent::setUp();

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ],
            ['rkw-kompetenzzentrum.local' => self::FIXTURE_PATH .  '/Frontend/Configuration/config.yaml']
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->queueMailRepository = $this->objectManager->get(QueueMailRepository::class);
        $this->queueRecipientRepository = $this->objectManager->get(QueueRecipientRepository::class);

        $this->standAloneViewHelper = $this->objectManager->get(EmailStandaloneView::class, 1);
        $this->standAloneViewHelper->setTemplateRootPaths(
            [
                0 => self::FIXTURE_PATH . '/Frontend/Templates'
            ]
        );

    }

    /**
     * @test
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersAbsoluteLink ()
    {

        /**
        * Scenario:
        *
        * Given the ViewHelper is used in a template
        * Given the absolute parameter is set to false
        * Given the baseUrl of rkw_mailer is set to http-protocol
        * When the link is rendered
        * Then an absolute link is returned like in frontend context
        * Then the controller- and action-attribute are converted in a speaking URL
        * Then the link uses the http-protocol
        * Then no cHash is used
         */

        $this->standAloneViewHelper->setTemplate('Check10.html');
        $result = $this->standAloneViewHelper->render();
        self::assertStringContainsString('http://www.rkw-kompetenzzentrum.rkw.local/rkw-basics/mediasources', $result);
        self::assertStringNotContainsString('cHash=', $result);
    }


    /**
     * @test
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @todo this test runs smoothly if you select the ViewHelper-tests directory. If you run ALL tests, this test fails for mysterious reasons
     */
    public function itRendersAbsoluteLinkHttps ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given the absolute parameter is set to false
         * Given the baseUrl of rkw_mailer is set to https-protocol
         * When the link is rendered
         * Then an absolute link is returned like in frontend context
         * Then the controller- and action-attribute are converted in a speaking URL
         * Then the link uses the https-protocol
         * Then no cHash is used
         */
        $this->setUpFrontendRootPage(
            10,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/RootpageHttps.typoscript',
            ],
            ['rkw.local' => self::FIXTURE_PATH .  '/Frontend/Configuration/configHttps.yaml']
        );

        // Flush all caches because of file-cache for YAML-files!
        FrontendSimulatorUtility::resetFrontendEnvironment();
        GeneralUtility::makeInstance(CacheManager::class)->flushCaches();
        sleep(3);

        $this->standAloneViewHelper = $this->objectManager->get(EmailStandaloneView::class, 10);
        $this->standAloneViewHelper->setTemplate('Check10.html');
        $this->standAloneViewHelper->setTemplateRootPaths(
            [
                0 => self::FIXTURE_PATH . '/Frontend/Templates'
            ]
        );
        $result = $this->standAloneViewHelper->render();

        /** IMPORTANT HINT IN PhpDocs !!! */
        self::assertStringContainsString('https://www.rkw.local/rkw-basics/mediasources', $result);
        self::assertStringNotContainsString('cHash=', $result);
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersAbsoluteLinkToGivenPage ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given the pageUid-attribute is set to an existing site
         * When the link is rendered
         * Then an absolute link to this given pageUid is returned like in frontend context
         * Then the controller- and action-attribute are converted in a speaking URL
         * Then no cHash is used
         */

        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check20.xml');

        $this->standAloneViewHelper->setTemplate('Check20.html');
        $result = $this->standAloneViewHelper->render();
        self::assertStringContainsString('http://www.rkw-kompetenzzentrum.rkw.local/test/rkw-basics/mediasources', $result);
        self::assertStringNotContainsString('cHash=', $result);
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersAbsoluteLinkToGivenPageWithFeGroup ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given the pageUid-attribute is set to an existing site
         * Given that existing site is access-restricted
         * When the link is rendered
         * Then an absolute link to this given pageUid is returned like in frontend context
         * Then the controller- and action-attribute are converted in a speaking URL
         * Then no cHash is used
         */

        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check40.xml');

        $this->standAloneViewHelper->setTemplate('Check40.html');
        $result = $this->standAloneViewHelper->render();
        self::assertStringContainsString('http://www.rkw-kompetenzzentrum.rkw.local/test/rkw-basics/mediasources', $result);
        self::assertStringNotContainsString('cHash=', $result);
    }

    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersAbsoluteLinkWithPageType ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given the absolute parameter is set to false
         * Given the baseUrl of rkw_mailer is set to http-protocol
         * Given the pageType-attribute is set
         * When the link is rendered
         * Then an absolute link is returned like in frontend context
         * Then the controller- and action-attribute are converted in a speaking URL
         * Then the link uses the http-protocol
         * Then the pageType is set to the given value
         * Then no cHash is used
         */

        $this->standAloneViewHelper->setTemplate('Check50.html');
        $result = $this->standAloneViewHelper->render();
        self::assertStringContainsString('http://www.rkw-kompetenzzentrum.rkw.local/rkw-basics/mediasources/print', $result);
        self::assertStringNotContainsString('cHash=', $result);

    }

    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersAbsoluteLinkWithQueueMailAndRedirect ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a pageUid-attribute is set
         * Given a queueMail-attribute is set
         * Given a redirect page is configured and exists
         * When the link is rendered
         * Then an absolute link to the configured redirect page is returned like in frontend context
         * Then the redirect link calls the redirect plugin of rkw_mailer
         * Then the redirect link contains the queueMailUid
         * Then the redirect link contains an url-attribute
         * Then the url-attribute contains the absolute link to the given pageUid
         * Then no cHash is used
         * Then a noCache-parameter is set
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');
        $queueMail = $this->queueMailRepository->findByIdentifier(1);

        $this->standAloneViewHelper->setTemplate('Check30.html');
        $this->standAloneViewHelper->assign('queueMail', $queueMail);
        $result = $this->standAloneViewHelper->render();

        self::assertStringContainsString('http://www.rkw-kompetenzzentrum.rkw.local/umleitungsseite-der-umleitungen/rkw-mailer/redirect/1/?', $result);
        self::assertStringContainsString('tx_rkwmailer_rkwmailer%5Burl%5D=http%3A%2F%2Fwww.rkw-kompetenzzentrum.rkw.local%2Ftest%2Frkw-basics%2Fmediasources%2F', $result);
        self::assertStringNotContainsString('cHash=', $result);
        self::assertStringContainsString('no_cache=1', $result);


    }

    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersAbsoluteLinkWithQueueRecipientAndRedirect ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a pageUid-attribute is set
         * Given a queueMail-attribute is set
         * Given a queueRecipient-attribute is set
         * When the link is rendered
         * Then an absolute link to the configured redirect page is returned like in frontend context
         * Then the redirect link calls the redirect plugin of rkw_mailer
         * Then the redirect link contains the queueMailUid
         * Then the redirect link contains the queueRecipientUid
         * Then the redirect link contains an url-attribute
         * Then no cHash is used
         * Then a noCache-parameter is set
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');
        $queueMail = $this->queueMailRepository->findByIdentifier(1);
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(1);

        $this->standAloneViewHelper->setTemplate('Check30.html');
        $this->standAloneViewHelper->assign('queueMail', $queueMail);
        $this->standAloneViewHelper->assign('queueRecipient', $queueRecipient);
        $result = $this->standAloneViewHelper->render();

        self::assertStringContainsString('http://www.rkw-kompetenzzentrum.rkw.local/umleitungsseite-der-umleitungen/rkw-mailer/redirect/1/1/?', $result);
        self::assertStringContainsString('tx_rkwmailer_rkwmailer%5Burl%5D=http%3A%2F%2Fwww.rkw-kompetenzzentrum.rkw.local%2Ftest%2Frkw-basics%2Fmediasources%2F', $result);
        self::assertStringNotContainsString('cHash=', $result);
        self::assertStringContainsString('no_cache=1', $result);

    }

    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersAbsoluteLinkWithQueueRecipientAndRedirectAndPageType ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a pageUid-attribute is set
         * Given a queueMail-attribute is set
         * Given a redirect page is configured and exists
         * Given a pageType is set
         * When the link is rendered
         * Then an absolute link to the configured redirect page is returned like in frontend context
         * Then the redirect link calls the redirect plugin of rkw_mailer
         * Then the redirect link contains the queueMailUid
         * Then the redirect link contains an url-attribute
         * Then the redirect link contains the pageType-parameter
         * Then the url-attribute contains the absolute link to the given pageUid
         * Then no pageType-parameter is added to the rendered link itself
         * Then no cHash is used
         * Then a noCache-parameter is set
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check60.xml');
        $queueMail = $this->queueMailRepository->findByIdentifier(1);
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(1);

        $this->standAloneViewHelper->setTemplate('Check60.html');
        $this->standAloneViewHelper->assign('queueMail', $queueMail);
        $this->standAloneViewHelper->assign('queueRecipient', $queueRecipient);

        $result = $this->standAloneViewHelper->render();

        self::assertStringContainsString('http://www.rkw-kompetenzzentrum.rkw.local/umleitungsseite-der-umleitungen/rkw-mailer/redirect/1/1/?', $result);
        self::assertStringContainsString('tx_rkwmailer_rkwmailer%5Burl%5D=http%3A%2F%2Fwww.rkw-kompetenzzentrum.rkw.local%2Ftest%2Frkw-basics%2Fmediasources%2Fprint%2F', $result);
        self::assertStringNotContainsString('cHash=', $result);
        self::assertStringNotContainsString('type=98', $result);
        self::assertStringContainsString('no_cache=1', $result);


    }

    //=============================================

    /**
     * TearDown
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }

}
