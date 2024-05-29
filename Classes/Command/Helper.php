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

namespace DMK\Mkcleaner\Command;

use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;

/**
 * Class CleanerTaskFieldProvider.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class Helper
{
    public function __construct(
        protected ResourceFactory $resourceFactory,
    ) {}

    /**
     * @param array $foldersToClean list of combined folder identifiers
     *
     * @return Folder[]
     */
    public function getFolderObjectsFromCombinedIdentifiers(array $foldersToClean): array
    {
        $folderObjects = [];
        foreach ($foldersToClean as $combinedFolderIdentifier) {
            $folderObjects[] = $this->resourceFactory->getFolderObjectFromCombinedIdentifier($combinedFolderIdentifier);
        }
        return $folderObjects;
    }
}
