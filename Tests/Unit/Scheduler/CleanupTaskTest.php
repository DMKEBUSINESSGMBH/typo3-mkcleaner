<?php

/*
 * Copyright notice
 *
 * (c) DMK E-BUSINESS GmbH <dev@dmk-ebusiness.com>
 * All rights reserved
 *
 * This file is part of the "mkcleaner" Extension for TYPO3 CMS.
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GNU Lesser General Public License can be found at
 * www.gnu.org/licenses/lgpl.html
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

namespace DMK\Mkcleaner\Tests\Task;

use DMK\Mkcleaner\Service\Mat2Service;
use DMK\Mkcleaner\Task\CleanupTask;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Class CleanupTaskTest.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class CleanupTaskTest extends UnitTestCase
{
    /**
     * @var CleanupTask|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $task;

    protected function setUp()
    {
        parent::setUp();

        // We need a mock to disable the constructor
        $this->task = $this->getMockBuilder(CleanupTask::class)
            // dummy method
            ->setMethods(['getTaskUid'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @test
     */
    public function getAdditionalInformation()
    {
        $GLOBALS['LANG'] = $this->getAccessibleMock(LanguageService::class, ['sL'], [], '', false);
        $GLOBALS['LANG']
            ->expects(self::once())
            ->method('sL')
            ->with('LLL:EXT:mkcleaner/Resources/Private/Language/locallang.xlf:label.CleanupTask.sourcepaths')
            ->willReturn('label');

        $this->task->setSourcepaths('paths');
        self::assertSame(
            'test'.CRLF.'label'.': '.CRLF.'paths',
            $this->task->getAdditionalInformation('test')
        );
    }

    /**
     * @test
     */
    public function execute()
    {
        $mat2Service = $this->getMockBuilder(Mat2Service::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mat2Service
            ->expects(self::exactly(2))
            ->method('cleanupFolder')
            ->withConsecutive(['path1'], ['path2']);
        GeneralUtility::setSingletonInstance(Mat2Service::class, $mat2Service);

        $this->task->setSourcepaths('path1'.CRLF.'path2');
        self::assertTrue($this->task->execute());
    }
}
