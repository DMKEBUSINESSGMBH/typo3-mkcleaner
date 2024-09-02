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

use DMK\Mkcleaner\Cleaner\Mat2Cleaner;
use DMK\Mkcleaner\Tests\CleanerTestCase;
use TYPO3\CMS\Core\Resource\File;

/**
 * Class Mat2CleanerTest.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class Mat2CleanerTest extends CleanerTestCase
{
    protected Mat2Cleaner $mat2Cleaner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mat2Cleaner = new Mat2Cleaner();
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['binSetup'] = 'mat2='.$this->fixturesFolder.'/mat2';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->fixturesFolder.'/mat2_failure')) {
            unlink($this->fixturesFolder.'/mat2_failure');
        }

        parent::tearDown();
    }

    /**
     * @test
     */
    public function cleanupFile(): void
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
                    'cmd' => $this->fixturesFolder."/mat2 --inplace --lightweight '".$this->fixturesFolder."/testPath'",
                    'output' => ['mat2 executed'],
                    'returnValue' => 0,
                ]
            );
        self::assertTrue($this->mat2Cleaner->cleanupFile($file));
    }

    /**
     * @test
     */
    public function cleanupFileIfFailure(): void
    {
        touch($this->fixturesFolder.'/mat2_failure');
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
                    'cmd' => $this->fixturesFolder."/mat2 --inplace --lightweight '".$this->fixturesFolder."/testPath'",
                    'output' => ['mat2 executed'],
                    'returnValue' => 123,
                ]
            );
        self::assertFalse($this->mat2Cleaner->cleanupFile($file));
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
        self::assertSame($canHandle, $this->mat2Cleaner->canHandleFile($file));
    }

    public function canHandleFileDataProvider(): array
    {
        return [
            ['unknown', true],
            ['image/png', true],
            ['image/svg+xml', false],
            ['application/pdf', false],
        ];
    }
}
