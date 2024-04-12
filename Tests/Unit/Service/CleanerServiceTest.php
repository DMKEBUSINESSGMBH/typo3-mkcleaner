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

use DMK\Mkcleaner\Cleaner\ExiftoolAndQpdfCleaner;
use DMK\Mkcleaner\Cleaner\Mat2Cleaner;
use DMK\Mkcleaner\Cleaner\Registry;
use DMK\Mkcleaner\Service\CleanerService;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Mat2ServiceTest.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class CleanerServiceTest extends UnitTestCase
{
    protected function tearDown()
    {
        parent::tearDown();

        Registry::unregisterCleaner(Mat2Cleaner::class);
        Registry::unregisterCleaner(ExiftoolAndQpdfCleaner::class);
    }

    /**
     * @test
     */
    public function cleanupFile()
    {
        $file = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $exiftoolAndQpdfCleaner = $this->getMockBuilder(ExiftoolAndQpdfCleaner::class)
            ->disableOriginalConstructor()
            ->getMock();
        $exiftoolAndQpdfCleaner
            ->expects(self::once())
            ->method('canHandleFile')
            ->with($file)
            ->willReturn(true);
        $exiftoolAndQpdfCleaner
            ->expects(self::once())
            ->method('cleanupFile')
            ->with($file);
        GeneralUtility::setSingletonInstance(ExiftoolAndQpdfCleaner::class, $exiftoolAndQpdfCleaner);

        $mat2Cleaner = $this->getMockBuilder(Mat2Cleaner::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mat2Cleaner
            ->expects(self::once())
            ->method('canHandleFile')
            ->with($file)
            ->willReturn(false);
        $mat2Cleaner
            ->expects(self::never())
            ->method('cleanupFile');
        GeneralUtility::setSingletonInstance(Mat2Cleaner::class, $mat2Cleaner);

        Registry::registerCleaner(Mat2Cleaner::class, 50);
        Registry::registerCleaner(ExiftoolAndQpdfCleaner::class, 75);

        (new CleanerService())->cleanupFile($file);
    }

    /**
     * @test
     */
    public function cleanupFolder()
    {
        $firstFile = $this->getMockBuilder(File::class)->disableOriginalConstructor()->getMock();
        $secondFile = $this->getMockBuilder(File::class)->disableOriginalConstructor()->getMock();

        $folder = $this->getMockBuilder(Folder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $folder
            ->expects(self::once())
            ->method('getFiles')
            ->with(0, 0, Folder::FILTER_MODE_USE_OWN_AND_STORAGE_FILTERS, true)
            ->willReturn([$firstFile, $secondFile]);

        $exiftoolAndQpdfCleaner = $this->getMockBuilder(ExiftoolAndQpdfCleaner::class)
            ->disableOriginalConstructor()
            ->getMock();
        $exiftoolAndQpdfCleaner
            ->expects(self::exactly(2))
            ->method('canHandleFile')
            ->withConsecutive([$firstFile], [$secondFile])
            ->willReturnOnConsecutiveCalls(true, false);
        $exiftoolAndQpdfCleaner
            ->expects(self::once())
            ->method('cleanupFile')
            ->with($firstFile);
        GeneralUtility::setSingletonInstance(ExiftoolAndQpdfCleaner::class, $exiftoolAndQpdfCleaner);

        Registry::registerCleaner(ExiftoolAndQpdfCleaner::class, 75);

        (new CleanerService())->cleanupFolder($folder);
    }
}
