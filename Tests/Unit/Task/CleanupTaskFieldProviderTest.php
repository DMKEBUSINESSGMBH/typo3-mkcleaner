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

use DMK\Mkcleaner\Task\CleanerTask;
use DMK\Mkcleaner\Task\CleanerTaskFieldProvider;
use DMK\Mkcleaner\Task\Helper;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;

/**
 * Class CleanerTaskTest.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class CleanerTaskFieldProviderTest extends UnitTestCase
{
    /**
     * @test
     */
    public function saveAdditionalFields()
    {
        $task = $this->getMockBuilder(CleanerTask::class)
            ->setMethods(['setFoldersToClean'])
            ->disableOriginalConstructor()
            ->getMock();
        $task
            ->expects(self::once())
            ->method('setFoldersToClean')
            ->with('testPath');
        $provider = new CleanerTaskFieldProvider();
        $provider->saveAdditionalFields(['foldersToClean' => 'testPath'], $task);
    }

    /**
     * @test
     */
    public function validateAdditionalFieldsWithInvalidData()
    {
        $GLOBALS['LANG'] = $this->getAccessibleMock(LanguageService::class, ['sL'], [], '', false);
        $GLOBALS['LANG']
            ->expects(self::once())
            ->method('sL')
            ->with('LLL:EXT:mkcleaner/Resources/Private/Language/locallang.xlf:message.CleanerTask.foldersToClean.invalid')
            ->willReturn('message');

        $helper = $this->getMockBuilder(Helper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helper
            ->expects(self::once())
            ->method('getFolderObjectsFromCombinedIdentifiers')
            ->with('/')
            ->willThrowException(new \Exception('folder not found'));

        $moduleController = $this->getMockBuilder(SchedulerModuleController::class)
            ->disableOriginalConstructor()
            ->getMock();
        $moduleController
            ->expects(self::once())
            ->method('addMessage')
            ->with('message', 2);
        $provider = new CleanerTaskFieldProvider($helper);
        $submittedData = ['foldersToClean' => '/'];
        self::assertFalse($provider->validateAdditionalFields($submittedData, $moduleController));
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
        $helper = $this->getMockBuilder(Helper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $provider = new CleanerTaskFieldProvider($helper);
        $submittedData = ['foldersToClean' => '/'];
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
        $task = $this->getMockBuilder(CleanerTask::class)
            ->setMethods(['getFoldersToClean'])
            ->disableOriginalConstructor()
            ->getMock();
        $provider = new CleanerTaskFieldProvider();
        $taskInfo = ['dummy' => 'test'];
        self::assertSame(
            [
                'foldersToClean' => [
                    'code' => '<textarea class="form-control" rows="5" cols="50" name="tx_scheduler[foldersToClean]" id="foldersToClean" ></textarea>',
                    'label' => 'LLL:EXT:mkcleaner/Resources/Private/Language/locallang.xlf:label.CleanerTask.foldersToClean',
                    'cshKey' => 'csh_mkcleaner',
                    'cshLabel' => 'label.CleanerTask.foldersToClean.csh',
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
        $task = $this->getMockBuilder(CleanerTask::class)
            ->setMethods(['getFoldersToClean'])
            ->disableOriginalConstructor()
            ->getMock();
        $task
            ->expects(self::once())
            ->method('getFoldersToClean')
            ->willReturn('<test>');
        $provider = new CleanerTaskFieldProvider();
        $taskInfo = ['dummy' => 'test'];
        self::assertSame(
            [
                'foldersToClean' => [
                    'code' => '<textarea class="form-control" rows="5" cols="50" name="tx_scheduler[foldersToClean]" id="foldersToClean" >&lt;test&gt;</textarea>',
                    'label' => 'LLL:EXT:mkcleaner/Resources/Private/Language/locallang.xlf:label.CleanerTask.foldersToClean',
                    'cshKey' => 'csh_mkcleaner',
                    'cshLabel' => 'label.CleanerTask.foldersToClean.csh',
                ],
            ],
            $provider->getAdditionalFields($taskInfo, $task, $moduleController)
        );
        self::assertSame(['dummy' => 'test', 'foldersToClean' => '<test>'], $taskInfo);
    }
}
