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

namespace DMK\Mkcleaner\Tests\Service;

use DMK\Mkcleaner\Service\Mat2Service;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Mat2ServiceTest.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class Mat2ServiceTest extends UnitTestCase
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Mat2Service
     */
    protected $mat2Service;

    protected function setUp()
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
            ->with(Mat2Service::class)
            ->willReturn($this->logger);
        GeneralUtility::setSingletonInstance(LogManager::class, $logManager);
        $this->mat2Service = new Mat2Service();

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['binSetup'] = 'mat2='.dirname(__FILE__).'/../../Fixtures/mat2';
    }

    /**
     * @test
     */
    public function cleanupFile()
    {
        $this->logger
            ->expects(self::once())
            ->method('info')
            ->with(
                'exec',
                [
                    'cmd' => dirname(__FILE__).'/../../Fixtures/mat2 --inplace --lightweight /var/www/html/packages/mkcleaner/Tests/Unit/Service/../../Fixtures/Files/dummy.txt',
                    'output' => ['mat2 executed'],
                    'returnValue' => 123,
                ]
            );
        $path = dirname(__FILE__).'/../../Fixtures/Files/dummy.txt';
        self::assertTrue($this->mat2Service->cleanupFile($path));
    }

    /**
     * @test
     */
    public function cleanupFileIfNoValidFileGiven()
    {
        $this->logger
            ->expects(self::never())
            ->method('info');
        self::assertFalse($this->mat2Service->cleanupFile('notExistingFile'));
    }

    /**
     * @test
     */
    public function cleanupFolderIfNoValidFolderGiven()
    {
        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with('failed: notExistingFolder ist not a directory');
        self::assertFalse($this->mat2Service->cleanupFolder('notExistingFolder'));
    }

    /**
     * @test
     */
    public function cleanupFolder()
    {
        $this->logger
            ->expects(self::exactly(2))
            ->method('info')
            ->withConsecutive(
                [
                    'exec',
                    [
                        'cmd' => dirname(__FILE__).'/../../Fixtures/mat2 --inplace --lightweight '.realpath(dirname(__FILE__).'/../../Fixtures/Files/dummy.txt'),
                        'output' => ['mat2 executed'],
                        'returnValue' => 123,
                    ],
                ],
                [
                    'exec',
                    [
                        'cmd' => dirname(__FILE__).'/../../Fixtures/mat2 --inplace --lightweight '.realpath(dirname(__FILE__).'/../../Fixtures/mat2'),
                        'output' => ['mat2 executed'],
                        'returnValue' => 123,
                    ],
                ]
            );
        self::assertTrue($this->mat2Service->cleanupFolder(dirname(__FILE__).'/../../Fixtures/'));
    }
}
