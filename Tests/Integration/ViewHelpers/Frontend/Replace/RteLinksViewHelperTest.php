<?php
namespace RKW\RkwMailer\Tests\Integration\ViewHelpers\Frontend\Replace;

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
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;


/**
 * RteLinksViewHelperTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RteLinksViewHelperTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/RteLinksViewHelperTest/Fixtures';


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
        define('TX_REALURL_AUTOCONF_FILE', 'typo3conf/ext/rkw_mailer/Tests/Integration/ViewHelpers/Frontend/Replace/RteLinksViewHelperTest/Fixtures/RealUrlConfiguration.php');

        parent::setUp();

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:realurl/Configuration/TypoScript/setup.txt',
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->standAloneViewHelper = $this->objectManager->get(StandaloneView::class);
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
    public function itRendersLinks ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given we have a plaintext section
         * Given we have a html-section
         * Given each section has a part with the old link-tag for typolink
         * Given each section has a part with the new a-tag for typolink
         * Given each section has an additional style-attribute given
         * Given each section contains links to existing internal pages
         * Given each section contains links to external pages
         * Given each section contains links to existing files
         * When the ViewHelper is rendered
         * Then all links are rendered with absolute urls
         * Then the plaintext-links are rendered without settings from the additional parameters
         * Then the plaintext-links are enclosed by brackets and the link text is placed before the link
         * Then the html-links include all additional parameters given as attributes
         * Then the existing style-attribute of the html-links using the old link-tag for typolink is replaced by the styles given via viewHelper-attribute
         * Then the existing style-attribute of the html-links using the new a-tag for typolink is extended by the styles given via viewHelper-attribute
         */

        $this->standAloneViewHelper->setTemplate('Check10.html');

        $expected = file_get_contents(self::FIXTURE_PATH . '/Expected/Check10.txt');
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