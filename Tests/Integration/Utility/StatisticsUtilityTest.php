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
use RKW\RkwMailer\Domain\Repository\QueueRecipientRepository;
use RKW\RkwMailer\Domain\Model\QueueRecipient;
use RKW\RkwMailer\Utility\StatisticsUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * StatisticsUtilityTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class StatisticsUtilityTest extends FunctionalTestCase
{


    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/StatisticsUtilityTest/Fixtures';


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
     * @var \RKW\RkwMailer\Domain\Repository\QueueRecipientRepository
     */
    private $queueRecipientRepository;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager = null;
    
    
    /**
     * @var \RKW\RkwMailer\Utility\StatisticsUtility
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
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        $this->subject = GeneralUtility::makeInstance(StatisticsUtility::class);
        
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->queueRecipientRepository = $this->objectManager->get(QueueRecipientRepository::class);
    }


    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function generateLinkHashTrimsTrailingSlashAndRemovesProtocol()
    {
        /**
         * Scenario:
         *
         * Given a link as string
         * When the method is called
         * Then a SHA1-Hash of that link is returned
         * Then the SHA1-Hash does not include the protocol
         * Then the SHA1-Hash does not include a trailing slash
         */
        
        $link = 'https://www.php.net/manual/de/';
        $linkTrimmed = 'www.php.net/manual/de';
        $expected = sha1($linkTrimmed);
        
        $result = $this->subject::generateLinkHash($link);
        static::assertEquals($expected, $result);        
    }


    /**
     * @test
     * @throws \Exception
     */
    public function generateLinkHashRespectsQueryParams()
    {
        /**
         * Scenario:
         *
         * Given two links as string
         * Given this two links contain params
         * Given this two links only differ by one value of a query-parameter
         * When the method is called with both links separately
         * Then a SHA1-Hash of the link is returned in each case
         * Then the SHA1-Hashes of both links differ
         */
        $linkOne = 'https://www.php.net/manual/de/?test=1';
        $linkTwo = 'https://www.php.net/manual/de/?test=2';
        
        $resultOne = $this->subject::generateLinkHash($linkOne);
        $resultTwo = $this->subject::generateLinkHash($linkTwo);

        static::assertNotEquals($resultOne, $resultTwo);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function generateLinkHashDecodesUrl()
    {
        /**
         * Scenario:
         *
         * Given an url-encoded link as string
         * When the method is called
         * Then a SHA1-Hash of that link is returned
         * Then the SHA1-Hash matches the one from the url-decoded version of the link
         */
        $link = 'https%3A%2F%2Fwww.google.de%2Fsuchen%2F';
        $linkDecoded = 'www.google.de/suchen';
        $expected = sha1($linkDecoded);

        $result = $this->subject::generateLinkHash($link);
        static::assertEquals($expected, $result);
    }
    
    
    //=============================================
    
    /**
     * @test
     * @throws \Exception
     */
    public function generateRecipientHashThrowsExceptionOnNonPersistentObject()
    {
        /**
         * Scenario:
         *
         * Given a queueRecipient object
         * Given the queueRecipient-object is not persisted
         * When the method is called 
         * Then an exception is thrown
         */
        static::expectException(\RKW\RkwMailer\Exception::class);

        $queueRecipient = new QueueRecipient();
        $result = $this->subject::generateRecipientHash($queueRecipient);
       
    }

    /**
     * @test
     * @throws \Exception
     */
    public function generateRecipientHashReturnsHashOnPersistentObject()
    {
        /**
         * Scenario:
         *
         * Given a queueRecipient object
         * Given this queueRecipient-object has an uid
         * When the method is called
         * Then a SHA1-Hash using the uid of the queueRecipient-object is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(10);
        $result = $this->subject::generateRecipientHash($queueRecipient);
        $expected = sha1($queueRecipient->getUid());

        static::assertEquals($expected, $result);
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function addParamsToUrlIgnoresEmptyParameterArray()
    {
        /**
         * Scenario:
         *
         * Given an url as string
         * Given no additional parameters
         * When the method is called 
         * Then the given link is returned unchanged
         */
        $url = 'https://www.php.net/manual/de/';
        $additionalParameters = [];

        $result = $this->subject::addParamsToUrl($url, $additionalParameters);
        static::assertEquals($url, $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function addParamsToUrlAddsParameters()
    {
        /**
         * Scenario:
         *
         * Given an url as string
         * Given two additional parameters as array
         * When the method is called
         * Then the given link is returned
         * Then the two parameters are added at the end of the url
         * Then an questionmark is added before the parameters
         * Then the parameters are separated by an ampersand
         */
        $url = 'https://www.php.net/manual/de/';
        $additionalParameters = [
            'test1=1',
            'test2=2'
        ];
        $expected = 'https://www.php.net/manual/de/?test1=1&test2=2';

        $result = $this->subject::addParamsToUrl($url, $additionalParameters);
        static::assertEquals($expected, $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function addParamsToUrlAddsParametersToExistingOnes()
    {
        /**
         * Scenario:
         *
         * Given an url as string
         * Given that url already contains parameters
         * Given two additional parameters as array
         * When the method is called
         * Then the given link is returned
         * Then the two additional parameters are added at the end of the url
         * Then no additional questionmark is added before the parameters
         * Then all the parameters are separated by an ampersand
         */
        $url = 'https://www.php.net/manual/de/?test0=0';
        $additionalParameters = [
            'test1=1',
            'test2=2'
        ];
        $expected = 'https://www.php.net/manual/de/?test0=0&test1=1&test2=2';

        $result = $this->subject::addParamsToUrl($url, $additionalParameters);
        static::assertEquals($expected, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function addParamsToUrlAddsParametersBeforeAnchor()
    {
        /**
         * Scenario:
         *
         * Given an url as string
         * Given that url has an anchor
         * Given two additional parameters as array
         * When the method is called
         * Then the given link is returned
         * Then the two additional parameters are added at the end of the url
         * Then an questionmark is added before the parameters
         * Then the parameters are separated by an ampersand
         * Then the anchor is added after the parameters
         */
        $url = 'https://www.php.net/manual/de/#anchor';
        $additionalParameters = [
            'test1=1',
            'test2=2'
        ];
        $expected = 'https://www.php.net/manual/de/?test1=1&test2=2#anchor';

        $result = $this->subject::addParamsToUrl($url, $additionalParameters);
        static::assertEquals($expected, $result);
    }

    
    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

}