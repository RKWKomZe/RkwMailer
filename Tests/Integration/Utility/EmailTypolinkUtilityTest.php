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
use RKW\RkwMailer\Exception;
use RKW\RkwMailer\Utility\EmailTypolinkUtility;

/**
 * EmailTypolinkUtilityTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class EmailTypolinkUtilityTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/EmailTypolinkUtilityTest/Fixtures';

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
     * Setup
     * @throws \Exception
     */
    protected function setUp()
    {
        // define realUrl-config
        define('TX_REALURL_AUTOCONF_FILE', 'typo3conf/ext/rkw_mailer/Tests/Integration/Utility/EmailTypolinkUtilityTest/Fixtures/RealUrlConfiguration.php');

        parent::setUp();

        $this->importDataSet(self::FIXTURE_PATH .  '/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        FrontendSimulatorUtility::simulateFrontendEnvironment(1);
    }

    //=============================================

    
    /**
     * @test
     * @throws \Exception
     */
    public function addStyleAttributeAddsAttribute()
    {
        /**
         * Scenario:
         *
         * Given some a-tag-attributes without a style-attribute
         * Given some styles as parameter
         * When the method is called
         * Then the given styles are added to the existing attributes as style-attribute
         * Then trailing and leading spaces are removed
         */
        $result = EmailTypolinkUtility::addStyleAttribute(' target="blank" rel="nofollow" ', 'color:red');
        self::assertEquals('target="blank" rel="nofollow" style="color:red"', $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function addStyleAttributeAddsStylesToExistingAttribute()
    {
        /**
         * Scenario:
         *
         * Given some a-tag-attributes a style-attribute
         * Given some styles as parameter
         * When the method is called
         * Then the given styles are added to the existing style-attribute
         * Then a colon is added between the existing and the new styles
         * Then trailing and leading spaces are removed
         */
        $result = EmailTypolinkUtility::addStyleAttribute(' target="blank" rel="nofollow" style="font-family:Arial"', 'color:red');
        self::assertEquals('target="blank" rel="nofollow" style="font-family:Arial; color:red"', $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function addStyleAttributeAddsStylesToExistingAttributeWithoutTrailingColon()
    {
        /**
         * Scenario:
         *
         * Given some a-tag-attributes a style-attribute
         * Given some styles as parameter
         * When the method is called
         * Then the given styles are added to the existing style-attribute
         * Then there is no double colon between the existing and the new styles
         * Then trailing and leading spaces are removed
         */
        $result = EmailTypolinkUtility::addStyleAttribute(' target="blank" rel="nofollow" style="font-family:Arial;"', 'color:red');
        self::assertEquals('target="blank" rel="nofollow" style="font-family:Arial; color:red"', $result);
    }

    //=============================================
    /**
     * @test
     * @throws \Exception
     */
    public function getTypolinkUrlThrowsException()
    {
        /**
         * Scenario:
         *
         * Given no frontend is instantiated
         * Given a typolink to an existing internal site in old-typolink-style
         * When the method is called
         * Then an exception of instance \RKW\RkwMailer\Exception is thrown
         * Then this exception has the code 1652102609
         */
       
        FrontendSimulatorUtility::resetFrontendEnvironment();
        
        self::expectException(Exception::class);
        self::expectExceptionCode(1652102609);

        EmailTypolinkUtility::getTypolinkUrl('9999 _blank test Titel');

    }
    
    /**
     * @test
     * @throws \Exception
     */
    public function getTypolinkUrlReturnsAbsoluteUrlToPageForOldStyleParameter()
    {
        /**
         * Scenario:
         *
         * Given the frontend is instantiated
         * Given a typolink to an existing internal site in old-typolink-style
         * When the method is called
         * Then an absolute url to the internal site is returned
         */
        
        $result = EmailTypolinkUtility::getTypolinkUrl('9999 _blank test Titel');
        self::assertEquals('http://www.rkw-kompetenzzentrum.rkw.local/testseite/', $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getTypolinkUrlReturnsAbsoluteUrlToPageForNewStyleParameter()
    {
        /**
         * Scenario:
         *
         * Given the frontend is instantiated
         * Given a typolink to an existing internal site in new-typolink-style
         * When the method is called
         * Then an absolute url to the internal site is returned
         */
        
        $result = EmailTypolinkUtility::getTypolinkUrl('t3://page?uid=9999 _blank test Titel');
        self::assertEquals('http://www.rkw-kompetenzzentrum.rkw.local/testseite/', $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getTypolinkUrlReturnsAbsoluteUrlToExternalWebsite()
    {
        /**
         * Scenario:
         *
         * Given the frontend is instantiated
         * Given a typolink to an external site 
         * When the method is called
         * Then an absolute url to the external site is returned
         */
        
        $result = EmailTypolinkUtility::getTypolinkUrl('http://www.google.de _blank test Titel');
        self::assertEquals('http://www.google.de', $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getTypolinkUrlReturnsAbsoluteUrlToFile()
    {
        /**
         * Scenario:
         *
         * Given the frontend is instantiated
         * Given a typolink to an existing file
         * When the method is called
         * Then an absolute url to the file is returned
         */
        
        $result = EmailTypolinkUtility::getTypolinkUrl('file:999 _blank test Titel');
        self::assertEquals('http://www.rkw-kompetenzzentrum.rkw.local/fileadmin/test.pdf', $result);
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getTypolinkThrowsException()
    {
        /**
         * Scenario:
         *
         * Given no frontend is instantiated
         * Given a typolink to an existing internal site in old-typolink-style
         * Given an additional style-parameter
         * When the method is called
         * Then an exception of instance \RKW\RkwMailer\Exception is thrown
         * Then this exception has the code 1652102610
         */

        FrontendSimulatorUtility::resetFrontendEnvironment();
        
        self::expectException(Exception::class);
        self::expectExceptionCode(1652102610);

        EmailTypolinkUtility::getTypolink('testen', 't3://page?uid=9999 _self test Titel', '', 'color:red');

    }
    
    
    /**
     * @test
     * @throws \Exception
     */
    public function getTypolinkReturnsTagsWithAbsoluteUrlToPageForOldStyleParameter()
    {
        /**
         * Scenario:
         *
         * Given the frontend is instantiated
         * Given a typolink to an existing internal site in old-typolink-style
         * Given an additional style-parameter
         * When the method is called
         * Then an a-tag with absolute url to the internal site is returned
         * Then the target-attribute is set according to typolink
         * Then the title-attribute is set according to typolink
         * Then the class-attribute is set according to typolink
         * Then a styles-attribute is set according to the given styles
         * Then the given link-text is used
         */
        
        $result = EmailTypolinkUtility::getTypolink('testen', '9999 _self test Titel', '', 'color:red');
        self::assertEquals('<a href="http://www.rkw-kompetenzzentrum.rkw.local/testseite/" title="Titel" target="_self" class="test" style="color:red">testen</a>', $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getTypolinkReturnsTagsWithAbsoluteUrlToPageForNewStyleParameter()
    {
        /**
         * Scenario:
         *
         * Given the frontend is instantiated
         * Given a typolink to an existing internal site in new-typolink-style
         * Given an additional style-parameter
         * When the method is called
         * Then an a-tag with absolute url to the internal site is returned
         * Then the target-attribute is set according to typolink
         * Then the title-attribute is set according to typolink
         * Then the class-attribute is set according to typolink
         * Then a styles-attribute is set according to the given styles
         * Then the given link-text is used
         */
        
        $result = EmailTypolinkUtility::getTypolink('testen', 't3://page?uid=9999 _self test Titel', '', 'color:red');
        self::assertEquals('<a href="http://www.rkw-kompetenzzentrum.rkw.local/testseite/" title="Titel" target="_self" class="test" style="color:red">testen</a>', $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getTypolinkReturnsTagsWithAbsoluteUrlToExternalWebsite()
    {
        /**
         * Scenario:
         *
         * Given the frontend is instantiated
         * Given a typolink to an external site
         * Given an additional style-parameter
         * When the method is called
         * Then an a-tag with absolute url to the external site is returned
         * Then the target-attribute is set according to typolink
         * Then the title-attribute is set according to typolink
         * Then the class-attribute is set according to typolink
         * Then a styles-attribute is set according to the given styles
         * Then the given link-text is used
         */
        
        $result = EmailTypolinkUtility::getTypolink('testen','http://www.google.de _self test Titel', '', 'color:red');
        self::assertEquals('<a href="http://www.google.de" title="Titel" target="_self" class="test" style="color:red">testen</a>', $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getTypolinkReturnsTagsWithAbsoluteUrlToFile()
    {
        /**
         * Scenario:
         *
         * Given the frontend is instantiated
         * Given a typolink to an existing file
         * Given an additional style-parameter
         * When the method is called
         * Then an a-tag with absolute url to the file is returned
         * Then the target-attribute is set according to typolink
         * Then the title-attribute is set according to typolink
         * Then the class-attribute is set according to typolink
         * Then a styles-attribute is set according to the given styles
         * Then the given link-text is used
         */
        
        $result = EmailTypolinkUtility::getTypolink('testen','file:999 _self test Titel', '', 'color:red');
        self::assertEquals('<a href="http://www.rkw-kompetenzzentrum.rkw.local/fileadmin/test.pdf" title="Titel" target="_self" class="test" style="color:red">testen</a>', $result);
    }
    
    //=============================================

    /**
     * TearDown
     */
    protected function tearDown()
    {
        FrontendSimulatorUtility::resetFrontendEnvironment();
        parent::tearDown();
    }

}