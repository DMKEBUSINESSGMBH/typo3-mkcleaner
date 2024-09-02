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

namespace DMK\Mkcleaner\Tests\Command;

use DMK\Mkcleaner\Command\Helper;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class HelperTest.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class HelperTest extends UnitTestCase
{
    /**
     * @test
     */
    public function getFolderObjectsFromCombinedIdentifiers(): void
    {
        $resourceFactory = $this->getMockBuilder(ResourceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $firstFolder = $this->getMockBuilder(Folder::class)->disableOriginalConstructor()->getMock();
        $secondFolder = $this->getMockBuilder(Folder::class)->disableOriginalConstructor()->getMock();
        $resourceFactory
            ->expects(self::exactly(2))
            ->method('getFolderObjectFromCombinedIdentifier')
            ->withConsecutive(['first'], ['second'])
            ->willReturnOnConsecutiveCalls($firstFolder, $secondFolder);

        $folders = (new Helper($resourceFactory))->getFolderObjectsFromCombinedIdentifiers(['first', 'second']);
        self::assertCount(2, $folders);
        self::assertSame($firstFolder, $folders[0]);
        self::assertSame($secondFolder, $folders[1]);
    }
}
