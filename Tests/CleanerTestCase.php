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

namespace DMK\Mkcleaner\Tests;

use DMK\Mkcleaner\Cleaner\AbstractCommandCleaner;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class CleanerTestCase.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
abstract class CleanerTestCase extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    protected Logger $logger;

    protected string $fixturesFolder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logManager = $this->getMockBuilder(LogManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logManager
            ->expects(self::once())
            ->method('getLogger')
            ->with(AbstractCommandCleaner::class)
            ->willReturn($this->logger);
        GeneralUtility::setSingletonInstance(LogManager::class, $logManager);
        $this->fixturesFolder = realpath(__DIR__.'/Fixtures');
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['binPath'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['BE']['disable_exec_function'] = false;
        $GLOBALS['TYPO3_CONF_VARS']['GFX']['processor_path'] = '';
        defined('LF') ?: define('LF', chr(10));
    }
}
