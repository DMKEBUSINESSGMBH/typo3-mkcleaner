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

use DMK\Mkcleaner\Task\CleanupTask;
use DMK\Mkcleaner\Task\CleanupTaskFieldProvider;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;

/**
 * Class CleanupTaskTest.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class CleanupTaskFieldProviderTest extends UnitTestCase
{
    /**
     * @test
     */
    public function saveAdditionalFields()
    {
        $task = $this->getMockBuilder(CleanupTask::class)
            ->setMethods(['setSourcepaths'])
            ->disableOriginalConstructor()
            ->getMock();
        $task
            ->expects(self::once())
            ->method('setSourcepaths')
            ->with('testPath');
        $provider = new CleanupTaskFieldProvider();
        $provider->saveAdditionalFields(['sourcepaths' => 'testPath'], $task);
    }

    /**
     * @test
     * @dataProvider validateAdditionalFieldsDataProvider
     */
    public function validateAdditionalFieldsWithInvalidData(array $submittedData)
    {
        $GLOBALS['LANG'] = $this->getAccessibleMock(LanguageService::class, ['sL'], [], '', false);
        $GLOBALS['LANG']
            ->expects(self::once())
            ->method('sL')
            ->with('LLL:EXT:mkcleaner/Resources/Private/Language/locallang.xlf:message.CleanupTask.sourcepaths.invalid')
            ->willReturn('message');

        $moduleController = $this->getMockBuilder(SchedulerModuleController::class)
            ->disableOriginalConstructor()
            ->getMock();
        $moduleController
            ->expects(self::once())
            ->method('addMessage')
            ->with('message', 2);
        $provider = new CleanupTaskFieldProvider();
        self::assertFalse($provider->validateAdditionalFields($submittedData, $moduleController));
    }

    public function validateAdditionalFieldsDataProvider(): array
    {
        return [
            [[]],
            [['sourcepaths' => '']],
            [['sourcepaths' => 'unknown']],
            [['sourcepaths' => 'unknown'.LF.'/']],
        ];
    }

    /**
     * @test
     */
    public function validateAdditionalFields()
    {
        $moduleController = $this->getMockBuilder(SchedulerModuleController::class)
            ->disableOriginalConstructor()
            ->getMock();
        $moduleController
            ->expects(self::never())
            ->method('addMessage');
        $provider = new CleanupTaskFieldProvider();
        $submittedData = ['sourcepaths' => '/'];
        self::assertTrue($provider->validateAdditionalFields($submittedData, $moduleController));
    }

    /**
     * @test
     */
    public function getAdditionalFields()
    {
        $moduleController = $this->getMockBuilder(SchedulerModuleController::class)
            ->disableOriginalConstructor()
            ->getMock();
        $task = $this->getMockBuilder(CleanupTask::class)
            ->setMethods(['getSourcepaths'])
            ->disableOriginalConstructor()
            ->getMock();
        $provider = new CleanupTaskFieldProvider();
        $taskInfo = ['dummy' => 'test'];
        self::assertSame(
            [
                'sourcepaths' => [
                    'code' => '<textarea class="form-control" rows="5" cols="50" name="tx_scheduler[sourcepaths]" id="sourcepaths" ></textarea>',
                    'label' => 'LLL:EXT:mkcleaner/Resources/Private/Language/locallang.xlf:label.CleanupTask.sourcepaths',
                    'cshKey' => '',
                    'cshLabel' => '',
                ],
            ],
            $provider->getAdditionalFields($taskInfo, $task, $moduleController)
        );
        self::assertSame(['dummy' => 'test'], $taskInfo);
    }

    /**
     * @test
     */
    public function getAdditionalFieldsWhenEditingTask()
    {
        $moduleController = $this->getMockBuilder(SchedulerModuleController::class)
            ->disableOriginalConstructor()
            ->getMock();
        $moduleController->CMD = 'edit';
        $task = $this->getMockBuilder(CleanupTask::class)
            ->setMethods(['getSourcepaths'])
            ->disableOriginalConstructor()
            ->getMock();
        $task
            ->expects(self::once())
            ->method('getSourcepaths')
            ->willReturn('<test>');
        $provider = new CleanupTaskFieldProvider();
        $taskInfo = ['dummy' => 'test'];
        self::assertSame(
            [
                'sourcepaths' => [
                    'code' => '<textarea class="form-control" rows="5" cols="50" name="tx_scheduler[sourcepaths]" id="sourcepaths" >&lt;test&gt;</textarea>',
                    'label' => 'LLL:EXT:mkcleaner/Resources/Private/Language/locallang.xlf:label.CleanupTask.sourcepaths',
                    'cshKey' => '',
                    'cshLabel' => '',
                ],
            ],
            $provider->getAdditionalFields($taskInfo, $task, $moduleController)
        );
        self::assertSame(['dummy' => 'test', 'sourcepaths' => '<test>'], $taskInfo);
    }
}
