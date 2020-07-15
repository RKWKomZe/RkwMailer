<?php
namespace RKW\RkwMailer\Tests\Integration\ViewHelpers;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use TYPO3\CMS\Fluid\View\StandaloneView;
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
 * PlaintextLineBreaksViewHelperTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PlaintextLineBreaksViewHelperTest extends FunctionalTestCase
{

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
     * @var \TYPO3\CMS\Fluid\View\StandaloneView
     */
    private $standAloneViewHelper;

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

        parent::setUp();

        $this->importDataSet(__DIR__ . '/PlaintextLineBreaksViewHelperTest/Fixtures/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Tests/Integration/ViewHelpers/Frontend/PlaintextLineBreaksViewHelperTest/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->standAloneViewHelper = $this->objectManager->get(StandaloneView::class);
        $this->standAloneViewHelper->setTemplateRootPaths(
            [
                0 => __DIR__ . '/PlaintextLineBreaksViewHelperTest/Fixtures/Frontend/Templates'
            ]
        );

    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersTextWithoutLineBreakDefault ()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a text without line break
         * When the ViewHelper is rendered
         * Then the text is rendered without line breaks
         */
        $text = "Without line break, remove line breaks.";

        $this->standAloneViewHelper->setTemplate('Check10.html');
        $this->standAloneViewHelper->assign('text', $text);

        $expected = file_get_contents(__DIR__ . '/PlaintextLineBreaksViewHelperTest/Fixtures/Expected/Check10.txt');
        $result = str_replace("\n", '', $this->standAloneViewHelper->render());

        static::assertEquals($expected, $result);
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersTextWithLineBreakDefault ()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a text with line break
         * When the ViewHelper is rendered
         * Then the text is rendered with line break
         * Then the whitespace at the end of the first line is keept
         */
        // @toDo: currently also the whitespace after the comma is removed by the VH. Is this expected?
        $text = "With line break, \nremove line breaks.";

        $this->standAloneViewHelper->setTemplate('Check20.html');
        $this->standAloneViewHelper->assign('text', $text);

        $expected = file_get_contents(__DIR__ . '/PlaintextLineBreaksViewHelperTest/Fixtures/Expected/Check20.txt');
        $result = str_replace("\n", '', $this->standAloneViewHelper->render());

        static::assertEquals($expected, $result);
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersTextWithLineBreakAndOptionKeepLineBreaks ()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a text without line break
         * When the ViewHelper is rendered
         * Then the text break is rendered with manual line breaks
         */
        $text = "With line break, \nkeep line breaks.";

        $this->standAloneViewHelper->setTemplate('Check30.html');
        $this->standAloneViewHelper->assign('text', $text);

        $expected = file_get_contents(__DIR__ . '/PlaintextLineBreaksViewHelperTest/Fixtures/Expected/Check30.txt');
        $result = str_replace("\n", '', $this->standAloneViewHelper->render());

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