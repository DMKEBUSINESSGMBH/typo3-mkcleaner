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
use DMK\Mkcleaner\Cleaner\Mat2Cleaner;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Mat2CleanerTest.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class Mat2CleanerTest extends UnitTestCase
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Mat2Cleaner
     */
    protected $mat2Cleaner;

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
        $this->mat2Cleaner = new Mat2Cleaner();
        $this->fixturesFolder = realpath(dirname(__FILE__).'/../../Fixtures');

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['binSetup'] = 'mat2='.$this->fixturesFolder.'/mat2';
    }

    /**
     * @test
     */
    public function cleanupFile()
    {
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
                    'cmd' => $this->fixturesFolder.'/mat2 --inplace --lightweight \''.$this->fixturesFolder.'/testPath\'',
                    'output' => ['mat2 executed'],
                    'returnValue' => 123,
                ]
            );
        self::assertTrue($this->mat2Cleaner->cleanupFile($file));
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
