<?php
namespace RKW\RkwMailer\Tests\Integration\Utility;


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
use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwMailer\Domain\Model\MailingStatistics;
use RKW\RkwMailer\Domain\Model\QueueMail;
use RKW\RkwMailer\Utility\QueueMailUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * QueueMailUtilityTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class QueueMailUtilityTest extends FunctionalTestCase
{


    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/QueueMailUtilityTest/Fixtures';

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_mailer',
        'typo3conf/ext/rkw_registration',
    ];

    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [ ];

    /**
     * @var \RKW\RkwMailer\Utility\QueueMailUtility
     */
    private $subject;

    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp()
    {
       
        parent::setUp();

        $this->importDataSet(self::FIXTURE_PATH .  '/Database/Global.xml');
        $this->setUpFrontendRootPage(
            100,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_registration/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        $this->subject = GeneralUtility::makeInstance(QueueMailUtility::class);

    }


    //=============================================

    

    /**
     * @test
     * @throws \Exception
     */
    public function initQueueMailReturnsQueueMailObject()
    {
        /**
         * Scenario:
         *
         * When the method is called
         * Then a QueueMail-object is returned
         */
        
        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $result */
        $result = $this->subject->initQueueMail();
        self::assertInstanceOf(QueueMail::class, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function initQueueMailSetsDefaultAddresses()
    {
        /**
         * Scenario:
         *
         * Given all TYPO3_CONF_VARS for the mail-configuration are set
         * When the method is called
         * Then a QueueMail-object is returned
         * Then the fromName-property is set accordingly
         * Then the fromAddress-property is set accordingly
         *  hen the replyToName-property is set accordingly
         * Then the replyToAddress-property is set accordingly
         * Then the returnPath-property is set accordingly
         */

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'RKW';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'service@mein.rkw.de';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailReplyToName'] = 'RKW Reply';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailReplyToAddress'] = 'reply@mein.rkw.de';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailReturnAddress'] = 'bounces@mein.rkw.de';

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $result */
        $result = $this->subject->initQueueMail();
        self::assertInstanceOf(QueueMail::class, $result);
        self::assertEquals('RKW', $result->getFromName());
        self::assertEquals('service@mein.rkw.de', $result->getFromAddress());
        self::assertEquals('RKW Reply', $result->getReplyToName());
        self::assertEquals('reply@mein.rkw.de', $result->getReplyToAddress());
        self::assertEquals('bounces@mein.rkw.de', $result->getReturnPath());

        
    }

    /**
     * @test
     * @throws \Exception
     */
    public function initQueueMailSetsDefaultAddressesWithFallbacks()
    {
        /**
         * Scenario:
         *
         * Given some TYPO3_CONF_VARS for the mail-configuration are set
         * Given the defaultMailFromName-key and the defaultMailFromAddress-key are set in TYPO3_CONF_VARS
         * Given no defaultMailReplyToAddress-key and no defaultMailReturnAddress-key is set in TYPO3_CONF_VARS
         * When the method is called
         * Then a QueueMail-object is returned
         * Then the fromName-property is set accordingly
         * Then the fromAddress-property is set accordingly
         * Then as value for the replyToName-property the defaultFromName is used
         * Then as value for the replyToAddress-property the defaultMailFromAddress is used
         * Then as value for the returnPath-property the defaultMailFromAddress is used
         */

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'RKW';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'service@mein.rkw.de';

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $result */
        $result = $this->subject->initQueueMail();
        self::assertInstanceOf(QueueMail::class, $result);
        self::assertEquals('RKW', $result->getFromName());
        self::assertEquals('service@mein.rkw.de', $result->getFromAddress());
        self::assertEquals('RKW', $result->getReplyToName());
        self::assertEquals('service@mein.rkw.de', $result->getReplyToAddress());
        self::assertEquals('service@mein.rkw.de', $result->getReturnPath());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function initQueueMailSetsSettingsPidToPageUid()
    {
        /**
         * Scenario:
         *
         * Given a page is loaded in frontend context
         * When the method is called
         * Then a QueueMail-object is returned
         * Then settingsPid-property is set to the uid of the page loaded in frontend context
         */

        FrontendSimulatorUtility::simulateFrontendEnvironment(100);
       
        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $result */
        $result = $this->subject->initQueueMail();
        self::assertInstanceOf(QueueMail::class, $result);
        self::assertEquals(100, $result->getSettingsPid());

        FrontendSimulatorUtility::resetFrontendEnvironment();

    }


    /**
     * @test
     * @throws \Exception
     */
    public function initQueueMailSetsStoragePid()
    {
        /**
         * Scenario:
         *
         * Given a storagePid as paramater
         * When the method is called
         * Then a QueueMail-object is returned
         * Then pid-property is set to the storagePid given
         */
        
        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $result */
        $result = $this->subject->initQueueMail(987);
        self::assertInstanceOf(QueueMail::class, $result);
        self::assertEquals(987, $result->getPid());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function initQueueMailSetsStatusToDraft()
    {
        /**
         * Scenario:
         *
         * When the method is called
         * Then a QueueMail-object is returned
         * Then status-property is set to the value one
         */
        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $result */
        $result = $this->subject->initQueueMail();
        self::assertInstanceOf(QueueMail::class, $result);
        self::assertEquals(1, $result->getStatus());

    }
    
    
    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

}