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

use DMK\Mkcleaner\Command\CleanerCommand;
use DMK\Mkcleaner\Command\Helper;
use DMK\Mkcleaner\Service\CleanerService;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class CleanerCommandTest.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class CleanerCommandTest extends UnitTestCase
{
    /**
     * @test
     */
    public function execute(): void
    {
        $GLOBALS['LANG'] = $this->createMock(LanguageService::class);
        $helper = $this->getMockBuilder(Helper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $firstFolder = $this->getMockBuilder(Folder::class)->disableOriginalConstructor()->getMock();
        $secondFolder = $this->getMockBuilder(Folder::class)->disableOriginalConstructor()->getMock();
        $helper
            ->expects(self::once())
            ->method('getFolderObjectsFromCombinedIdentifiers')
            ->with(['path1', 'path2'])
            ->willReturn([$firstFolder, $secondFolder]);

        $cleanerService = $this->getMockBuilder(CleanerService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cleanerService
            ->expects(self::exactly(2))
            ->method('cleanupFolder')
            ->withConsecutive([$firstFolder], [$secondFolder]);

        $command = $this->getAccessibleMock(CleanerCommand::class, ['run'], [$cleanerService, $helper]);
        $input = new ArrayInput(['foldersToClean' => ['path1', 'path2']]);
        $input->bind($command->getDefinition());

        self::assertTrue(0 === $command->_call('execute', $input, $this->createMock(OutputInterface::class)));
    }
}
