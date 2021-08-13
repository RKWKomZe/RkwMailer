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
use RKW\RkwMailer\Utility\FrontendTypolinkUtility;

/**
 * FrontendTypolinkUtilityTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendTypolinkUtilityTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/FrontendTypolinkUtilityTest/Fixtures';

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
        define('TX_REALURL_AUTOCONF_FILE', 'typo3conf/ext/rkw_mailer/Tests/Integration/Utility/FrontendTypolinkUtilityTest/Fixtures/RealUrlConfiguration.php');

        parent::setUp();

        $this->importDataSet(self::FIXTURE_PATH .  '/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getPageUidFromParameterGetsPidFromOldTypolinkStyle()
    {
        /**
         * Scenario:
         *
         * Given a typolink parameter beginning with a pageUid
         * When the method is called
         * Then this pageUid is returned
         */
        $result = FrontendTypolinkUtility::getPageUidFromParameter('9999 _blank test Titel');
        self::assertEquals(9999, $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getPageUidFromParameterGetsPidFromOldTypolinkStyleWithAnchor()
    {
        /**
         * Scenario:
         *
         * Given a typolink parameter beginning with a pageUid
         * Given an anchor-id follows
         * When the method is called
         * Then this pageUid is returned
         */
        $result = FrontendTypolinkUtility::getPageUidFromParameter('9999#anchor1 _blank test Titel');
        self::assertEquals(9999, $result);
    }
    

    /**
     * @test
     * @throws \Exception
     */
    public function getPageUidFromParameterGetsPidFromNewTypolinkStyle()
    {
        /**
         * Scenario:
         *
         * Given a typolink parameter beginning with t3-prefix 
         * When the method is called
         * Then this pageUid is returned
         */
        $result = FrontendTypolinkUtility::getPageUidFromParameter('t3://page?uid=9999 _blank test Titel');
        self::assertEquals(9999, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getPageUidFromParameterGetsPidFromNewTypolinkStyleWithAnchor()
    {
        /**
         * Scenario:
         *
         * Given a typolink parameter beginning with t3-prefix
         * Given an anchor-id follows
         * When the method is called
         * Then this pageUid is returned
         */
        $result = FrontendTypolinkUtility::getPageUidFromParameter('t3://page?uid=9999#anchor1 _blank test Titel');
        self::assertEquals(9999, $result);
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
        $result = FrontendTypolinkUtility::addStyleAttribute(' target="blank" rel="nofollow" ', 'color:red');
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
        $result = FrontendTypolinkUtility::addStyleAttribute(' target="blank" rel="nofollow" style="font-family:Arial"', 'color:red');
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
        $result = FrontendTypolinkUtility::addStyleAttribute(' target="blank" rel="nofollow" style="font-family:Arial;"', 'color:red');
        self::assertEquals('target="blank" rel="nofollow" style="font-family:Arial; color:red"', $result);
    }

    //=============================================
    /**
     * @test
     * @throws \Exception
     */
    public function getTypolinkUrlReturnsAbsoluteUrlToPageForOldStyleParameter()
    {
        /**
         * Scenario:
         *
         * Given a typolink to an existing internal site in old-typolink-style
         * When the method is called
         * Then an absolute url to the internal site is returned
         */
        $result = FrontendTypolinkUtility::getTypolinkUrl('9999 _blank test Titel');
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
         * Given a typolink to an existing internal site in new-typolink-style
         * When the method is called
         * Then an absolute url to the internal site is returned
         */
        $result = FrontendTypolinkUtility::getTypolinkUrl('t3://page?uid=9999 _blank test Titel');
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
         * Given a typolink to an external site 
         * When the method is called
         * Then an absolute url to the external site is returned
         */
        $result = FrontendTypolinkUtility::getTypolinkUrl('http://www.google.de _blank test Titel');
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
         * Given a typolink to an existing file
         * When the method is called
         * Then an absolute url to the file is returned
         */
        $result = FrontendTypolinkUtility::getTypolinkUrl('file:999 _blank test Titel');
        self::assertEquals('http://www.rkw-kompetenzzentrum.rkw.local/fileadmin/test.pdf', $result);
    }

    //=============================================
    /**
     * @test
     * @throws \Exception
     */
    public function getTypolinkReturnsTagsWithAbsoluteUrlToPageForOldStyleParameter()
    {
        /**
         * Scenario:
         *
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
        $result = FrontendTypolinkUtility::getTypolink('testen', '9999 _self test Titel', '', 'color:red');
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
        $result = FrontendTypolinkUtility::getTypolink('testen', 't3://page?uid=9999 _self test Titel', '', 'color:red');
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
        $result = FrontendTypolinkUtility::getTypolink('testen','http://www.google.de _self test Titel', '', 'color:red');
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
        $result = FrontendTypolinkUtility::getTypolink('testen','file:999 _self test Titel', '', 'color:red');
        self::assertEquals('<a href="http://www.rkw-kompetenzzentrum.rkw.local/fileadmin/test.pdf" title="Titel" target="_self" class="test" style="color:red">testen</a>', $result);
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