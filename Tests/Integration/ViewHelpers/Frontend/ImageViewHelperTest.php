<?php
namespace RKW\RkwMailer\Tests\Integration\ViewHelpers\Frontend;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use TYPO3\CMS\Fluid\View\StandaloneView;

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
 * ImageViewHelperTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ImageViewHelperTest extends FunctionalTestCase
{

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_mailer',
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

        parent::setUp();

        $this->importDataSet(__DIR__ . '/ImageViewHelperTest/Fixtures/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:realurl/Configuration/TypoScript/setup.txt',
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Tests/Integration/ViewHelpers/Frontend/ImageViewHelperTest/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->standAloneViewHelper = $this->objectManager->get(StandaloneView::class);
        $this->standAloneViewHelper->setTemplateRootPaths(
            [
                0 => __DIR__ . '/ImageViewHelperTest/Fixtures/Frontend/Templates'
            ]
        );


    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersImages ()
    {

        /**
        * Scenario:
        *
        * Given the ViewHelper is used in a template
        * When the ViewHelper is rendered
        * Then the images are rendered like in frontend context
        */
        $this->standAloneViewHelper->setTemplate('Check10.html');

        $result = $this->standAloneViewHelper->render();

        static::assertContains('<img src="', $result);
        static::assertContains('width="536"', $result);
        static::assertContains('height="200"', $result);
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