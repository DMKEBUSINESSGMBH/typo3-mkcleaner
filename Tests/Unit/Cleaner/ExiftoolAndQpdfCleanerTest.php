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

use DMK\Mkcleaner\Cleaner\ExiftoolAndQpdfCleaner;
use DMK\Mkcleaner\Tests\CleanerTestCase;
use TYPO3\CMS\Core\Resource\File;

/**
 * Class ExiftoolAndQpdfCleanerTest.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class ExiftoolAndQpdfCleanerTest extends CleanerTestCase
{
    protected ExiftoolAndQpdfCleaner $exiftoolAndQpdfCleaner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->exiftoolAndQpdfCleaner = new ExiftoolAndQpdfCleaner();
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['binSetup'] = 'mat2='.$this->fixturesFolder.'/mat2,'.
            'exiftool='.$this->fixturesFolder.'/exiftool,'.
            'qpdf='.$this->fixturesFolder.'/qpdf';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->fixturesFolder.'/exiftool_failure')) {
            unlink($this->fixturesFolder.'/exiftool_failure');
        }

        if (file_exists($this->fixturesFolder.'/qpdf_failure')) {
            unlink($this->fixturesFolder.'/qpdf_failure');
        }

        if (file_exists($this->fixturesFolder.'/testPath_intermediate')) {
            unlink($this->fixturesFolder.'/testPath_intermediate');
        }

        parent::tearDown();
    }

    /**
     * @test
     */
    public function cleanupFile(): void
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
                        'cmd' => $this->fixturesFolder."/exiftool -all:all= '".$this->fixturesFolder."/testPath' -o '".$this->fixturesFolder."/testPath_intermediate'",
                        'output' => ['exiftool executed'],
                        'returnValue' => 0,
                    ],
                ],
                [
                    'exec',
                    [
                        'cmd' => $this->fixturesFolder."/qpdf --linearize '".$this->fixturesFolder."/testPath_intermediate' '".$this->fixturesFolder."/testPath'",
                        'output' => ['qpdf executed'],
                        'returnValue' => 0,
                    ],
                ]
            );
        self::assertTrue($this->exiftoolAndQpdfCleaner->cleanupFile($file));
        self::assertFileDoesNotExist($this->fixturesFolder.'/testPath_intermediate');
    }

    /**
     * @test
     */
    public function cleanupFileIfExiftoolFails(): void
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
                    'cmd' => $this->fixturesFolder."/exiftool -all:all= '".$this->fixturesFolder."/testPath' -o '".$this->fixturesFolder."/testPath_intermediate'",
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
    public function cleanupFileIfIntermediateFileNotCreated(): void
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
                    'cmd' => $this->fixturesFolder."/exiftool -all:all= '".$this->fixturesFolder."/testPath' -o '".$this->fixturesFolder."/testPath_intermediate'",
                    'output' => ['exiftool executed'],
                    'returnValue' => 0,
                ]
            );
        self::assertFalse($this->exiftoolAndQpdfCleaner->cleanupFile($file));
        self::assertFileDoesNotExist($this->fixturesFolder.'/testPath_intermediate');
    }

    /**
     * @test
     */
    public function cleanupFileIfQpdfFails(): void
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
                    'cmd' => $this->fixturesFolder."/exiftool -all:all= '".$this->fixturesFolder."/testPath' -o '".$this->fixturesFolder."/testPath_intermediate'",
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
                    'cmd' => $this->fixturesFolder."/qpdf --linearize '".$this->fixturesFolder."/testPath_intermediate' '".$this->fixturesFolder."/testPath'",
                    'output' => ['qpdf executed'],
                    'returnValue' => 123,
                ]
            );

        self::assertFalse($this->exiftoolAndQpdfCleaner->cleanupFile($file));
        self::assertFileDoesNotExist($this->fixturesFolder.'/testPath_intermediate');
    }

    /**
     * @test
     *
     * @dataProvider canHandleFileDataProvider
     */
    public function canHandleFileIfSvgFileGiven(string $mimeType, bool $canHandle): void
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
