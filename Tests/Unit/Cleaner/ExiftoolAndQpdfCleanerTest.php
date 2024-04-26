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

namespace DMK\Mkcleaner\Tests\Cleaner;

use DMK\Mkcleaner\Cleaner\AbstractCommandCleaner;
use DMK\Mkcleaner\Cleaner\ExiftoolAndQpdfCleaner;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ExiftoolAndQpdfCleanerTest.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class ExiftoolAndQpdfCleanerTest extends UnitTestCase
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ExiftoolAndQpdfCleaner
     */
    protected $exiftoolAndQpdfCleaner;

    /**
     * @var string
     */
    protected $fixturesFolder;

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
            ->with(AbstractCommandCleaner::class)
            ->willReturn($this->logger);
        GeneralUtility::setSingletonInstance(LogManager::class, $logManager);
        $this->exiftoolAndQpdfCleaner = new ExiftoolAndQpdfCleaner();

        $this->fixturesFolder = realpath(dirname(__FILE__).'/../../Fixtures');
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['binSetup'] = 'mat2='.$this->fixturesFolder.'/mat2,'.
            'exiftool='.$this->fixturesFolder.'/exiftool,'.
            'qpdf='.$this->fixturesFolder.'/qpdf';
    }

    protected function tearDown()
    {
        parent::tearDown();

        if (file_exists($this->fixturesFolder.'/exiftool_failure')) {
            unlink($this->fixturesFolder.'/exiftool_failure');
        }
        if (file_exists($this->fixturesFolder.'/qpdf_failure')) {
            unlink($this->fixturesFolder.'/qpdf_failure');
        }
        if (file_exists($this->fixturesFolder.'/testPath_intermediate')) {
            unlink($this->fixturesFolder.'/testPath_intermediate');
        }
    }

    /**
     * @test
     */
    public function cleanupFile()
    {
        touch($this->fixturesFolder.'/testPath_intermediate');
        $file = $this->getMockBuilder(File::class)->disableOriginalConstructor()->getMock();
        $file
            ->expects(self::any())
            ->method('getForLocalProcessing')
            ->with(false)
            ->willReturn($this->fixturesFolder.'/testPathSymlink');
        $this->logger
            ->expects(self::never())
            ->method('warning');
        $this->logger
            ->expects(self::exactly(2))
            ->method('info')
            ->withConsecutive(
                [
                    'exec',
                    [
                        'cmd' => $this->fixturesFolder.'/exiftool -all:all= \''.$this->fixturesFolder.'/testPath\' -o \''.$this->fixturesFolder.'/testPath_intermediate\'',
                        'output' => ['exiftool executed'],
                        'returnValue' => 0,
                    ],
                ],
                [
                    'exec',
                    [
                        'cmd' => $this->fixturesFolder.'/qpdf --linearize \''.$this->fixturesFolder.'/testPath_intermediate\' \''.$this->fixturesFolder.'/testPath\'',
                        'output' => ['qpdf executed'],
                        'returnValue' => 0,
                    ],
                ]
            );
        self::assertTrue($this->exiftoolAndQpdfCleaner->cleanupFile($file));
        self::assertFileNotExists($this->fixturesFolder.'/testPath_intermediate');
    }

    /**
     * @test
     */
    public function cleanupFileIfExiftoolFails()
    {
        touch($this->fixturesFolder.'/testPath_intermediate');
        touch($this->fixturesFolder.'/exiftool_failure');
        $file = $this->getMockBuilder(File::class)->disableOriginalConstructor()->getMock();
        $file
            ->expects(self::any())
            ->method('getForLocalProcessing')
            ->with(false)
            ->willReturn($this->fixturesFolder.'/testPathSymlink');
        $this->logger
            ->expects(self::never())
            ->method('info');
        $this->logger
            ->expects(self::once())
            ->method('warning')
            ->with(
                'exec',
                [
                    'cmd' => $this->fixturesFolder.'/exiftool -all:all= \''.$this->fixturesFolder.'/testPath\' -o \''.$this->fixturesFolder.'/testPath_intermediate\'',
                    'output' => ['exiftool executed'],
                    'returnValue' => 123,
                ]
            );
        self::assertFalse($this->exiftoolAndQpdfCleaner->cleanupFile($file));
        self::assertFileExists($this->fixturesFolder.'/testPath_intermediate');
    }

    /**
     * @test
     */
    public function cleanupFileIfIntermediateFileNotCreated()
    {
        $file = $this->getMockBuilder(File::class)->disableOriginalConstructor()->getMock();
        $file
            ->expects(self::any())
            ->method('getForLocalProcessing')
            ->with(false)
            ->willReturn($this->fixturesFolder.'/testPathSymlink');
        $this->logger
            ->expects(self::never())
            ->method('warning');
        $this->logger
            ->expects(self::once())
            ->method('info')
            ->with(
                'exec',
                [
                    'cmd' => $this->fixturesFolder.'/exiftool -all:all= \''.$this->fixturesFolder.'/testPath\' -o \''.$this->fixturesFolder.'/testPath_intermediate\'',
                    'output' => ['exiftool executed'],
                    'returnValue' => 0,
                ]
            );
        self::assertFalse($this->exiftoolAndQpdfCleaner->cleanupFile($file));
        self::assertFileNotExists($this->fixturesFolder.'/testPath_intermediate');
    }

    /**
     * @test
     */
    public function cleanupFileIfQpdfFails()
    {
        touch($this->fixturesFolder.'/testPath_intermediate');
        touch($this->fixturesFolder.'/qpdf_failure');
        $file = $this->getMockBuilder(File::class)->disableOriginalConstructor()->getMock();
        $file
            ->expects(self::any())
            ->method('getForLocalProcessing')
            ->with(false)
            ->willReturn($this->fixturesFolder.'/testPathSymlink');
        $this->logger
            ->expects(self::once())
            ->method('info')
            ->with(
                'exec',
                [
                    'cmd' => $this->fixturesFolder.'/exiftool -all:all= \''.$this->fixturesFolder.'/testPath\' -o \''.$this->fixturesFolder.'/testPath_intermediate\'',
                    'output' => ['exiftool executed'],
                    'returnValue' => 0,
                ]
            );
        $this->logger
            ->expects(self::once())
            ->method('warning')
            ->with(
                'exec',
                [
                    'cmd' => $this->fixturesFolder.'/qpdf --linearize \''.$this->fixturesFolder.'/testPath_intermediate\' \''.$this->fixturesFolder.'/testPath\'',
                    'output' => ['qpdf executed'],
                    'returnValue' => 123,
                ]
            );

        self::assertFalse($this->exiftoolAndQpdfCleaner->cleanupFile($file));
        self::assertFileNotExists($this->fixturesFolder.'/testPath_intermediate');
    }

    /**
     * @test
     * @dataProvider canHandleFileDataProvider
     */
    public function canHandleFileIfSvgFileGiven(string $mimeType, bool $canHandle)
    {
        $file = $this->getMockBuilder(File::class)->disableOriginalConstructor()->getMock();
        $file
            ->expects(self::any())
            ->method('getMimeType')
            ->willReturn($mimeType);
        self::assertSame($canHandle, $this->exiftoolAndQpdfCleaner->canHandleFile($file));
    }

    public function canHandleFileDataProvider(): array
    {
        return [
            ['unknown', false],
            ['image/png', false],
            ['image/svg+xml', false],
            ['application/pdf', true],
        ];
    }
}
