<?php
namespace RKW\RkwMailer\Tests\Unit\Service;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use RKW\RkwMailer\Service\MailService;

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
 * MailService
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MailBodyTest extends UnitTestCase
{

    /**
     * @var \RKW\RkwMailer\Service\MailService
     */
    private $subject;


    /**
     * Setup
     */
    protected function setUp()
    {
        parent::setUp();
        $this->subject = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MailService::class, ['unitTest' => true]);

    }


    /**
     * @test
     */
    public function setQueueRecipientPropertiesByBackendUserGivenRegistrationBackendUserAndAdditionalDataSetsExpectedValues()
    {
        $queueRecipient = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $fixture = new \RKW\RkwMailer\Domain\Model\QueueRecipient();
        $backendUser = new \RKW\RkwRegistration\Domain\Model\BackendUser();

        $additionalData = [
            'realName'      => 'Dr. Angela Merkel',
            'email'         => 'merkel@cdu.de',
            'languageCode'  => 'ru',
            'test'          => 'Merkel'
        ];

        $backendUser->setEmail('lauterbach@spd.de');

        $fixture->setEmail('lauterbach@spd.de');
        $fixture->setTitle('Dr.');
        $fixture->setFirstName('Angela');
        $fixture->setLastName('Merkel');
        $fixture->setLanguageCode('en');

        $this->subject->setQueueRecipientPropertiesByBackendUser($queueRecipient, $backendUser, $additionalData);
        static::assertEquals($fixture, $queueRecipient);
    }

    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }








}