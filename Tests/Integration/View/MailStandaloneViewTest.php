<?php
namespace RKW\RkwMailer\Tests\Integration\View;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwAjax\Helper\AjaxHelper;
use RKW\RkwMailer\Domain\Repository\QueueMailRepository;
use RKW\RkwMailer\View\MailStandaloneView;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;


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
     * @var \RKW\RkwMailer\Domain\Repository\QueueMailRepository
     */
    private $queueMailRepository;


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
        $this->queueMailRepository = $this->objectManager->get(QueueMailRepository::class);

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
        * Then a three configuration types of the rootpage of the given subpage are loaded
        */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');
        $this->setUpFrontendRootPage(
            10,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailerConfiguration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Configuration/Check10.typoscript',
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
            $settings['view.']['layoutRootPaths.'][1]
        );

        static::assertEquals(
            1010,
            $settings['persistence.']['storagePid']
        );

        static::assertEquals(
            1010,
            $settings['settings.']['redirectPid']
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
            $settings['view.']['layoutRootPaths.'][1]
        );

        static::assertEquals(
            9999,
            $settings['persistence.']['storagePid']
        );

        static::assertEquals(
            9999,
            $settings['settings.']['redirectPid']
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
                'EXT:rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Configuration/Check10.typoscript',
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
                'EXT:rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Configuration/Check10.typoscript',
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
                'EXT:rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Configuration/Check20.typoscript',
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
                'EXT:rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Configuration/Check20.typoscript',
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
                'EXT:rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Configuration/Check20.typoscript',
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
                'EXT:rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Configuration/Check30.typoscript',
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
                'EXT:rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Configuration/Check30.typoscript',
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
                'EXT:rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Configuration/Check30.typoscript',
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
         * Then no paths as added to the templateRootPath
         * Then the resolved path is set for the template
         * Then the default file extension is used
         */

        $expected = $this->subject->getTemplateRootPaths();
        $this->subject->setTemplate('EXT:rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Templates/Testing/Test');

        $resultingPaths = $this->subject->getTemplateRootPaths();
        static::assertCount(2, $resultingPaths);
        static::assertStringEndsWith('typo3conf/ext/rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Templates/Testing/Test.html', $this->subject->getTemplatePathAndFilename());
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

        $expected = $this->subject->getTemplateRootPaths();
        $this->subject->setTemplate('EXT:rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Templates/Testing/Test.test');

        $resultingPaths = $this->subject->getTemplateRootPaths();
        static::assertCount(2, $resultingPaths);
        static::assertStringEndsWith('typo3conf/ext/rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Templates/Testing/Test.test', $this->subject->getTemplatePathAndFilename());
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
        static::assertStringEndsWith('typo3conf/ext/rkw_mailer/Tests/Integration/View/MailStandaloneViewTest/Fixtures/Frontend/Templates/Testing/', $resultingPaths[2]);
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

    //=============================================


    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }








}