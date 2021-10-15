<?php
namespace RKW\RkwMailer\Tests\Integration\View;

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
use RKW\RkwBasics\Domain\Repository\PagesRepository;
use RKW\RkwMailer\Domain\Model\QueueMail;
use RKW\RkwMailer\Domain\Model\QueueRecipient;
use RKW\RkwMailer\View\MailStandaloneView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;


/**
 * MailStandaloneViewTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MailStandaloneViewTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/MailStandaloneViewTest/Fixtures';


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
     * @var \RKW\RkwMailer\View\MailStandaloneView
     */
    private $subject;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager;


    /**
     * @var \RKW\RkwBasics\Domain\Repository\PagesRepository
     */
    private $pagesRepository;

    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp()
    {

        parent::setUp();

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailerConfiguration/TypoScript/setup.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $this->objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->subject = $this->objectManager->get(MailStandaloneView::class);
        $this->pagesRepository = $this->objectManager->get(PagesRepository::class);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function constructLoadsConfigurationOfGivenPage ()
    {

        /**
        * Scenario:
        *
        * Given a rootpage with the configuration for the mailer extension
        * Given this rootpage has a subpage
        * Given this subpage as parameter
        * When the object is instanced
        * Then a configuration array is loaded
        * Then the three configuration types of the rootpage of the given subpage are loaded
        */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');
        $this->setUpFrontendRootPage(
            10,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailerConfiguration/TypoScript/setup.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check10.typoscript',
            ]
        );

        $this->subject = $this->objectManager->get(MailStandaloneView::class, 11);
        $settings = $this->subject->getSettings();

        static::assertInternalType(
            'array',
            $settings
        );

        static::assertEquals(
            'EXT:rkw_mailer/Tests/Functional/Service/Fixtures/Frontend/Check10/Layouts/',
            $settings['view']['layoutRootPaths'][1]
        );

        static::assertEquals(
            1010,
            $settings['persistence']['storagePid']
        );

        static::assertEquals(
            1010,
            $settings['settings']['redirectPid']
        );

    }

    /**
     * @test
     * @throws \Exception
     */
    public function constructLoadsFallbackConfiguration ()
    {

        /**
         * Scenario:
         *
         * Given a rootpage with the configuration for the mailer extension
         * Given no pid is passed as argument
         * When the object is instanced
         * Then a configuration array is loaded
         * Then a three configuration types of the rootpage of the default page are loaded
         */

        $this->subject = $this->objectManager->get(MailStandaloneView::class);
        $settings = $this->subject->getSettings();

        static::assertInternalType(
            'array',
            $settings
        );

        static::assertEquals(
            'EXT:rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Layouts/',
            $settings['view']['layoutRootPaths'][1]
        );

        static::assertEquals(
            9999,
            $settings['persistence']['storagePid']
        );

        static::assertEquals(
            9999,
            $settings['settings']['redirectPid']
        );

    }

    /**
     * @test
     * @throws \Exception
     */
    public function constructSetsSettingsPidToGivenPage ()
    {

        /**
         * Scenario:
         *
         * Given a rootpage with the configuration for the mailer extension
         * Given this rootpage has a subpage
         * Given this subpage as parameter
         * When the object is instanced
         * Then the given pid is set as settingsPid
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');
        $this->setUpFrontendRootPage(
            10,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailerConfiguration/TypoScript/setup.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check10.typoscript',
            ]
        );

        $this->subject = $this->objectManager->get(MailStandaloneView::class, 11);

        static::assertEquals(11, $this->subject->getSettingsPid());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function constructSetsViewPathsAccoringToConfiguration ()
    {

        /**
         * Scenario:
         *
         * Given a rootpage with the configuration for the mailer extension
         * Given this rootpage has a subpage
         * Given this subpage as parameter
         * When the object is instanced
         * Then the layoutRootPaths are set according to configuration
         * Then the partialRootPaths are set according to configuration
         * Then the templateRootPaths are set according to configuration
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');
        $this->setUpFrontendRootPage(
            10,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailerConfiguration/TypoScript/setup.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check10.typoscript',
            ]
        );

        $this->subject = $this->objectManager->get(MailStandaloneView::class, 11);

        $expected = [
            'layout' => [
                0 => 'typo3conf/ext/rkw_mailer/Resources/Private/Layouts/',
                1 => 'typo3conf/ext/rkw_mailer/Tests/Functional/Service/Fixtures/Frontend/Check10/Layouts/'
            ],
            'partial' => [
                0 => 'typo3conf/ext/rkw_mailer/Resources/Private/Partials/',
                1 => 'typo3conf/ext/rkw_mailer/Tests/Functional/Service/Fixtures/Frontend/Check10/Partials/'
            ],
            'template' => [
                0 => 'typo3conf/ext/rkw_mailer/Resources/Private/Templates/',
                1 => 'typo3conf/ext/rkw_mailer/Tests/Functional/Service/Fixtures/Frontend/Check10/Templates/'
            ],
        ];

        $result = $this->subject->getLayoutRootPaths();
        static::assertInternalType('array', $result);
        static::assertCount(2, $result);
        static::assertStringEndsWith($expected['layout'][0], $result[0]);
        static::assertStringEndsWith($expected['layout'][1], $result[1]);

        $result = $this->subject->getPartialRootPaths();
        static::assertInternalType('array', $result);
        static::assertCount(2, $result);
        static::assertStringEndsWith($expected['partial'][0], $result[0]);
        static::assertStringEndsWith($expected['partial'][1], $result[1]);

        $result = $this->subject->getTemplateRootPaths();
        static::assertInternalType('array', $result);
        static::assertCount(2, $result);
        static::assertStringEndsWith($expected['template'][0], $result[0]);
        static::assertStringEndsWith($expected['template'][1], $result[1]);


    }
    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getRelativePathDoesNotChangeRelativePaths ()
    {

        /**
         * Scenario:
         *
         * Given a relative path without prefix
         * When the method is called
         * Then the path is returned unchanged
         * Then the trailing slash is removed
         */

        $path = 'fileadmin/stuff/Images/';
        $expected = 'fileadmin/stuff/Images';
        static::assertEquals($expected, $this->subject->getRelativePath($path));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getRelativePathWorksOnValidPrefixOnly ()
    {

        /**
         * Scenario:
         *
         * Given a path with an invalid prefix
         * When the method is called
         * Then the path is returned unchanged
         * Then the trailing slash is removed
         */
        $path = 'EXI:rkw_mailer/rkw_mailer/Resources/Public/Images/';
        $expected = 'EXI:rkw_mailer/rkw_mailer/Resources/Public/Images';
        static::assertEquals($expected, $this->subject->getRelativePath($path));
    }
    /**
     * @test
     * @throws \Exception
     */
    public function getRelativePathWorksForLoadedExtensionsOnly ()
    {

        /**
         * Scenario:
         *
         * Given a path with a valid prefix
         * Given that paths references an unloaded extension
         * When the method is called
         * Then the path is returned unchanged
         * Then the trailing slash is removed
         */
        $path = 'EXT:rkw_tester/rkw_mailer/Resources/Public/Images/';
        $expected = 'EXT:rkw_tester/rkw_mailer/Resources/Public/Images';
        static::assertEquals($expected, $this->subject->getRelativePath($path));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getRelativePathReturnsRelativePath ()
    {

        /**
         * Scenario:
         *
         * Given a path with a valid prefix
         * Given that paths references a loaded extension
         * When the method is called
         * Then the path is returned as relative path
         * Then the trailing slash is removed
         */
        $path = 'EXT:rkw_mailer/rkw_mailer/Resources/Public/Images/';
        $expected = 'typo3conf/ext/rkw_mailer/rkw_mailer/Resources/Public/Images';
        static::assertEquals($expected, $this->subject->getRelativePath($path));
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getBaseUrlReturnsBaseUrlBasedOnConfiguration ()
    {

        /**
         * Scenario:
         *
         * Given a valid configuration
         * When the method is called
         * Then a full url to the image-path is returned
         * Then the trailing slash is removed
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');
        $this->setUpFrontendRootPage(
            20,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailerConfiguration/TypoScript/setup.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check20.typoscript',
            ]
        );

        $this->subject = $this->objectManager->get(MailStandaloneView::class, 20);


        $expected = 'http://www.example.de';
        static::assertEquals($expected, $this->subject->getBaseUrl());
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getBaseUrlImagesReturnsBaseUrlForImagesBasedOnConfiguration ()
    {

        /**
         * Scenario:
         *
         * Given a valid configuration
         * When the method is called
         * Then a full url to the image-path is returned
         * Then the trailing slash is removed
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');
        $this->setUpFrontendRootPage(
            20,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailerConfiguration/TypoScript/setup.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check20.typoscript',
            ]
        );

        $this->subject = $this->objectManager->get(MailStandaloneView::class, 20);

        $expected = 'http://www.example.de/typo3conf/ext/rkw_mailer/Resources/Public/Images';
        static::assertEquals($expected, $this->subject->getBaseUrlImages());
    }


    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getLogoUrlReturnsUrlBasedOnConfiguration ()
    {

        /**
         * Scenario:
         *
         * Given a valid configuration
         * When the method is called
         * Then a full url to the logo-path is returned
         * Then the trailing slash is removed
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');
        $this->setUpFrontendRootPage(
            20,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailerConfiguration/TypoScript/setup.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check20.typoscript',
            ]
        );

        $this->subject = $this->objectManager->get(MailStandaloneView::class, 20);

        $expected = 'http://www.example.de/typo3conf/ext/rkw_mailer/Resources/Public/Images/logo.png';
        static::assertEquals($expected, $this->subject->getLogoUrl());
    }


    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function addLayoutPathsMergesGivenPathsToExistingOnes ()
    {

        /**
         * Scenario:
         *
         * Given a valid configuration with two configured layoutPaths for the view
         * Given two further layoutPaths for the view
         * When the method is called
         * Then four layoutPaths exist
         * Then the further layoutPaths are added after the existing ones
         */

        $paths = [
            0 => 'EXT:rkw_mailer/Tests/Functional/Service/New100/Layouts/',
            1 => 'EXT:rkw_mailer/Tests/Functional/Service/New200/Layouts/'
        ];

        $expected = [
            0 => 'typo3conf/ext/rkw_mailer/Resources/Private/Layouts/',
            1 => 'typo3conf/ext/rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Layouts/',
            2 => 'typo3conf/ext/rkw_mailer/Tests/Functional/Service/New100/Layouts/',
            3 => 'typo3conf/ext/rkw_mailer/Tests/Functional/Service/New200/Layouts/'
        ];

        $this->subject->addLayoutRootPaths($paths);
        $result = $this->subject->getLayoutRootPaths();

        static::assertCount(4, $result);
        static::assertStringEndsWith($expected[0], $result[0]);
        static::assertStringEndsWith($expected[1], $result[1]);
        static::assertStringEndsWith($expected[2], $result[2]);
        static::assertStringEndsWith($expected[3], $result[3]);
    }



    /**
     * @test
     * @throws \Exception
     */
    public function addLayoutPathsSetsGivenPathsWhenNoExistingOnes ()
    {

        /**
         * Scenario:
         *
         * Given no a valid configuration for layoutPaths for the view
         * Given two further layoutPaths for the view
         * When the method is called
         * Then two layoutPaths exist
         * Then the further layoutPaths are added
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');
        $this->setUpFrontendRootPage(
            30,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailerConfiguration/TypoScript/setup.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check30.typoscript',
            ]
        );

        $this->subject = $this->objectManager->get(MailStandaloneView::class, 30);

        $paths = [
            0 => 'EXT:rkw_mailer/Tests/Functional/Service/New100/Layouts/',
            1 => 'EXT:rkw_mailer/Tests/Functional/Service/New200/Layouts/'
        ];

        $expected = [
            0 => 'typo3conf/ext/rkw_mailer/Tests/Functional/Service/New100/Layouts/',
            1 => 'typo3conf/ext/rkw_mailer/Tests/Functional/Service/New200/Layouts/'
        ];

        $this->subject->addLayoutRootPaths($paths);
        $result = $this->subject->getLayoutRootPaths();

        static::assertCount(2, $result);
        static::assertStringEndsWith($expected[0], $result[0]);
        static::assertStringEndsWith($expected[1], $result[1]);

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function addPartialPathsMergesGivenPathsToExistingOnes ()
    {

        /**
         * Scenario:
         *
         * Given a valid configuration with two configured partialPaths for the view
         * Given two further partialPaths for the view
         * When the method is called
         * Then four partialPaths exist
         * Then the further partialPaths are added after the existing ones
         */

        $paths = [
            0 => 'EXT:rkw_mailer/Tests/Functional/Service/New100/Partials/',
            1 => 'EXT:rkw_mailer/Tests/Functional/Service/New200/Partials/'
        ];

        $expected = [
            0 => 'typo3conf/ext/rkw_mailer/Resources/Private/Partials/',
            1 => 'typo3conf/ext/rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Partials/',
            2 => 'typo3conf/ext/rkw_mailer/Tests/Functional/Service/New100/Partials/',
            3 => 'typo3conf/ext/rkw_mailer/Tests/Functional/Service/New200/Partials/'
        ];

        $this->subject->addPartialRootPaths($paths);
        $result = $this->subject->getPartialRootPaths();

        static::assertCount(4, $result);
        static::assertStringEndsWith($expected[0], $result[0]);
        static::assertStringEndsWith($expected[1], $result[1]);
        static::assertStringEndsWith($expected[2], $result[2]);
        static::assertStringEndsWith($expected[3], $result[3]);
    }



    /**
     * @test
     * @throws \Exception
     */
    public function addPartialPathsSetsGivenPathsWhenNoExistingOnes ()
    {

        /**
         * Scenario:
         *
         * Given no a valid configuration for partialPaths for the view
         * Given two further partialPaths for the view
         * When the method is called
         * Then two partialPaths exist
         * Then the further partialPaths are added
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');
        $this->setUpFrontendRootPage(
            30,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailerConfiguration/TypoScript/setup.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check30.typoscript',
            ]
        );

        $this->subject = $this->objectManager->get(MailStandaloneView::class, 30);

        $paths = [
            0 => 'EXT:rkw_mailer/Tests/Functional/Service/New100/Partials/',
            1 => 'EXT:rkw_mailer/Tests/Functional/Service/New200/Partials/'
        ];

        $expected = [
            0 => 'typo3conf/ext/rkw_mailer/Tests/Functional/Service/New100/Partials/',
            1 => 'typo3conf/ext/rkw_mailer/Tests/Functional/Service/New200/Partials/'
        ];

        $this->subject->addPartialRootPaths($paths);
        $result = $this->subject->getPartialRootPaths();

        static::assertCount(2, $result);
        static::assertStringEndsWith($expected[0], $result[0]);
        static::assertStringEndsWith($expected[1], $result[1]);

    }
    
    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function addTemplatePathsMergesGivenPathsToExistingOnes ()
    {

        /**
         * Scenario:
         *
         * Given a valid configuration with two configured templatePaths for the view
         * Given two further templatePaths for the view
         * When the method is called
         * Then four templatePaths exist
         * Then the further templatePaths are added after the existing ones
         */

        $paths = [
            0 => 'EXT:rkw_mailer/Tests/Functional/Service/New100/Templates/',
            1 => 'EXT:rkw_mailer/Tests/Functional/Service/New200/Templates/'
        ];

        $expected = [
            0 => 'typo3conf/ext/rkw_mailer/Resources/Private/Templates/',
            1 => 'typo3conf/ext/rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Templates/',
            2 => 'typo3conf/ext/rkw_mailer/Tests/Functional/Service/New100/Templates/',
            3 => 'typo3conf/ext/rkw_mailer/Tests/Functional/Service/New200/Templates/'
        ];

        $this->subject->addTemplateRootPaths($paths);
        $result = $this->subject->getTemplateRootPaths();

        static::assertCount(4, $result);
        static::assertStringEndsWith($expected[0], $result[0]);
        static::assertStringEndsWith($expected[1], $result[1]);
        static::assertStringEndsWith($expected[2], $result[2]);
        static::assertStringEndsWith($expected[3], $result[3]);
    }



    /**
     * @test
     * @throws \Exception
     */
    public function addTemplatePathsSetsGivenPathsWhenNoExistingOnes ()
    {

        /**
         * Scenario:
         *
         * Given no a valid configuration for templatePaths for the view
         * Given two further templatePaths for the view
         * When the method is called
         * Then two templatePaths exist
         * Then the further templatePaths are added
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');
        $this->setUpFrontendRootPage(
            30,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailerConfiguration/TypoScript/setup.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check30.typoscript',
            ]
        );

        $this->subject = $this->objectManager->get(MailStandaloneView::class, 30);

        $paths = [
            0 => 'EXT:rkw_mailer/Tests/Functional/Service/New100/Templates/',
            1 => 'EXT:rkw_mailer/Tests/Functional/Service/New200/Templates/'
        ];

        $expected = [
            0 => 'typo3conf/ext/rkw_mailer/Tests/Functional/Service/New100/Templates/',
            1 => 'typo3conf/ext/rkw_mailer/Tests/Functional/Service/New200/Templates/'
        ];

        $this->subject->addTemplateRootPaths($paths);
        $result = $this->subject->getTemplateRootPaths();

        static::assertCount(2, $result);
        static::assertStringEndsWith($expected[0], $result[0]);
        static::assertStringEndsWith($expected[1], $result[1]);

    }



    //=============================================
    /**
     * @test
     * @throws \Exception
     */
    public function setTemplateAddsTemplateAsAction ()
    {

        /**
         * Scenario:
         *
         * Given a controller action as template
         * When the method is called
         * Then no paths as added to the templateRootPaths
         * Then the controller action is set as template
         */

        $expected = $this->subject->getTemplateRootPaths();
        $this->subject->setTemplate('test');

        $resultingPaths = $this->subject->getTemplateRootPaths();
        static::assertCount(2, $resultingPaths);
        static::assertEquals($expected, $resultingPaths);
        static::assertEquals('test', $this->subject->getRenderingContext()->getControllerAction());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function setTemplateAddsTemplateAsFullPath()
    {

        /**
         * Scenario:
         *
         * Given a full path to the template beginning with EXT-keyword
         * Given no file extension is specified for the template
         * When the method is called
         * Then no paths are added to the templateRootPath
         * Then the resolved path is set for the template
         * Then the default file extension is used
         */

        $this->subject->setTemplate(
            'EXT:rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Templates/Testing/Test'
        );

        $resultingPaths = $this->subject->getTemplateRootPaths();
        static::assertCount(2, $resultingPaths);
        static::assertStringEndsWith(
            'typo3conf/ext/rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Templates/Testing/Test.html',
            $this->subject->getTemplatePathAndFilename()
        );
    }

    /**
     * @test
     * @throws \Exception
     */
    public function setTemplateAddsTemplateAsFullPathWithGivenFileExtension()
    {

        /**
         * Scenario:
         *
         * Given a full path to the template beginning with EXT-keyword
         * Given a file extension is specified for the template
         * When the method is called
         * Then no paths as added to the templateRootPath
         * Then the resolved path is set for the template
         * Then the file extension specified is used
         */

        $this->subject->setTemplate(
            'EXT:rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Templates/Testing/Test.test'
        );

        $resultingPaths = $this->subject->getTemplateRootPaths();
        static::assertCount(2, $resultingPaths);
        static::assertStringEndsWith(
            'typo3conf/ext/rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Templates/Testing/Test.test', 
            $this->subject->getTemplatePathAndFilename()
        );
    }

    /**
     * @test
     * @throws \Exception
     */
    public function setTemplateAddsTemplateRootPathsAndAction ()
    {

        /**
         * Scenario:
         *
         * Given an existing relative path as template
         * When the method is called
         * Then the given relative path is added to the templateRootPaths
         * Then the given relative path is resolved to the absolute path
         * Then the last part of the relative path is set as template
         */
        $this->subject->setTemplate('Testing/test');

        $resultingPaths = $this->subject->getTemplateRootPaths();
        static::assertCount(3, $resultingPaths);
        static::assertStringEndsWith(
            'typo3conf/ext/rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Templates/Testing/', 
            $resultingPaths[2]
        );
        static::assertEquals('test', $this->subject->getRenderingContext()->getControllerAction());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function setTemplateAddsNoTemplateRootPathsAndActionOnNonExtistingPath ()
    {

        /**
         * Scenario:
         *
         * Given an non-existing relative path as template
         * When the method is called
         * Then the given nothing is added to the templateRootPaths
         * Then the last part of the relative path is set as template
         */
        $this->subject->setTemplate('Testingxyz/test');

        $resultingPaths = $this->subject->getTemplateRootPaths();
        static::assertCount(2, $resultingPaths);
        static::assertEquals('test', $this->subject->getRenderingContext()->getControllerAction());
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function setTemplateTypesThrowsExceptionIfNoQueueMailSet ()
    {

        /**
         * Scenario:
         *
         * Given a valid type-string
         * Given no queueMail is set
         * When the method is called
         * Then an exception is thrown
         * Then the exception has the code 1633088149
         */
        static::expectException(\RKW\RkwMailer\Exception::class);
        static::expectExceptionCode(1633088149);

        $this->subject->setTemplateType('plaintext');
    }

    
    /**
     * @test
     * @throws \Exception
     */
    public function setTemplateTypesThrowsExceptionIfInvalidType ()
    {

        /**
         * Scenario:
         *
         * Given a queueMail-object has been set to subject  before
         * Given an invalid type-string
         * When the method is called
         * Then an exception is thrown
         * Then the exception has the code 1633088157
         */
        static::expectException(\RKW\RkwMailer\Exception::class);
        static::expectExceptionCode(1633088157);
        
        $queueMail = new QueueMail();
        $this->subject->setQueueMail($queueMail);
        $this->subject->setTemplateType('test');
    }

    
    /**
     * @test
     * @throws \Exception
     */
    public function setTemplateTypesSetsTemplateAndTypeProperty ()
    {

        /**
         * Scenario:
         *
         * Given a queueMail-object has been set to subject before
         * Given the queueMail-object contains a full path to the template beginning with EXT-keyword in the plaintextTemplate-attribute
         * Given in that full path no file-extension is specified for the template
         * Given the valid type-string with value "plaintext"
         * When the method is called
         * Then no paths are added to the templateRootPath
         * Then the resolved path is set as the template
         * Then the default file-extension is used
         * Then the templateType-property of subject is set to the given type-string
         * Then the templateType-property is transformed to lower case letters only
         */
        $queueMail = new QueueMail();
        $queueMail->setPlaintextTemplate(
            'EXT:rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Templates/Testing/Test'
        );
        
        $this->subject->setQueueMail($queueMail);
        $this->subject->setTemplateType('plaInTeXt');

        $resultingPaths = $this->subject->getTemplateRootPaths();
        static::assertCount(2, $resultingPaths);
        static::assertStringEndsWith(
            'typo3conf/ext/rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Templates/Testing/Test.html', 
            $this->subject->getTemplatePathAndFilename()
        );
        static::assertEquals('plaintext', $this->subject->getTemplateType());

    }
    
    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function assignMultipleExplodesValues()
    {

        /**
         * Scenario:
         *
         * Given an array of values
         * Given that array contains a key with a string
         * Given that array contains a second key with a Page-Object
         * When the method is called
         * Then the Page-Object in the values is exploded
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwBasics\Domain\Model\Pages $entityOne */
        $entityOne = $this->pagesRepository->findByIdentifier(1);
        $expected = [
            'hello' => 'string',
            'page' => $entityOne
        ];
        
        $values = [
            'hello' => 'string',
            'page' => 'RKW_MAILER_NAMESPACES RKW\RkwBasics\Domain\Model\Pages:1'
        ];
         
        $this->subject->assignMultiple($values);

        $variableProvider = $this->subject->getRenderingContext()->getVariableProvider();
        $variables = $variableProvider->getAll();
        unset($variables['settings']);
        
        self::assertEquals($expected, $variables);
        
    }

    /**
     * @test
     * @throws \Exception
     */
    public function assignMultipleAddsSettings()
    {

        /**
         * Scenario:
         *
         * Given an array of values
         * Given that array contains no settings key
         * When the method is called
         * Then the existing keys are kept 
         * Then a settings-key is added to the values
         * Then this settings-key contains an array
         * Then this array equals the normal settings loaded for the view
         */

        $values = [
            'hello' => 'string',
            'page' => 'RKW_MAILER_NAMESPACES RKW\RkwBasics\Domain\Model\Pages:1'
        ];

        $this->subject->assignMultiple($values);

        $variableProvider = $this->subject->getRenderingContext()->getVariableProvider();
        $variables = $variableProvider->getAll();
        
        $settings = $this->subject->getSettings();

        self::assertArrayHasKey('hello', $variables);
        self::assertArrayHasKey('page', $variables);
        self::assertArrayHasKey('settings', $variables);
        self::assertInternalType('array', $variables['settings']);
        self::assertEquals($settings['settings'], $variables['settings']);
        
    }

    /**
     * @test
     * @throws \Exception
     */
    public function assignMultipleAddsQueueMail()
    {

        /**
         * Scenario:
         *
         * Given an array of values
         * Given that array contains no queueMail-key
         * Given a queueMail-object has been set to the subject via setQueueMail() before
         * When the method is called
         * Then the existing keys are kept 
         * Then a queueMail-key is added to the values
         * Then this queueMail-key contains an queueMail-object
         */

        $values = [
            'hello' => 'string',
        ];
        
        $queueMail = new QueueMail();
        $this->subject->setQueueMail($queueMail);
        
        $this->subject->assignMultiple($values);

        $variableProvider = $this->subject->getRenderingContext()->getVariableProvider();
        $variables = $variableProvider->getAll();

        self::assertArrayHasKey('hello', $variables);
        self::assertArrayHasKey('queueMail', $variables);
        self::assertInstanceOf(QueueMail::class , $variables['queueMail']);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function assignMultipleAddsQueueRecipient()
    {

        /**
         * Scenario:
         *
         * Given an array of values
         * Given that array contains no queueRecipient-key
         * Given a queueRecipient-object has been set to the subject via setQueueRecipient() before
         * When the method is called
         * Then the existing keys are kept
         * Then a queueRecipient-key is added to the values
         * Then this queueRecipient-key contains an queueMail-object
         */

        $values = [
            'hello' => 'string',
        ];

        $queueRecipient = new QueueRecipient();
        $this->subject->setQueueRecipient($queueRecipient);

        $this->subject->assignMultiple($values);

        $variableProvider = $this->subject->getRenderingContext()->getVariableProvider();
        $variables = $variableProvider->getAll();

        self::assertArrayHasKey('hello', $variables);
        self::assertArrayHasKey('queueRecipient', $variables);
        self::assertInstanceOf(QueueRecipient::class , $variables['queueRecipient']);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function assignMultipleAddsMailTypeAndTemplateType()
    {

        /**
         * Scenario:
         *
         * Given an array of values
         * Given that array contains no mailType-key
         * Given that array contains no templateType-key
         * Given a queueMail-object has been set to the subject via setQueueMail() before
         * Given setTemplateType() of the subject has been called before successfully
         * When the method is called
         * Then the existing keys are kept
         * Then a mailType-key is added to the values
         * Then this mailType-key contains the type that was given to setTemplateType()
         * Then the first letter of the mailType-key is uppercase
         * Then a templateType-key is added to the values
         * Then this templateType-key contains the type that was given to setTemplateType()
         * Then the first letter of the templateType-key is uppercase
         */
        $values = [
            'hello' => 'string',
        ];

        $queueMail = new QueueMail();
        $this->subject->setQueueMail($queueMail);
        $this->subject->setTemplateType('plaintext');
        $this->subject->assignMultiple($values);

        $variableProvider = $this->subject->getRenderingContext()->getVariableProvider();
        $variables = $variableProvider->getAll();

        self::assertArrayHasKey('hello', $variables);
        self::assertArrayHasKey('mailType', $variables);
        self::assertEquals('Plaintext', $variables['mailType']);
        self::assertArrayHasKey('templateType', $variables);
        self::assertEquals('Plaintext', $variables['templateType']);


    }
    
    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function renderReplacesPathMarkers()
    {

        /**
         * Scenario:
         *
         * Given a valid configuration
         * When the method is called
         * Then the baseUrl-marker is replaced
         * Then the baseUrlImages-marker is replaced
         * Then the baseUrlLogo-marker is replaced
         * Then the logoUrl-marker is replaced
         */
        $this->subject->setTemplate('Testing/Check40.html');
        $result = $this->subject->render();

        static::assertContains('baseUrl: http://www.example.de', $result);
        static::assertContains('baseUrlImages: http://www.example.de/typo3conf/ext/rkw_mailer/Resources/Public/Images', $result);
        static::assertContains('baseUrlLogo: http://www.example.de/typo3conf/ext/rkw_mailer/Resources/Public/Images/logo.png', $result);
        static::assertContains('logoUrl: http://www.example.de/typo3conf/ext/rkw_mailer/Resources/Public/Images/logo.png', $result);
    }



    /**
     * @test
     * @throws \Exception
     */
    public function renderResolvesRelativeAndAbsolutePathsToUrls()
    {

        /**
         * Scenario:
         *
         * Given a valid configuration
         * Given a relative path as url
         * Given a relative path as src
         * Given an absolute path as src
         * When the method is called
         * Then the relative url is resolved to an url
         * Then the relative src is resolved to an url
         * Then the absolute src is resolved to an url
         */
        $this->subject->setTemplate('Testing/Check50.html');
        $result = $this->subject->render();

        static::assertContains('<a href="http://www.example.de/test.html">Test</a>', $result);
        static::assertContains('<img src="http://www.example.de/test.png" width="30" height="30" alt="Test"/>', $result);
        static::assertContains('<img src="http://www.example.de/fileadmin/_processed_/', $result);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function renderHasAccessToSettings ()
    {

        /**
         * Scenario:
         *
         * Given a valid configuration
         * When the method is called
         * Then the setting variables are available
         */
        $this->subject->setTemplate('Testing/Check70.html');
        $result = $this->subject->render();

        static::assertContains('Wonderful!', $result);

    }

    //=============================================
    /**
     * @test
     * @throws \Exception
     */
    public function setQueueMailAddsLayoutRootPaths()
    {

        /**
         * Scenario:
         *
         * Given a queueMailObject with a layoutPath-property set
         * When the method is called
         * Then the value of the layoutPath-property of the queueMailObject is added to the layoutPaths of the subject
         */
        $queueMail = new QueueMail();
        $queueMail->setLayoutPaths(
            [
                0 => 'EXT:rkw_mailer/Tests/Funky/New100/Layouts/',
                1 => 'EXT:rkw_mailer/Tests/Funky/New200/Layouts/'
            ]
        );

        $expected = [
            0 => 'typo3conf/ext/rkw_mailer/Resources/Private/Layouts/',
            1 => 'typo3conf/ext/rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Layouts/',
            2 => 'typo3conf/ext/rkw_mailer/Tests/Funky/New100/Layouts/',
            3 => 'typo3conf/ext/rkw_mailer/Tests/Funky/New200/Layouts/'
        ];
        
        $this->subject->setQueueMail($queueMail);
        $result = $this->subject->getLayoutRootPaths();
        
        static::assertCount(4, $result);
        static::assertStringEndsWith($expected[0], $result[0]);
        static::assertStringEndsWith($expected[1], $result[1]);
        static::assertStringEndsWith($expected[2], $result[2]);
        static::assertStringEndsWith($expected[3], $result[3]);        
    }

    /**
     * @test
     * @throws \Exception
     */
    public function setQueueMailAddsTemplateRootPaths()
    {

        /**
         * Scenario:
         *
         * Given a queueMailObject with a templatePath-property set
         * When the method is called
         * Then the value of the templatePath-property of the queueMailObject is added to the layoutPaths of the subject
         */
        $queueMail = new QueueMail();
        $queueMail->setTemplatePaths(
            [
                0 => 'EXT:rkw_mailer/Tests/Funky/New100/Templates/',
                1 => 'EXT:rkw_mailer/Tests/Funky/New200/Templates/'
            ]
        );

        $expected = [
            0 => 'typo3conf/ext/rkw_mailer/Resources/Private/Templates/',
            1 => 'typo3conf/ext/rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Templates/',
            2 => 'typo3conf/ext/rkw_mailer/Tests/Funky/New100/Templates/',
            3 => 'typo3conf/ext/rkw_mailer/Tests/Funky/New200/Templates/'
        ];

        $this->subject->setQueueMail($queueMail);
        $result = $this->subject->getTemplateRootPaths();

        static::assertCount(4, $result);
        static::assertStringEndsWith($expected[0], $result[0]);
        static::assertStringEndsWith($expected[1], $result[1]);
        static::assertStringEndsWith($expected[2], $result[2]);
        static::assertStringEndsWith($expected[3], $result[3]);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function setQueueMailAddsPartialRootPaths()
    {

        /**
         * Scenario:
         *
         * Given a queueMailObject with a partialPath-property set
         * When the method is called
         * Then the value of the partialPath-property of the queueMailObject is added to the partialsRootPaths of the subject
         */
        $queueMail = new QueueMail();
        $queueMail->setPartialPaths(
            [
                0 => 'EXT:rkw_mailer/Tests/Funky/New100/Partials/',
                1 => 'EXT:rkw_mailer/Tests/Funky/New200/Partials/'
            ]
        );

        $expected = [
            0 => 'typo3conf/ext/rkw_mailer/Resources/Private/Partials/',
            1 => 'typo3conf/ext/rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Partials/',
            2 => 'typo3conf/ext/rkw_mailer/Tests/Funky/New100/Partials/',
            3 => 'typo3conf/ext/rkw_mailer/Tests/Funky/New200/Partials/'
        ];

        $this->subject->setQueueMail($queueMail);
        $result = $this->subject->getPartialRootPaths();

        static::assertCount(4, $result);
        static::assertStringEndsWith($expected[0], $result[0]);
        static::assertStringEndsWith($expected[1], $result[1]);
        static::assertStringEndsWith($expected[2], $result[2]);
        static::assertStringEndsWith($expected[3], $result[3]);
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