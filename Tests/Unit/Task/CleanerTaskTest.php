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

use DMK\Mkcleaner\Service\CleanerService;
use DMK\Mkcleaner\Task\CleanerTask;
use DMK\Mkcleaner\Task\Helper;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class CleanerTaskTest.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class CleanerTaskTest extends UnitTestCase
{
    /**
     * @var CleanerTask|MockObject
     */
    protected $task;

    protected function setUp()
    {
        parent::setUp();

        // We need a mock to disable the constructor
        $this->task = $this->getMockBuilder(CleanerTask::class)
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
            ->with('LLL:EXT:mkcleaner/Resources/Private/Language/locallang.xlf:label.CleanerTask.foldersToClean')
            ->willReturn('label');

        $this->task->setFoldersToClean('paths');
        self::assertSame(
            'test'.CRLF.'label: '.CRLF.'paths',
            $this->task->getAdditionalInformation('test')
        );
    }

    /**
     * @test
     */
    public function execute()
    {
        $helper = $this->getMockBuilder(Helper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $firstFolder = $this->getMockBuilder(Folder::class)->disableOriginalConstructor()->getMock();
        $secondFolder = $this->getMockBuilder(Folder::class)->disableOriginalConstructor()->getMock();
        $helper
            ->expects(self::once())
            ->method('getFolderObjectsFromCombinedIdentifiers')
            ->with('path1'.CRLF.'path2')
            ->willReturn([$firstFolder, $secondFolder]);
        GeneralUtility::addInstance(Helper::class, $helper);

        $cleanerService = $this->getMockBuilder(CleanerService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cleanerService
            ->expects(self::exactly(2))
            ->method('cleanupFolder')
            ->withConsecutive([$firstFolder], [$secondFolder]);
        GeneralUtility::setSingletonInstance(CleanerService::class, $cleanerService);

        $this->task->setFoldersToClean('path1'.CRLF.'path2');
        self::assertTrue($this->task->execute());
    }
}
