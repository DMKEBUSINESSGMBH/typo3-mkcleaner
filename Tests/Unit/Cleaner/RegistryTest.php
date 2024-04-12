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
use DMK\Mkcleaner\Cleaner\Mat2Cleaner;
use DMK\Mkcleaner\Cleaner\Registry;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class RegistryTest.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class RegistryTest extends UnitTestCase
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
    public function registerAndUnregisterCleaner()
    {
        Registry::registerCleaner(Mat2Cleaner::class, 50);
        Registry::registerCleaner(ExiftoolAndQpdfCleaner::class, 75);

        $registeredCleaners = Registry::getRegisteredCleaners();
        self::assertCount(2, $registeredCleaners);
        self::assertInstanceOf(ExiftoolAndQpdfCleaner::class, current($registeredCleaners));
        self::assertInstanceOf(Mat2Cleaner::class, next($registeredCleaners));

        Registry::unregisterCleaner(ExiftoolAndQpdfCleaner::class);
        $registeredCleaners = Registry::getRegisteredCleaners();
        self::assertCount(1, $registeredCleaners);
        self::assertInstanceOf(Mat2Cleaner::class, current($registeredCleaners));
    }

    /**
     * @test
     */
    public function registerCleanerWithSamePriorityThrowsException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Priority 50 for cleaner DMK\Mkcleaner\Cleaner\Mat2Cleaner already in use');
        Registry::registerCleaner(Mat2Cleaner::class, 50);
        Registry::registerCleaner(Mat2Cleaner::class, 50);
    }

    /**
     * @test
     */
    public function registerCleanerWithoutCleanerInterfaceThrowsException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cleaner stdClass needs to implement DMK\Mkcleaner\Cleaner\CleanerInterface');
        Registry::registerCleaner(\stdClass::class, 50);
    }
}
