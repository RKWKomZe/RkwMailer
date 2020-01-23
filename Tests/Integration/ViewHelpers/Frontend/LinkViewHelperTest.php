<?php
namespace RKW\RkwMailer\Tests\Integration\ViewHelpers\Frontend;

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
 * LinkViewHelperTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class LinkViewHelperTest extends FunctionalTestCase
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
        define('TX_REALURL_AUTOCONF_FILE', 'typo3conf/ext/rkw_mailer/Tests/Integration/ViewHelpers/Frontend/LinkViewHelperTest/Fixtures/RealUrlConfiguration.php');

        parent::setUp();

        $this->importDataSet(__DIR__ . '/LinkViewHelperTest/Fixtures/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:realurl/Configuration/TypoScript/setup.txt',
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Tests/Integration/ViewHelpers/Frontend/LinkViewHelperTest/Fixtures/Frontend/Configuration/Rootpage.typoscript',
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
                0 => __DIR__ . '/LinkViewHelperTest/Fixtures/Frontend/Templates'
            ]
        );


    }


    /**
     * @test
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersAbsoluteLink ()
    {

        /**
        * Scenario:
        *
        * Given the ViewHelper is used in a template
        * When the link is rendered
        * Then an absolute link is returned
        */

        $this->standAloneViewHelper->setTemplate('Check10.html');
        $result = str_replace("\n", '', $this->standAloneViewHelper->render());

        static::assertEquals('http://www.rkw-kompetenzzentrum.rkw.local/tx-rkw-basics/media/list/', $result);
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersAbsoluteLinkToGivenPage ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a pageUid is set
         * When the link is rendered
         * Then an absolute link to this given pageUid is returned
         */
        $this->importDataSet(__DIR__ . '/LinkViewHelperTest/Fixtures/Database/Check20.xml');

        $this->standAloneViewHelper->setTemplate('Check20.html');
        $result = str_replace("\n", '', $this->standAloneViewHelper->render());

        static::assertEquals('http://www.rkw-kompetenzzentrum.rkw.local/test/tx-rkw-basics/media/list/', $result);
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersAbsoluteLinkWithQueueMailAndRedirect ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a pageUid is set
         * Given a queueMail is set
         * When the link is rendered
         * Then an absolute link to the redirect page is generated
         * Then the redirect link calls the redirect plugin of rkw_mailer
         * Then the redirect link contains the queueMailUid
         * Then the redirect link contains a hash-value
         */
        $this->importDataSet(__DIR__ . '/LinkViewHelperTest/Fixtures/Database/Check30.xml');
        $queueMail = $this->queueMailRepository->findByIdentifier(1);

        $this->standAloneViewHelper->setTemplate('Check30.html');
        $this->standAloneViewHelper->assign('queueMail', $queueMail);

        $result = str_replace("\n", '', $this->standAloneViewHelper->render());

        static::assertStringStartsWith('http://www.rkw-kompetenzzentrum.rkw.local/umleitungsseite-der-umleitungen/?', $result);
        static::assertContains('&tx_rkwmailer_rkwmailer%5Baction%5D=redirect&tx_rkwmailer_rkwmailer%5Bcontroller%5D=Link', $result);
        static::assertContains('tx_rkwmailer_rkwmailer%5Bmid%5D=1', $result);
        static::assertContains('tx_rkwmailer_rkwmailer%5Bhash%5D=e442b5ecbe646ecad7cbfa2727d255392ab7be71', $result);

    }

    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersAbsoluteLinkWithQueueRecipientRedirect ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a pageUid is set
         * Given a queueMail is set
         * Given a queueRecipient is set
         * When the link is rendered
         * Then an absolute link to the redirect page is generated
         * Then the redirect link calls the redirect plugin of rkw_mailer
         * Then the redirect link contains the queueMailUid
         * Then the redirect link contains a hash-value
         */
        $this->importDataSet(__DIR__ . '/LinkViewHelperTest/Fixtures/Database/Check30.xml');
        $queueMail = $this->queueMailRepository->findByIdentifier(1);
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(1);


        $this->standAloneViewHelper->setTemplate('Check30.html');
        $this->standAloneViewHelper->assign('queueMail', $queueMail);
        $this->standAloneViewHelper->assign('queueRecipient', $queueRecipient);


        $result = str_replace("\n", '', $this->standAloneViewHelper->render());

        static::assertStringStartsWith('http://www.rkw-kompetenzzentrum.rkw.local/umleitungsseite-der-umleitungen/?', $result);
        static::assertContains('&tx_rkwmailer_rkwmailer%5Baction%5D=redirect&tx_rkwmailer_rkwmailer%5Bcontroller%5D=Link', $result);
        static::assertContains('tx_rkwmailer_rkwmailer%5Bmid%5D=1', $result);
        static::assertContains('tx_rkwmailer_rkwmailer%5Buid%5D=1', $result);
        static::assertContains('tx_rkwmailer_rkwmailer%5Bhash%5D=e442b5ecbe646ecad7cbfa2727d255392ab7be71', $result);

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